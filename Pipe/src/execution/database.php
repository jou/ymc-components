<?php

class ymcPipeExecutionDatabase extends ymcPipeExecutionSuspendable
{
    /**
     *  YYYY-MM-DD HH:MM:SS
     */
    const DB_DATETIMEFORMAT = 'U';

    protected $pipeName;

    protected $pipeVersion;

    protected $db;

    protected $created;

    /**
     * Temporary variable to cache node states fetched from the DB until the pipe is created.
     * 
     * @var array( ( int )nodeId => ( array )nodeVariables )
     */
    protected $nodeStates = array();

    /**
     * Prefix for DB tables.
     * 
     * @var string
     */
    protected $prefix = '';

    /**
     * id of this execution;
     * 
     * @var integer
     */
    protected $id;

    /**
     * inTransaction 
     * 
     * @var bool
     */
    protected $inTransaction = false;

    public function __construct( ezcDbHandler $db, $executionId = Null )
    {
        $this->db = $db;

        if( is_integer( $executionId ) )
        {
            $this->id = $executionId;
            $this->load();
        }
        elseif( NULL === $executionId )
        {
            $this->created = new DateTime( 'now' );
            $this->executionState = self::NOT_STARTED;
        }
        else
        {
            throw new Exception( 'executionId must be of type integer.' );
        }
    }

    /**
     * Deletes this execution from the Database. 
     * 
     */
    public function delete()
    {
        self::deleteById( $this->db, $this->id, $this->prefix );

        if( $this->inTransaction )
        {
            //$this->db->commit();
            $this->inTransaction = false;
        }
    }

    public static function deleteById( ezcDbHandler $db, $id, $prefix='' )
    {
        //$db->beginTransaction();

        $q = $db->createDeleteQuery();
        $q->deleteFrom( $db->quoteIdentifier( $prefix.'pipe_execution' ) )
          ->where( $q->expr->eq( $db->quoteIdentifier( 'id' ), $q->bindValue( (int)$id ) ) );

        $stmt = $q->prepare();
        $stmt->execute();

        $q = $db->createDeleteQuery();
        $q->deleteFrom( $db->quoteIdentifier( $prefix.'pipe_execution_state' ) )
          ->where( $q->expr->eq( $db->quoteIdentifier( 'execution_id' ), $q->bindValue( (int)$id ) ) );

        $stmt = $q->prepare();
        $stmt->execute();

        //$db->commit();
    }

    /**
     * Loads the execution from the database.
     * 
     */
    protected function load()
    {
        //$this->db->beginTransaction();
        $this->inTransaction = true;

        $q = $this->db->createSelectQuery();

        $q->select( '*' )
              ->from( $this->db->quoteIdentifier( $this->prefix.'pipe_execution' ) )
              ->where( $q->expr->eq( $this->db->quoteIdentifier( 'id' ),
                                         $q->bindValue( (int)$this->id ) ) );

        $stmt = $q->prepare();
        $stmt->execute();

        $result = $stmt->fetchAll( PDO::FETCH_ASSOC );

        if ( empty( $result ) )
        {
            //@todo better Exception
            throw new Exception(
              'No state information for execution '.$this->id.'.'
            );
        }

        if ( $result === false )
        {
            //@todo better Exception
            throw new Exception(
              'DB error loading state of execution '.$this->id.'.'
            );
        }

        // There can be only one result row
        $result = array_pop( $result );

        $this->pipeName       = $result['pipe_name'];
        $this->pipeVersion    = ( int )$result['pipe_version'];
        $this->executionState = ( int )$result['state'];

        //$this->parent      = $result['parent'];
        $this->created     = new DateTime( '@'.$result['created'] );

        // Load variables of this execution and of all nodes
        $q = $this->db->createSelectQuery();

        $q->select( '*' )
              ->from( $this->db->quoteIdentifier( $this->prefix.'pipe_execution_state' ) )
              ->where( $q->expr->eq( $this->db->quoteIdentifier( 'execution_id' ),
                                         $q->bindValue( (int)$this->id ) ) );

        $stmt = $q->prepare();
        $stmt->execute();

        $result         = $stmt->fetchAll( PDO::FETCH_ASSOC );
        //@todo Check result

        $nodeStates = array();

        foreach( $result as $row )
        {
            $nodeId = ( int )$row['node_id'];

            if( 0 === $nodeId )
            {
                // This is the state of the execution 
                $this->unserializeState( $row['state'] );
            }
            else
            {
                // It's a node's state
                $nodeStates[$nodeId] = $row['state'];
            }
        }

        $this->nodeStates = $nodeStates;
    }

    protected function serializeState()
    {
        return serialize(
            array( 'v' => $this->variables,
                   'e' => $this->exception,
                   'a' => array_keys( $this->activatedNodes )
        ) );
    }

    protected function unserializeState( $state )
    {
        $s = unserialize( $state );
        $this->variables      = $s['v'];
        $this->exception      = $s['e'];

        foreach( $s['a'] as $activatedNodeId )
        {
            $this->activatedNodes[(int)$activatedNodeId] = TRUE;
        }
    }


    /**
     * Stores the current execution state do the database.
     * 
     */
    public function store()
    {
        if( $this->isPersistent() )
        {
            $this->update();
        }
        else
        {
            $this->insert();
        }
    }

    /**
     * Prepares this execution for starting and calls parent::start().
     *
     * After completion you must decide whether to call suspend() or delete()!
     * @todo save a static flac to avoid the start of a second pipe during a transaction.
     * 
     */
    public function start( $parentId = NULL )
    {
        // Make sure the pipe is set up.
        $this->__get( 'pipe' );

        parent::start( $parentId );
    }

    protected function insert()
    {
        //$this->db->beginTransaction();

        $q = $this->db->createInsertQuery();

        $q->insertInto( $this->db->quoteIdentifier( $this->prefix . 'pipe_execution' ) )
              ->set( 'pipe_name', $q->bindValue( (string)$this->__get( 'pipeName' ) ) )
              ->set( 'pipe_version', $q->bindValue( (int)$this->__get( 'pipeVersion' ) ) )
              ->set( 'state', $q->bindValue( (int)$this->executionState ) )
              ->set( 'created', $q->bindValue( $this->created->format( self::DB_DATETIMEFORMAT ) ) )
              ->set( 'parent', $q->bindValue(0) );

        $statement = $q->prepare();
        $statement->execute();

        $this->id = (int)$this->db->lastInsertId( 'execution_id_seq' );

        // Save execution variables
        $this->insertState( null, $this->serializeState() );

        // save node states
        if( $this->pipe instanceof ymcPipe )
        {
            foreach( $this->pipe->nodes as $node )
            {
                $id = $node->id;
                if( !is_integer( $id ) )
                {
                    throw new Exception( 'Node has no id!' );
                }
                $this->insertState( $node->id, $node->serializeState() );
            }
        }

        //$this->db->commit();
    }

    protected function insertState( $nodeId, $state )
    {
        $q = $this->db->createInsertQuery();

        $q->insertInto( $this->db->quoteIdentifier( $this->prefix . 'pipe_execution_state' ) )
              ->set( 'execution_id', $q->bindValue( (int)$this->id ) )
              ->set( 'node_id', $q->bindValue( (int)$nodeId ) )
              ->set( 'state', $q->bindValue( $state ) );

        $statement = $q->prepare();
        $statement->execute();
    }

    protected function updateState( $nodeId, $state )
    {
        $q = $this->db->createUpdateQuery();

        $q->update( $this->db->quoteIdentifier( $this->prefix . 'pipe_execution_state' ) )
              ->set( 'state', $q->bindValue( $state ) )
              ->where( $q->expr->eq( 'node_id', $q->bindValue( (int)$nodeId ) ) )
              ->where( $q->expr->eq( 'execution_id', $q->bindValue( (int)$this->id ) ) );

        $statement = $q->prepare();
        $statement->execute();
    }

    protected function update()
    {
        $this->updateState( null, $this->serializeState() );

        // Update the state column in pipe_execution
        // @todo: remember the state from load() and update only if necessary
        $q = $this->db->createUpdateQuery();
        $q->update( $this->db->quoteIdentifier( $this->prefix . 'pipe_execution' ) )
              ->set( 'state', $q->bindValue( ( int )$this->executionState ) )
              ->where( $q->expr->eq( 'id', $q->bindValue( (int)$this->id ) ) );

        $statement = $q->prepare();
        $statement->execute();

        if( $this->pipe instanceof ymcPipe )
        {
            foreach( $this->pipe->nodes as $node )
            {
                $id = $node->id;
                $serialized = $node->serializeState();

                if( !array_key_exists( $id, $this->nodeStates ) )
                {
                    if( !is_integer( $id ) )
                    {
                        throw new Exception( 'Node has no id.' );
                    }
                    $this->insertState( $id, $serialized );
                }
                else
                {
                    // Only update, if sth. has changed.
                    if( $serialized !== $this->nodeStates[$id] )
                    {
                        $this->updateState( $id, $serialized );
                    }
                }
            }
        }

        //$this->db->commit();
        $this->inTransaction = false;
    }

    protected function isPersistent()
    {
        return NULL !== $this->id;
    }

    /**
     * Indicates the pipe to be executed.
     *
     * This is intended to allow the initialization of a pipe execution without instantiating the
     * pipe. So the execution can be set up, saved and started later by another process.
     *
     * The version can remain undefined and will be set the first time the pipe is loaded.
     * 
     * @param string  $name 
     * @param integer $version optional, defaults to the most recent.
     * @return void
     */
    public function setPipe( $name, $version = NULL )
    {
        if( !is_string( $name ) || ( !is_integer( $version ) && ( NULL !== $version ) ) )
        {
            throw new Exception( sprintf( 'Pipe name must be string and pipe version integer. Given %s and %s.', gettype( $name ), gettype( $version ) ) );
        }
        $this->pipeName    = $name;
        $this->pipeVersion = $version;
    }

    /**
     * Loads the pipe indicated by setPipe() and puts it in $this->pipe.
     * 
     */
    protected function loadPipe()
    {
        if( NULL === $this->pipeName )
        {
            throw new Exception( 'You must specify a pipe with setPipe() first!' );
        }

        $pipe = $this->__get( 'definitionStorage' )->loadByName( $this->pipeName, $this->pipeVersion );
        if( !$pipe instanceof ymcPipe )
        {
            throw new Exception( 
                sprintf( 'Could not load pipe %s, version %s with definitionStorage of type %s.',
                         $this->pipeName,
                         $this->pipeVersion ? $this->pipeVersion : '(undefined)',
                         get_class( $this->__get( 'definitionStorage' ) ) )
                );
        }

        $nodeStates = $this->nodeStates;
        foreach( $pipe->nodes as $node )
        {
            $id = $node->id;
            if( array_key_exists( $id, $nodeStates ) )
            {
                $node->unserializeState( $nodeStates[$id] );
            }
        }

        $this->pipe = $pipe;
        $this->pipeVersion = $pipe->version;
    }

    public function __get( $name )
    {
        switch( $name )
        {
            case 'executionState':
            case 'id':
            case 'prefix':
            case 'created':
            case 'exception':
                return $this->$name;
            //@todo check whether $this->pipe is loaded and get values from there in this case.
            case 'pipeName':
            case 'pipeVersion':
                return $this->$name;
            case 'pipe':
                if( !$this->pipe instanceof ymcPipe )
                {
                    $this->loadPipe();
                }
                return $this->pipe;

            case 'definitionStorage':
                if( !isset( $this->definitionStorage ) )
                {
                    $this->definitionStorage = new ymcPipeDefinitionStorageDatabase( $this->db );
                }
                return $this->definitionStorage;

            default:
                return parent::__get( $name );
        }
    }

    public function __set( $name, $value )
    {
        switch( $name )
        {
            case 'prefix':
                break;
            case 'definitionStorage':
                if ( !( $value instanceof ymcPipeDefinitionStorage ) )
                {
                    throw new ezcBaseValueException(
                        $name,
                        $value,
                        'ymcPipeDefinitionStorage'
                    );
                }
                break;

            case 'created':
                if ( !( $value instanceof DateTime ) )
                {
                    throw new ezcBaseValueException(
                        $name,
                        $value,
                        'DateTime'
                    );
                }
                break;

            default:
                parent::__set( $name, $value );

        }
        $this->$name = $value;
    }

    public function __destruct()
    {
        if( $this->inTransaction )
        {
            //$this->db->rollback();
        }
    }
}
