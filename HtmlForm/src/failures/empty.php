<?php

class ymcHtmlFormFailureEmpty extends ymcHtmlFormFailure
{
    public function __construct( $elements )
    {
        parent::__construct( $elements, 'empty' );
    }
}
