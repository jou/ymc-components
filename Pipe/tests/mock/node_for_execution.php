<?php
require_once 'node_configuration_basic.php';

class ymcPipeNodeForExecutionMock extends ymcPipeNode
{
    public $todo = true;
    public $hasBeenExecuted = false;

    protected function getConfigurationClass()
    {
        return 'ymcPipeBasicNodeConfigurationMock';
    }

    public function execute( ymcPipeExecution $execution )
    {
        $this->hasBeenExecuted = true;

        if( is_callable( $this->todo ) )
        {
            return call_user_func( $this->todo, $execution );
        }
        elseif( is_bool( $this->todo ) )
        {
            return $this->todo;
        }
        elseif( is_integer( $this->todo ) )
        {
            
        }
        throw new Exception( 'HERE I AM' );
    }
}
