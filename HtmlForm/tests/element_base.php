<?php

require_once 'autoload.php';

class ymcHtmlFormElementBaseTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $element = new ymcHtmlFormElementText( 'dummyname' );

        $this->assertThat( $element, $this->isInstanceOf( 'ymcHtmlFormElement' ) );
    }

    public function testParseAndGet()
    {
        $element = new ymcHtmlFormElementText( 'dummyname' );
        $inputSource = new ymcHtmlFormInputSourceDummy( array( 'dummyname' => 'testvalue' ) );
        $element->validate( $inputSource );

        $this->assertEquals( 'testvalue', $element->value );
    }
}
