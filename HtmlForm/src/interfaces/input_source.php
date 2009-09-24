<?php

interface ymcHtmlFormInputSource
{
    public function has( $name );

    public function get( $name, $filter = FILTER_DEFAULT, $options = NULL );

    public function getUnsafeRaw( $name );

    public function hasData();
}
