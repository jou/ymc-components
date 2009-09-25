<?php

class ymcEzcPersistentObjectBitSetConverter implements ezcPersistentPropertyConverter
{
    private $bitSetClass;

    public function __construct( $bitSetClass )
    {
        $this->bitSetClass = $bitSetClass;
    }

    public function fromDatabase( $databaseValue )
    {
        if ( !is_numeric( $databaseValue ) )
        {
            throw new ezcBaseValueException( 'databaseValue', $databaseValue, 'numeric', 'parameter' );
        }
        $obj = new $this->bitSetClass;
        $obj->setInteger( ( int )$databaseValue );
        return $obj;
    }

    public function toDatabase( $propertyValue )
    {
        if( !is_object( $propertyValue ) )
        {
            throw new ezcBaseValueException( 'propertyValue', $propertyValue, 'object', 'parameter' );
        }

        return $propertyValue->getInteger();
    }

    public static function __set_state( Array $state )
    {
        return new self( $state['bitSetClass'] );
    }
}
