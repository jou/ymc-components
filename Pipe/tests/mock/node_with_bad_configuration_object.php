<?php

class ymcPipeNodeWithBadConfigurationObject extends ymcPipeNode
{
    protected function getConfigurationClass()
    {
        return 'stdClass';
    }

    public function getDefinition()
    {
        return array();
    }
}
