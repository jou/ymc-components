<?php

/**
 * Class that helps to represent a set of named boolean values as an integer.
 *
 */
class ymcEzcPersistentObjectNamedBitSet implements ArrayAccess, Iterator, serializable
{
    private $value = 0;

    private $iteratorPosition = 0;

    /**
     * Contains the enum definition as a number indexed array
     *
     * ex. array(
     *   0 => 'monday',
     *   1 => 'tuesday',
     *   ...
     * )
     *
     * @var array( int => string )
     */
    private $bitSetMapping = array();

    /**
     * The reverse of $enumMapping.
     *
     * @var array( string => int )
     */
    private $reverseBitSetMapping = array();

    public function __construct()
    {
        $this->initBitSetMapping();
    }

    /**
     * This method can be overwritten to initialize the name mapping.
     *
     * @access public
     * @return void
     */
    protected function initBitSetMapping()
    {
    }

    public function setInteger( $value )
    {
        if( !is_integer( $value ) )
        {
            throw new ezcBaseValueException( 'bitset integer', $value, 'integer', 'parameter' );
        }
        $this->value = $value;
    }

    public function getInteger()
    {
        return $this->value;
    }

    public function getNames()
    {
        return array_values( $this->bitSetMapping );
    }

    /**
     *
     *
     * @todo strict error checking
     * @param Array $enumMapping
     */
    public function setBitSetMapping( Array $bitSetMapping )
    {
        $reverseBitSetMapping = array();

        foreach( $bitSetMapping as $key => $value )
        {
            if( !is_integer( $key ) )
            {
                throw new ezcBaseValueException( 'bit offset', $key, 'integer', 'parameter' );
            }
            if( !is_string( $value ) )
            {
                throw new ezcBaseValueException( 'bit offset name', $value, 'string', 'offset '.$key );
            }
            // Check that enum strings are unique
            if( array_key_exists( $value, $reverseBitSetMapping ) )
            {
                throw new Exception( 'duplicate '.$value );
            }
            $reverseBitSetMapping[$value] = $key;
        }
        $this->bitSetMapping = $bitSetMapping;
        $this->reverseBitSetMapping = $reverseBitSetMapping;
    }

    /**
     * Returns the bitset as an array representation with names as keys.
     *
     * @return array( string => bool )
     */
    public function toNamedArray()
    {
        $bitset = array();
        foreach( $this->bitSetMapping as $offset => $name )
        {
            $bitset[$name] = $this->getByPosition( $offset );
        }
        return $bitset;
    }

    public function __get( $name )
    {
        if( is_string( $name ) )
        {
            return $this->getByName( $name );
        }
        elseif( is_integer( $name ) )
        {
            return $this->getByPosition( $name );
        }
        throw new Exception;
    }

    public function getByName( $name )
    {
        if( !array_key_exists( $name, $this->reverseBitSetMapping ) )
        {
            throw new Exception;
        }
        return $this->getByPosition( $this->reverseBitSetMapping[$name] );
    }

    public function getByPosition( $position )
    {
        return (bool)( 1 << $position & $this->value );
    }

    public function __set( $name, $value )
    {
        if( !is_bool( $value ) )
        {
            throw new ezcBaseValueException( $name, $value, 'boolean', 'parameter' );
        }
        if( is_string( $name ) )
        {
            $this->setByName( $name, $value );
        }
        elseif( is_integer( $name ) )
        {
            $this->setByPosition( $name, $value );
        }
        else
        {
            throw new Exception;
        }
    }

    public function setByName( $name, $value )
    {
        if( !array_key_exists( $name, $this->reverseBitSetMapping ) )
        {
            throw new Exception;
        }
        $this->setByPosition( $this->reverseBitSetMapping[$name], $value );
    }

    public function setByPosition( $position, $value )
    {
        $bit = 1 << $position;

        // reset the bit
        $this->value = $this->value & ~$bit;

        if( $value )
        {
            $this->value = $this->value | $bit;
        }
    }

    public function __isset( $name )
    {
        return array_key_exists( $name, $this->reverseBitSetMapping );
    }

    public function __unset( $name )
    {
        throw new Exception;
    }

    // methods implementing interface ArrayAccess

    public function offsetExists( $offset )
    {
        return $this->__isset( $offset );
    }

    public function offsetGet( $offset )
    {
        return $this->__get( $offset );
    }

    public function offsetSet ( $offset, $value )
    {
        $this->__set( $offset, $value );
    }

    public function offsetUnset ( $offset )
    {
        $this->__unset( $offset );
    }

    // methods implementing interface serializable

    public function serialize()
    {
        return ( string )$this->value;
    }

    public function unserialize( $value )
    {
        $this->initBitSetMapping();
        $this->value = ( int )$value;
    }

    // methods implementing interface Iterator

    public function current()
    {
        return $this->getByPosition( $this->iteratorPosition );
    }

    public function key()
    {
        return $this->bitSetMapping[$this->iteratorPosition];
    }

    public function next()
    {
        ++$this->iteratorPosition;
    }

    public function rewind()
    {
        $this->iteratorPosition = 0;
    }

    public function valid()
    {
        return $this->iteratorPosition < count( $this->bitSetMapping );
    }
}
