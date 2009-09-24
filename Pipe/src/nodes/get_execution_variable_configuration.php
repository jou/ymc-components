<?php

class ymcPipeGetExecutionVariableNodeConfiguration extends ymcPipeNodeConfiguration
{
    public function getDefinition()
    {
        return array( 
            'variable' => array( 'type' => self::TYPE_STRING,
                                  'required' => TRUE )
        );
    }
}
