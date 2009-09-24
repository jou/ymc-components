<?php

class ymcPipeVisitorMock implements ymcPipeVisitor
{
    public $visitedNodes;

    public function __construct(  )
    {
        $this->visitedNodes = new ymcPipeNodeList;
    }

    public function visit( ymcPipeVisitable $node )
    {
        if( $node instanceof ymcPipe ) return;

        if( !$this->visitedNodes->addIfNotContained( $node ) )
        {
            return FALSE;
        }
        return TRUE;
    }
}
