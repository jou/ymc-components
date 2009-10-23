<?php

require_once 'ezc/Base/ezc_bootstrap.php';
require_once dirname( __FILE__ ).'/../../../src/PersistentObject/converter/separated_list.php';

class ymcEzcPersistentObjectBitSetConverterTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyStringToEmptyArray()
    {
        $conv = new ymcEzcPersistentObjectSeparatedListConverter;

        $this->assertEquals( array(), $conv->fromDatabase( '' ) );
    }

    public function testOneStringToOneElementArray()
    {
        $conv = new ymcEzcPersistentObjectSeparatedListConverter;

        $this->assertEquals( array('hallo welt'), $conv->fromDatabase( 'hallo welt' ) );
    }

    public function testTwoStringsToTwoElementsArray()
    {
        $conv = new ymcEzcPersistentObjectSeparatedListConverter;

        $this->assertEquals( array('hallo welt','und mond'), $conv->fromDatabase( 'hallo welt,und mond' ) );
    }

    public function testEmptyArrayToEmptyString()
    {
        $conv = new ymcEzcPersistentObjectSeparatedListConverter;

        $this->assertEquals( '', $conv->toDatabase( array() ) );
    }

    public function testOneElementArrayToOneString()
    {
        $conv = new ymcEzcPersistentObjectSeparatedListConverter;

        $this->assertEquals( 'hallo leute', $conv->toDatabase( array('hallo leute') ) );
    }

    public function testTwoElementsArrayToTwoStrings()
    {
        $conv = new ymcEzcPersistentObjectSeparatedListConverter;

        $this->assertEquals( 'hallo leute,hallo tiere', $conv->toDatabase( array('hallo leute', 'hallo tiere') ) );
    }
}
