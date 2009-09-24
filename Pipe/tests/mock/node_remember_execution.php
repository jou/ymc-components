<?php

class ymcPipeNodeRememberExecutionMock extends ymcPipeNode
{
    public $executed = false;

    public function execute( ymcPipeExecution $execution )
    {
        //echo 'execute Node '.$this->id."\n";
        $this->executed = true;

        return parent::execute( $execution );
    }

    protected function getConfigurationClass()
    {
        return 'ymcPipeBasicNodeConfigurationMock';
    }
}
