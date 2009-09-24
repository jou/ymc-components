<?php

interface ymcPipeDefinitionStorage
{
    /**
     * Load a pipe definition by name.
     *
     * @param  string $pipeName
     * @param  string $pipeVersion if( !$pipeVersion ) loads most recent
     * @return ymcPipe
     * @throws ymcPipeDefinitionStorageException
     */
    public function loadByName( $pipeName, $pipeVersion = NULL );

    /**
     * Save a pipe definition to the database.
     *
     * @param  ymcPipe $pipe
     * @throws ymcPipeDefinitionStorageException
     */
    public function save( ymcPipe $pipe );
}
