<?php

class ymcPipeExecutionNonSuspendable extends ymcPipeExecution
{
    public function __get( $name )
    {
        switch( $name )
        {
            case 'pipe':
                return $this->$name;
            default:
                return parent::__get( $name );
        }
    }

    public function __set( $name, $value )
    {
        switch( $name )
        {
            case 'pipe':
                if( $value instanceof ymcPipe )
                {
                    $this->$name = $value;
                }
                else
                {
                    //@todo get Exception right
                    throw new ezcBaseValueException;
                }
                break;
            default:
                parent::__set( $name, $value );
        }
    }

    public function run()
    {
        return $this->start();
    }
}
