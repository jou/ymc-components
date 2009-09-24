<?php

class ymcHtmlFormFailureNotInSet extends ymcHtmlFormFailure
{
    public function __construct( $elements )
    {
        parent::__construct( $elements, 'not_in_set' );
    }
}
