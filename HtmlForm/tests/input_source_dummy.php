<?php

require_once dirname( __FILE__ ).'/autoload.php';

class ymcHtmlFormInputSourceDummyTest extends PHPUnit_Framework_TestCase
{
    public function testHasPositive()
    {
        $inputSource = new ymcHtmlFormInputSourceDummy( array( 'testkey' => 'testvalue' ) );

        $this->assertTrue( $inputSource->has( 'testkey' ) );
    }

    public function testHasNegative()
    {
        $inputSource = new ymcHtmlFormInputSourceDummy();

        $this->assertFalse( $inputSource->has( 'testkey' ) );
    }

    public function testSetValue()
    {
        $inputSource = new ymcHtmlFormInputSourceDummy();
        $inputSource->testkey = 'testvalue';

        $this->assertTrue( $inputSource->has( 'testkey' ) );
        $this->assertEquals( 'testvalue', $inputSource->getUnsafeRaw( 'testkey' ) );
    }

    public function testGetValue()
    {
        $inputSource = new ymcHtmlFormInputSourceDummy();
        $inputSource->testkey = 'testvalue';

        $this->assertTrue( $inputSource->has( 'testkey' ) );
        $this->assertEquals( 'testvalue', $inputSource->get( 'testkey' ) );
    }

    public function testFilterValue()
    {
        $inputSource = new ymcHtmlFormInputSourceDummy();
        $inputSource->testkey = 'testvalue';

        $this->assertTrue( $inputSource->has( 'testkey' ) );
        $this->assertEquals( NULL, $inputSource->get( 'testkey', FILTER_VALIDATE_INT ) );
    }
}
