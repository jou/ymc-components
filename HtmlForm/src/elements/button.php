<?php

class ymcHtmlFormElementButton extends ymcHtmlFormElementBase
{
    protected $type = 'submit';

    public function init( ymcHtmlForm $form, ymcHtmlFormInputSource $inputSource = NULL )
    {
        $form->registerOnInit( $this );
        if( $inputSource )
        {
            $options = $this->options;
            $this->value = $inputSource->get( $this->name, 
                                              $options->filter,
                                              $options->filterOptions );

            if( $this->value )
            {
                $form->setButton( $this );
            }
        }
        return array();
    }
}
