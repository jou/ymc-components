<?php

class ymcHtmlFormElementRadio extends ymcHtmlFormElementBase
{
    protected $type = 'radio';

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
