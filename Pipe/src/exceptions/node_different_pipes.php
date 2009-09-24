<?php

class ymcPipeNodeDifferentPipesException extends ymcPipeNodeException
{
    public function __construct(  )
    {
        parent::__construct( 'Operation not possible. Nodes belong to different pipes.' );
    }
}
