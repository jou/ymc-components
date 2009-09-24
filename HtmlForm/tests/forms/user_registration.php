<?php

require_once dirname( __FILE__ ).'/../autoload.php';
require_once 'definitions/user_registration.php';

class ymcHtmlFormUserRegistrationTest extends PHPUnit_Framework_Testcase
{
    protected $group;
    protected $form;
    protected $input;

    public function setUp()
    {
        $this->group = new ymcHtmlFormTestGroupUserRegistration;
        $this->form  = new ymcHtmlFormGeneric( $this->group );
        $this->input = new ymcHtmlFormInputSourceDummy; 
    }

    public function testInstantiateGroup()
    {
        $this->assertThat( $this->group, $this->isInstanceOf( 'ymcHtmlFormElementsGroup' ) );
    }

    public function testEmptyInputIsInvalid()
    {
        $this->form->init();
        $this->form->validate( $this->input );
        $this->assertFalse( $this->form->isValid() );
    }

    public function testGoodInputIsValid()
    {
        $input = $this->input;
        $input->nickname = 'thkoch';
        $input->password = 'wontsay';
        $input->password_repeat = 'wontsay';
        $input->email = 'thomas@koch.ro';

        $this->form->init();
        $this->form->validate( $this->input );
        $this->assertTrue( $this->form->isValid() );
    }

    public function testNoCredentialsIsInvalid()
    {
        $input = $this->input;
        $input->nickname = 'thkoch';
        $input->email = 'thomas@koch.ro';

        $this->form->init();
        $this->form->validate( $this->input );
        $this->assertFalse( $this->form->isValid() );
    }

    public function testCredentialOpenidIsValid()
    {
        $input = $this->input;
        $input->nickname = 'thkoch';
        $input->email = 'thomas@koch.ro';
        $input->openid_url = 'http://thomas.koch.ro';

        $this->form->init();
        $this->form->validate( $this->input );
        $this->assertTrue( $this->form->isValid() );
    }
}
