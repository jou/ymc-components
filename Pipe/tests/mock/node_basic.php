<?php

require_once 'node_configuration_basic.php';

class ymcPipeBasicNodeMock extends ymcPipeNode
{
    protected function getConfigurationClass()
    {
        return 'ymcPipeBasicNodeConfigurationMock';
    }
}
