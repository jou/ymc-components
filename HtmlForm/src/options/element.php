<?php

class ymcHtmlFormElementOptions extends ezcBaseOptions
{
    protected $properties = array( 
        'filter'        => FILTER_DEFAULT,
        'filterOptions' => array(),
        'validator'     => NULL,
        'required'      => TRUE,
        'emptyFailure'  => 'ymcHtmlFormFailureEmpty',
        'filterFailure' => 'ymcHtmlFormFailureFilter'
    );

    public function __set( $name, $value )
    {
        switch( $name )
        {
            case 'filter':
            case 'validator':
            case 'filterOptions':
            case 'required':
            case 'emptyFailure':
            case 'filterFailure':
                $this->properties[$name] = $value;
                return;
        }
        throw new ezcBasePropertyNotFoundException( $name );
    }
}
