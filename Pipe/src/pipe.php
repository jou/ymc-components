<?php

class ymcPipe implements ymcPipeVisitable
{
    public $name;

    public $version;

    protected $nodes;

    protected $created;

    public function __construct( $name = 'unnamed' )
    {
        $this->name = $name;
        $this->nodes = new ymcPipeNodeList;
    }

    /**
     * Creates and returns a new ymcPipeNode of type $nodeType.
     * 
     * @param string $nodeType Must be a class that extends ymcPipeNode.
     * @param string $name     The name for the new node.
     * @throws //@todo
     * @return ymcPipeNode
     */
    public function createNode( $nodeType, $name = '' )
    {
        $node = new $nodeType( $this, $name );
        if( !$node instanceof ymcPipeNode )
        {
            //@todo 
            throw new Exception;
        }
        return $node;
    }

    /**
     * Removes a node from this pipe and deletes all of the node's properties.
     *
     * Make sure not to reference this node anymore after calling this method. You should unset
     * the $node after directly after this method call:
     *
     * $pipe->deleteNode( $node );
     * unset( $node );
     * 
     * @param ymcPipeNode $node 
     * @throws ymcPipeNodeListException if the node does not belong to this pipe.
     */
    public function deleteNode( ymcPipeNode $node )
    {
        $this->nodes->remove( $node );
        $node->delete();
    }

    /**
     * Used from a node's ctor to register itself with the pipe.
     * 
     * @param ymcPipeNode $node 
     * @throws
     * @return void
     */
    public function addNode( $node )
    {
        if( $this !== $node->pipe )
        {
            //@todo
            throw new Exception;
        }

        if( $this->nodes->contains( $node ) !== false )
        {
            throw new Exception( 'Node is already registered in this pipe!' );
        }

        $this->nodes[] = $node;
    }

    public function __get( $name )
    {
        switch( $name )
        {
            case 'created':
                if( $this->created instanceof DateTime )
                {
                    return $this->created;
                }
                else
                {
                    return $this->created = new DateTime( 'now' );
                }
            case 'nodes':
                return $this->$name;
            default:
                throw new ezcBasePropertyNotFoundException( $name );
        }
    }

    public function __set( $name, $value )
    {
        switch( $name )
        {
            case 'created':
                if( !$value instanceof DateTime )
                {
                    throw new ezcBaseValueException( $name, $value, 'DateTime' );
                }
            break;
            case 'nodes':
                throw new ezcBasePropertyPermissionException( ezcBasePropertyPermissionException::READ );
            default:
                throw new ezcBasePropertyNotFoundException( $name );
        }
        $this->$name = $value;
    }

    /**
     * Returns a NodeList with all nodes with 0 === $node->numInNodes.
     * 
     * @return ymcPipeNodeList
     */
    public function getStartNodes()
    {
        $startNodes = new ymcPipeNodeList;

        foreach( $this->nodes as $node )
        {
            if( 0 === $node->numInNodes )
            {
                $startNodes[] = $node;
            }
        }
        return $startNodes;
    }

    /**
     * Returns a NodeList with all nodes with 0 === $node->numOutNodes.
     * 
     * @return ymcPipeNodeList
     */
    public function getEndNodes()
    {
        $startNodes = new ymcPipeNodeList;

        foreach( $this->nodes as $node )
        {
            if( 0 === $node->numOutNodes )
            {
                $startNodes[] = $node;
            }
        }
        return $startNodes;
    }

    /**
     * Overridden implementation of accept() calls accept on the start nodes.
     *
     * @param ymcPipeVisitor $visitor
     */
    public function accept( ymcPipeVisitor $visitor )
    {
        $visitor->visit( $this );

        foreach( $this->getStartNodes() as $node )
        {
            $node->accept( $visitor );
        }
    }
}
