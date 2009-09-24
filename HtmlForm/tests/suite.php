<?php

require_once 'autoload.php';

class ymcHtmlFormTestSuite extends PHPUnit_Framework_TestSuite
{
    public function __construct()
    {
        parent::__construct();
        $this->setName("ymcHtmlForm");

        $this->addTestFile( 'element_base.php' );
        $this->addTestFile( 'input_source_dummy.php' );
        $this->addTestFile( 'forms/suite.php' );
        $this->addTestFile( 'form_generic.php' );
        $this->addTestFile( 'elements_group_generic.php' );
    }

    public static function suite()
    {
        return new self;
    }
}
