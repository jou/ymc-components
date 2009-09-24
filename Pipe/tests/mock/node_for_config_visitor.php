<?php

require_once 'node_for_config_visitor_config.php';
class ymcPipeNodeForConfigTest extends ymcPipeNode
{
    public function getConfigurationClass()
    {
        return 'ymcPipeNodeForConfigTestConfig';
    }

    public function _initConfiguration( $def, $properties = array() )
    {
        $this->config = new ymcPipeNodeForConfigTestConfig( $properties, $def );
    }
}
