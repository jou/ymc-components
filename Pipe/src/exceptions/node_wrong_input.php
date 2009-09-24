<?php

class ymcPipeNodeWrongInputException extends ymcPipeException
{
    public function __construct( $nodeName, $nodeInput, $inputType )
    {
        parent::__construct( "Operation not possible. Node <$nodeName> got wrong input <".gettype($nodeInput)."> of previous pipe, should be a <".$inputType.">" );
    }
}
