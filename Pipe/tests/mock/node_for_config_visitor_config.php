<?php

class ymcPipeNodeForConfigTestConfig extends ymcPipeNodeConfiguration
{
    public $_definition;

    public function __construct( $properties = array(), $definition = array() )
    {
        $this->_definition = $definition;
        parent::__construct( $properties );
    }

    public function getDefinition()
    {
        return $this->_definition;
    }
}
