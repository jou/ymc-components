<?php

class ymcPipeExecutionTest extends ymcPipeExecution
{
    public function __construct( ymcPipe $pipe, ymcFeedEntry $entry )
    {
        $this->pipe = $pipe;
        
        $this->executionState = self::NOT_STARTED;
        $this->setState( $entry );
    }

    public $variables = array( 
        '_id'         => NULL,
        'feedId'      => NULL,
        'added'       => NULL,
        'author'      => NULL,
        'description' => NULL,
        'id'          => NULL,
        'link'        => NULL,
        'title'       => NULL,
        'updated'     => NULL,
        'published'   => NULL,
    );

    public function setState( $state )
    {
        //@todo test this with php5.3
        foreach( $this->variables as $key => &$value )
        {
            $value = $state->$key;
        }
    }
    
}
