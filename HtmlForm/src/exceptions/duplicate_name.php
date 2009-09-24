<?php

class ymcHtmlFormDuplicateNameException extends ymcHtmlFormException
{
    public function __construct( $name )
    {
        parent::__construct( 'An Element with name '.$name.' has already been registered.' );
    }
}
