<?php

class ymcPipeDefinitionStorageDatabase implements ymcPipeDefinitionStorage
{
    protected $db;

    protected $prefix;

    public function __construct( $db, $prefix = '' )
    {
        $this->db     = $db;
        $this->prefix = $prefix;
    }

    public function loadByName( $pipeName, $pipeVersion = NULL )
    {
        // Some shortcuts
        $db     = $this->db;
        $prefix = $this->prefix;

        $q = $db->createSelectQuery();
        $q->select( '*' )
          ->from( $db->quoteIdentifier( $prefix.'pipe' ) );

        $e = $q->expr;
        if( is_integer( $pipeVersion ) && $pipeVersion > 0 )
        {
            $q->where( $e->eq( 'id', $q->bindValue( $pipeVersion ) ) );
        }
        else
        {
            // Just take the pipe with the highest id
            $q->where( $e->eq( 'name', $q->bindValue( $pipeName ) ) )
              ->orderBy( 'id', ezcQuerySelect::DESC )
              ->limit( 1 );
        }

        $stmt = $q->prepare();
        $stmt->execute();

        $result = $stmt->fetchAll( PDO::FETCH_ASSOC );
        if( empty( $result ) )
        {
            throw new ymcPipeDefinitionStoragePipeNotFoundException( $pipeName, $pipeVersion );
        }
        $result = array_pop( $result );

        $pipe = new ymcPipe( $result['name'] );
        $pipe->version = ( int )$result['id'];
        $pipe->created = new DateTime( '@'.$result['created'] );

        // Get the nodes
        $q = $db->createSelectQuery();
        $q->select( '*' )
          ->from( $db->quoteIdentifier( $prefix.'pipe_node' ) )
          ->where( $q->expr->eq( 'pipe_id', $pipe->version ) );

        $stmt = $q->prepare();
        $stmt->execute();
        $result = $stmt->fetchAll( PDO::FETCH_ASSOC );

        $nodes = array();
        foreach( $result as $row )
        {
            $nodeId = ( int )$row['id'];
            $node = new $row['class']( $pipe, $row['name'], unserialize( $row['config'] ) );
            $node->id = $nodeId;
            $nodes[$nodeId] = $node;
        }

        // Get the edges
        $q = $db->createSelectQuery();
        $q->select( '*' )
          ->from( $db->quoteIdentifier( $prefix.'pipe_edge' ) )
          ->where( $q->expr->eq( 'pipe_id', $pipe->version ) );

        $stmt = $q->prepare();
        $stmt->execute();
        $result = $stmt->fetchAll( PDO::FETCH_ASSOC );

        foreach( $result as $row )
        {
            $nodes[(int)$row['in_id']]->addOutNode( $nodes[(int)$row['out_id']] );
        }

        return $pipe;
    }

    public function save( ymcPipe $pipe )
    {
        // Some shortcuts
        $db     = $this->db;
        $prefix = $this->prefix;

        $db->beginTransaction();

        $q = $db->createInsertQuery();
        $q->insertInto( $db->quoteIdentifier( $prefix.'pipe' ) )
              ->set( 'name', $q->bindValue( $pipe->name ) )
              ->set( 'created', $q->bindValue( time() ) );

        $statement = $q->prepare();
        $statement->execute();

        $pipeId = (int)$db->lastInsertId( 'pipe_id_seq' );
        $pipe->version = $pipeId;

        // Store nodes
        foreach( $pipe->nodes as $key => $node )
        {
            $node->id = $key+1;
            $q = $this->db->createInsertQuery();
            $q->insertInto( $db->quoteIdentifier( $prefix.'pipe_node' ) )
                ->set( 'id', $q->bindValue( ( int )$node->id ) )
                ->set( 'name', $q->bindValue( ( string )$node->name ) )
                ->set( 'class', $q->bindValue( get_class( $node ) ) )
                ->set( 'pipe_id', $q->bindValue( $pipeId ) )
                ->set( 'config', $q->bindValue( serialize( $node->config ) ) );

            $statement = $q->prepare();
            $statement->execute();
        }

        // Store node edges
        foreach( $pipe->nodes as $node )
        {
            $id = $node->id;
            foreach( $node->outNodes as $outNode )
            {
                $q = $db->createInsertQuery();
                $q->insertInto( $db->quoteIdentifier( $prefix.'pipe_edge' ) )
                    ->set( 'pipe_id', $q->bindValue( $pipeId ) )
                    ->set( 'in_id', $q->bindValue( $id ) )
                    ->set( 'out_id', $q->bindValue( $outNode->id ) );

                $statement = $q->prepare();
                $statement->execute();
            }
        }

        $db->commit();
    }
}
