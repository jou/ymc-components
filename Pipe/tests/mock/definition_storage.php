<?php

class ymcPipeDefinitionStorageMock implements ymcPipeDefinitionStorage
{
    public $pipe;

    public function loadByName( $name, $version = null )
    {
        return $this->pipe;
    }

    public function save( ymcPipe $pipe )
    {
        $this->pipe = $pipe;
    }
}
