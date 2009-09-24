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
        if ( !is_integer( $databaseValue ) )
        {
            throw new Exception;
        }
        $obj = new $this->bitSetClass;
        $obj->setInteger( $databaseValue );
        return $obj;
    }

    public function toDatabase( $obj )
    {
        return $obj->getInteger();
    }

    public static function __set_state( Array $state )
    {
        return new self( $state['bitSetClass'] );
    }
}
