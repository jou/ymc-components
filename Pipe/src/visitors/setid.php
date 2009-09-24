<?php

class ymcPipeSetIdVisitor implements ymcPipeVisitor
{
    protected $visitedNodes;

    protected $idCounter = 0;

    public function __construct()
    {
        $this->visitedNodes = new ymcPipeNodeList;
    }

    public function visit( ymcPipeVisitable $visitable )
    {
        if( $visitable instanceof ymcPipe )
        {
            return TRUE;
        }

        if( !$this->visitedNodes->addIfNotContained( $visitable ) )
        {
            return FALSE;
        }
        $visitable->id = ++$this->idCounter;

        return TRUE;
    }
}
