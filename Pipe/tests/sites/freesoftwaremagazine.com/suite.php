<?php

require_once 'PHPUnit/Framework/TestSuite.php';

class ymcPipeFreesoftwaremagazineTestSuite extends PHPUnit_Framework_TestSuite
{
    public function __construct()
    {
        // The upper suite includes this suite via addTestFiles, which would also automatically
        // all required classes during this call.
        require_once 'regexp_extract.php';
        parent::__construct();
        $this->setName("freesoftwaremagazine.com");

        $this->addTest( ymcPipeSiteFreesoftwaremagazineExtractTest::suite() );
    }

    public static function suite()
    {
        return new self;
    }
}
