<?php

class ymcPipeBasicNodeConfigurationMock extends ymcPipeNodeConfiguration
{
    public function setPropertiesTestHelper( Array $properties )
    {
        $this->properties = $properties;
    }

    public function __set( $name, $value )
    {
        $this->properties[$name] = $value;
    }

    public function __get( $name )
    {
        return $this->properties[$name];
    }

    public function getDefinition()
    {
        return array();
    }
}
