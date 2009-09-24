<?php

class ymcHtmlFormElementSingleSelect extends ymcHtmlFormElementBase
{
    protected $type = 'select';

    public function __construct( $name )
    {
        parent::__construct( $name );
    }

    protected function filter( ymcHtmlFormInputSource $inputSource )
    {
        $options = $this->options;

        $value = $inputSource->get( $this->name, 
                                    $options->filter,
                                    $options->filterOptions );
        
        if( !in_array( $value, $this->values ) )
        {
            $value = '';
            $this->failures[] = new ymcHtmlFormFailureNotInSet( $this );
        }
        return $value;
    }
}
