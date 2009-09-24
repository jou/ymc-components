<?php

class ymcPipeNodeList implements ArrayAccess, IteratorAggregate, Countable
{
    protected $nodes = array();

    /**
     * Returns a node from this list identified by it's id.
     * 
     * @param integer/string $id 
     * @throws ymcPipeNodeListException if it iterates over a node without an id.
     * @return ymcPipeNode / false if the node could not be found
     */
    public function getById( $id )
    {
        if( !is_integer( $id ) )
        {
            throw new ymcPipeNodeListException( 'Parameter $id must be an integer.' );
        }

        foreach( $this->nodes as $node )
        {
            $nodeId = $node->id;
            if( !is_integer( $nodeId ) )
            {
                throw new ymcPipeNodeListException( 'You must not call getById as long as there is a node without an id' );
            }
            if( $id === $nodeId )
            {
                return $node;
            }
        }
        return false;
    }

    /**
     * Return all nodes with name $name.
     * 
     * @param string $name 
     * @return array of ymcPipeNode
     */
    public function getByName( $name )
    {
        $nodes = array();

        foreach( $this->nodes as $node )
        {
            if( $name === $node->name )
            {
                $nodes[] = $node;
            }
        }

        return $nodes;
    }

    /**
     * Return all nodes with typename $name.
     * 
     * @param string $name 
     * @return array of ymcPipeNode
     */
    public function getByTypename( $typename )
    {
        $nodes = array();

        foreach( $this->nodes as $node )
        {
            if( $typename === $node->typename )
            {
                $nodes[] = $node;
            }
        }

        return $nodes;
    }

    /**
     * Checks whether a node is in $this list.
     * 
     * @param ymcPipeNode $node The node to be searched in this list. 
     *
     * @return mixed false or the offset of the node in this list.
     */
    public function contains( ymcPipeNode $node )
    {
        foreach( $this->nodes as $offset => $listNode )
        {
            if( $node === $listNode )
            {
                return $offset;
            }
        }
        return FALSE;
    }

    public function remove( ymcPipeNode $node )
    {
        foreach( $this->nodes as $offset => $listNode )
        {
            if( $node === $listNode )
            {
                unset( $this->nodes[$offset] );
                return;
            }
        }
        throw new ymcPipeNodeListException( 'Node does not belong to this list.' );
    }

    /**
     * Adds $node to the list only if it is not yet contained in the list.
     * 
     * @param ymcPipeNode $node Node to add.
     * @return boolean Whether the node has been added or not.
     */
    public function addIfNotContained( ymcPipeNode $node )
    {
        foreach( $this->nodes as $offset => $listNode )
        {
            if( $node === $listNode )
            {
                return FALSE;
            }
        }
        $this->nodes[] = $node;
        return TRUE;
    }

    ///////////////////////////////////////////////////////////////////////////
    //
    // Methods implemented for ArrayAccess, IteratorAggregate and Countable
    //

    /**
     * Defined by IteratorAggregate interface
     * Returns an iterator for for this object, for use with foreach
     * @return ArrayIterator
     */
    public function getIterator() 
    {
      return new ArrayIterator($this->nodes);
    }

    public function offsetExists($offset)
    {
        return array_key_exists( $offset, $this->nodes );
    }

 	public function offsetGet($offset)
    {
        if( array_key_exists( $offset, $this->nodes ) )
        {
            return $this->nodes[$offset];
        }
        throw new ymcPipeNodeListException( 'Offset '.$offset.' is not set.' );
    }

 	public function offsetSet($offset, $value)
    {
        if( !$value instanceof ymcPipeNode )
        {
            throw new ymcPipeNodeListException( 'Could not set a value of type '.gettype( $value).' in a ymcPipeNodeList.' );
        }
        if( NULL === $offset )
        {
            $this->nodes[] = $value;
        }
        elseif( !is_integer( $offset ) )
        {
            throw new ymcPipeNodeListException( 'Offset must be of type integer.' );
        }
        else
        {
            $this->nodes[$offset] = $value;
        }
    }

 	public function offsetUnset($offset)
    {
        if( !array_key_exists( $offset, $this->nodes ) )
        {
            throw new ymcPipeNodeListException( $offset );
        }
        unset( $this->nodes[$offset] );
    }

    public function count()
    {
        return count( $this->nodes );
    }
}
