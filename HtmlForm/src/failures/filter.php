<?php

class ymcHtmlFormFailureFilter extends ymcHtmlFormFailure
{
    public function __construct( $elements )
    {
        parent::__construct( $elements, 'filter' );
    }
}
