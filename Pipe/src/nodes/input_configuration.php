<?php

class ymcPipeInputNodeConfiguration extends ymcPipeNodeConfiguration
{
    public function getDefinition()
    {
        return array( 
            'inputName' => array( 'type' => self::TYPE_STRING,
                                  'required' => TRUE ),
            'inputType' => array( 'type' => self::TYPE_STRING,
                                  'required' => FALSE )
        );
    }
}
