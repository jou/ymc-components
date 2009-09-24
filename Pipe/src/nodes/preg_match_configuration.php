<?php

class ymcPipePregMatchNodeConfiguration extends ymcPipeNodeConfiguration
{
    public function getDefinition()
    {
        return array( 
            'regexp' => array( 'type' => self::TYPE_TEXT,
                               'required' => TRUE )
        );
    }
}
