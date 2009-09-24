<?php

class ymcPipeNodeInputProviderMock extends ymcPipeNode
{
    public $input;

    public function __construct( ymcPipe $pipe, $input )
    {
        $this->input = $input;
        parent::__construct( $pipe );
    }

    public function execute( ymcPipeExecution $execution )
    {
        throw new Exception( 'Don\'t you dare to call me!' );
    }

    public function getOutput(  )
    {
        return $this->input;
    }

    public function getConfigurationClass()
    {
        return 'ymcPipeBasicNodeConfigurationMock';
    }
}
