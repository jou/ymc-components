<?php

require_once 'autoload.php';

class ymcHtmlFormFormsTestSuite extends PHPUnit_Framework_TestSuite
{
    public function __construct()
    {
        parent::__construct();
        $this->setName("ymcHtmlForm");

        $basedir = dirname( __FILE__ );
        $this->addTestFile( $basedir.'/user_registration.php' );
    }

    public static function suite()
    {
        return new self;
    }
}
