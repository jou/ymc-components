<?php

require_once 'autoload.php';

class ymcHtmlFormElementsGroupGenericTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException ymcHtmlFormDuplicateNameException
     */
    public function testDuplicateNameThrowsException()
    {
        $group = new ymcHtmlFormElementsGroupGeneric( 'group' );
        $group->add( new ymcHtmlFormElementText( 'same' ) );
        $group->add( new ymcHtmlFormElementText( 'same' ) );

        $this->fail( 'Expected exception' );
    }
}
