<?php

class ymcHtmlFormInputSourceDummy implements ymcHtmlFormInputSource
{
    protected $input;

    public function __construct( $input = array() )
    {
        $this->input = $input;
    }

    public function has( $name )
    {
        return array_key_exists( $name, $this->input );
    }

    public function get( $name, $filter = FILTER_DEFAULT, $options = array() )
    {
        if( !$this->has( $name ) )
        {
            return NULL;
        }
        $value = filter_var( $this->input[$name], $filter, $options );
        return $value;
    }

    public function getUnsafeRaw( $name )
    {
        if( !$this->has( $name ) )
        {
            return NULL;
        }
        return $this->input[$name];
    }

    public function __set( $name, $property )
    {
        $this->input[$name] = $property;
    }

    public function hasData()
    {
        return count( $this->input ) > 0;
    }
}
