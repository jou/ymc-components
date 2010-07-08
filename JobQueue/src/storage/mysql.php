<?php

class ymcJobQueueStorageMysql //implements ymcJobQueueStorage
{
    const TABLE = 'job_queue';
    const LOCKPREFIX = 'jq';

    /**
     * Database instance to work on.
     * 
     * @var ezcDbHandlerMysql
     */
    private $db;

    /**
     * Mapping of integers to job class names.
     * 
     * @var array
     */
    private $jobClasses;

    /**
     * Cached statement for lock()
     * 
     * @var PDOStatement
     */
    private $lockStmt;

    /**
     * The last lock string aquired.
     * 
     * @var string
     */
    private $lock;

    /**
     * Cached statement for push()
     * 
     * @var PDOStatement
     */
    private $pushStmt;

    private $pushParamClass, $pushParamPriority, $pushParamState;

    public function __construct( ezcDbHandlerMysql $db, Array $jobClasses )
    {
        $this->db = $db;
        $this->jobClasses = $jobClasses;
    }

    /**
     * Tries to aquire a lock for job $id and returns, whether it was succesful.
     * 
     * @param integer $id 
     * @return bool
     */
    public function lock( $id )
    {
        $this->lock = self::LOCKPREFIX.$id;

        if( !$this->lockStmt )
        {
            $q = $this->db->createSelectQuery();
            $q->select( 'GET_LOCK( '.$q->bindParam( $this->lock ).', 0 )' );
            $this->lockStmt = $q->prepare();
        }

        $this->lockStmt->execute();
        $result = $this->lockStmt->fetchColumn();

        if( '1' === $result ) 
        {
            return TRUE;
        }
        if( '0' === $result ) 
        {
            $this->lock = null;
            return FALSE;
        }

        throw new Exception( $result );
    }

    /**
     * Release lock aquired by lock().
     */
    public function release()
    {
        if( NULL === $this->lock )
        {
            throw new Exception( 'Nothing locked!' );
        }
        $this->db->exec( 'DO RELEASE_LOCK( "'.$this->lock.'" )' );
        $this->lock = NULL;
    }

    /**
     * Returns and locks an array with job data.
     * 
     * @param array $jobClasses To filter the jobs
     * @return array
     */
    public function pop( $jobClasses = array(), $randomizeList = true )
    {
        // get me the numeric keys of the classes I'm searching for
        $classes = array_keys( array_intersect( $this->jobClasses, $jobClasses ) );
        
        $id = $this->popJobId( $classes, $randomizeList );
        if( !$id ) return $id;

        $q = $this->db->createSelectQuery();
        $q->select( '*' )->from( self::TABLE )
          ->where( $q->expr->eq( 'id', $q->bindValue( $id ) ) );

        $stmt = $q->prepare();
        $stmt->execute();

        $result = $stmt->fetch( PDO::FETCH_ASSOC );
        if( isset( $result['class'] ) )
        {
            $result['class'] = $this->jobClasses[$result['class']];
        }
        return $result;
    }

    /**
     * Helper function to try job ids until a lock can be aquired.
     * 
     * @param array $classes 
     * @access public
     * @return void
     */
    public function popJobId( Array $classes, $randomizeList = true )
    {
        $q = $this->db->createSelectQuery();
        $q->select( 'id' )->from( self::TABLE );

        // Don't run deactivated jobs
        $q->where( $q->expr->neq( 'priority', 0 ) );

        if( !empty( $classes ) )
        {
            $q->where( $q->expr->in( 'class', $classes ) );
        }

        $q->orderBy( 'priority', ezcQuerySelect::ASC )
          ->limit( 300 );

        $stmt = $q->prepare();
        $stmt->execute();

        $ids = $stmt->fetchAll( PDO::FETCH_COLUMN );

        if( empty( $ids ) ) return NULL;

        // Try to acquire a lock
        if ( $randomizeList )
        {
            shuffle( $ids );
        }

        while( $id = array_shift( $ids ) )
        {
            if( $this->lock( $id ) ) return $id;
        }
        return FALSE;
    }

    /**
     * Pushes job information to the DB.
     * 
     * @param integer $jobClass 1-255, see jobClasses
     * @param integer $priority 1-255, 1 is most important
     * @param string  $state    binary, serialized state
     * @access public
     * @return void
     */
    public function push( $jobClass, $state, $priority = 0 )
    {
        $this->pushParamClass    = array_search( $jobClass, $this->jobClasses );
        $this->pushParamPriority = $priority;
        $this->pushParamState    = $state;

        if( NULL === $this->pushStmt )
        {
            $q = $this->db->createInsertQuery();
            $q->insertInto( self::TABLE );
            $q->set( 'class',    $q->bindParam( $this->pushParamClass ) );
            $q->set( 'priority', $q->bindParam( $this->pushParamPriority ) );
            $q->set( 'state',    $q->bindParam( $this->pushParamState ) );
            $this->pushStmt = $q->prepare();
        }
        $this->pushStmt->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Activates a job with $priority.
     * 
     * @param integer $jobId 
     * @param integer $priority 
     */
    public function activate( $jobId, $priority )
    {
        $q = $this->db->createUpdateQuery();
        $q->update( self::TABLE )
          ->set( 'priority', $q->bindValue( $priority ) )
          ->where( $q->expr->eq( 'id', $q->bindValue( $jobId ) ) );

        $stmt = $q->prepare();
        $stmt->execute();
    }

    /**
     * Saves followupjobs and deletes current job.
     * 
     * @param integer $jobId 
     * @param array $followUpJobs 
     */
    public function done( $jobId, $followUpJobs = array() )
    {
        if( !empty( $followUpJobs ) )
        {
            $this->db->beginTransaction();
            foreach( $followUpJobs as $fJob )
            {
                $this->push( $fJob['class'], $fJob['state'], $fJob['priority'] );
            }
        }

        $this->deleteJob( $jobId );

        if( !empty( $followUpJobs ) )
        {
            $this->db->commit();
        }
        $this->release();
    }

    public function cancel( $jobId )
    {
        $this->deleteJob( $jobId );
    }

    public function deleteJob( $id )
    {
        $q = $this->db->createDeleteQuery();
        $q->deleteFrom( self::TABLE )->where( $q->expr->eq( 'id', $q->bindValue( $id ) ) );

        $q->prepare()->execute();
    }

    public function update( $id, $jobClass, $state, $priority )
    {
        $q = $this->db->createUpdateQuery();
        $q->update( self::TABLE );
        $q->set( 'class',    $q->bindValue( array_search( $jobClass, $this->jobClasses ) ) );
        $q->set( 'priority', $q->bindValue( $priority ) );
        $q->set( 'state',    $q->bindValue( $state ) );
        $q->where( $q->expr->eq( 'id', $q->bindValue( $id ) ) );

        $q->prepare()->execute();
    }
}
