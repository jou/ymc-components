<?php

require_once 'autoload.php';

class ymcHtmlFormGenericTest extends PHPUnit_Framework_Testcase
{
    /**
     * @expectedException ymcHtmlFormDuplicateNameException
     */
    public function testDuplicateNameThrowsException()
    {
        $group1 = new ymcHtmlFormElementsGroupGeneric( 'group1' );
        $group1->add( new ymcHtmlFormElementText( 'same' ) );
        $group2 = new ymcHtmlFormElementsGroupGeneric( 'group2' );
        $group2->add( new ymcHtmlFormElementText( 'same' ) );
        $form = new ymcHtmlFormGeneric();
        $form->group->add( $group1 );
        $form->group->add( $group2 );
        $form->init();

        $this->fail( 'Expected exception' );
    }
}
