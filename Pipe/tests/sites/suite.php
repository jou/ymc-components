<?php

require_once 'PHPUnit/Framework/TestSuite.php';

class ymcPipeSitesTestSuite extends PHPUnit_Framework_TestSuite
{
    public function __construct()
    {
        parent::__construct();
        $this->setName("ymcPipeSites");

        $suites = glob( dirname( __FILE__ ).'/*/suite.php' );
        $this->addTestFiles( $suites );
    }

    public static function suite()
    {
        return new self;
    }
}
