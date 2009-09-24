<?php

/**
 *
 * nickname
 * email
 * password
 * password_repeat
 * openid_url
 * 
 */
class ymcHtmlFormTestGroupUserRegistration extends ymcHtmlFormElementsGroupGeneric
{
    public function __construct()
    {
        parent::__construct( 'user_registration' );
        $this->build();
    }

    protected function build()
    {
        $this->addElements( array( 
            new ymcHtmlFormElementText( 'nickname' ),
            new ymcHtmlFormElementEmail( 'email' ),
            new ymcHtmlFormElementText( 'openid_url', array( 'emptyFailure' => NULL  ) ),
            new ymcHtmlFormElementPassword( 'password', array( 'emptyFailure' => NULL  ) ),
            new ymcHtmlFormElementPassword( 'password_repeat', array( 'emptyFailure' => NULL  ) )
        ) );
    }

    public function init( ymcHtmlForm $form )
    {
        $form->registerOnInit( $this );
    }

    public function validate( ymcHtmlFormInputSource $inputSource )
    {
        $failures = $this->validateElements( $inputSource );

        $failures = array_merge( $failures, $this->validateGroup() );
        $this->failures = $failures;

        return $this->failures;
    }

    protected function validateGroup()
    {
        $failures = array();
        $elements = $this->elements;

        if( !$elements['openid_url']->hasData() && !$elements['password']->hasData() )
        {
            $failures[] = new ymcHtmlFormFailure( array( 
                                  $elements['openid_url'],
                                  $elements['password']
                              ),
                              'neither_openid_nor_password'
                          );
        }

        if( $elements['password']->value !== $elements['password_repeat']->value )
        {
            $failures[] = new ymcHtmlFormFailure( array( 
                                  $elements['password_repeat']
                              ),
                              'password_repeat_failure'
                          );
        }

        return $failures;
    }
}
