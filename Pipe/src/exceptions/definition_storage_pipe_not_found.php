<?php

class ymcPipeDefinitionStoragePipeNotFoundException extends ymcPipeDefinitionStorageException
{
    public function __construct( $name, $id )
    {
        $id = $id ? $id : 'undefined';
        parent::__construct( 'Did not find pipe with name '.$name.' and id '.$id.'.' );
    }
}
