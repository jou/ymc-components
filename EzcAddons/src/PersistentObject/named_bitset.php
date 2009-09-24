<?php

/**
 * Class that helps to represent a set of named boolean values as an integer.
 * 
 */
class ymcEzcPersistentObjectNamedBitSet implements ArrayAccess
{
    private $value = 0;

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

    public function setInteger( $value )
    {
        if( !is_integer( $value ) )
        {
            throw new Exception;
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
                throw new Exception;
            }
            if( !is_string( $value ) )
            {
                throw new Exception;
            }
            // Check that enum strings are unique
            if( array_key_exists( $value, $reverseBitSetMapping ) )
            {
                throw new Exception;
            }
            $reverseBitSetMapping[$value] = $key;
        }
        $this->bitSetMapping = $bitSetMapping;
        $this->reverseBitSetMapping = $reverseBitSetMapping;
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
            throw new Exception;
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

        $this->value = $this->value | $bit & ( $value ? ~0 : 0 );
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
}
