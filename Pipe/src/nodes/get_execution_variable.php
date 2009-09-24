<?php

class ymcPipeGetExecutionVariableNode extends ymcPipeNode
{
    protected $typename = 'GetVariable';
    protected $minInNodes = 0;
    protected $maxInNodes = 0;

    protected function fetchInput()
    {
        //@todo Check whether the input is of $this->config->inputType
        return array();
    }

    public function processInput( ymcPipeExecution $execution, $input )
    {
        return $execution->variables[$this->config->variable];
    }

    protected function getConfigurationClass()
    {
        return 'ymcPipeGetExecutionVariableNodeConfiguration';
    }
}
