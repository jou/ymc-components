<?php

/**
 * Node that receives input from outside the pipe via a callback function.
 * 
 * @uses       ymcPipeNode
 */
class ymcPipeInputNode extends ymcPipeNode
{
    protected $typename = 'Input';
    protected $minInNodes = 0;
    protected $maxInNodes = 0;

    protected static $fetchCallback;

    public static function setFetchCallback( $callback )
    {
        if( !is_callable( $callback ) )
        {
            //@todo
            throw new Exception;
        }
        self::$fetchCallback = $callback;
    }

    protected function fetchInput()
    {
        //@todo Check whether the input is of $this->config->inputType
        return array( call_user_func( self::$fetchCallback, $this->config->inputName ) );
    }

    public function getOutputType()
    {
        return $this->config->inputType;
    }

    protected function getConfigurationClass()
    {
        return 'ymcPipeInputNodeConfiguration';
    }
}
