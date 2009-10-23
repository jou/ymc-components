<?php

require_once dirname( __FILE__ ).'/../../src/PersistentObject/named_bitset.php';

class ymcEzcPersistentObjectNamedBitSetTest extends PHPUnit_Framework_TestCase
{
    protected $goodBitSetMapping = array( 
        0 => 'monday',
        1 => 'tuesday',
        2 => 'wednesday',
        3 => 'thursday',
        4 => 'friday',
        5 => 'saturday',
        6 => 'sunday'
    );

    public function testGetIntegerReturnsValueFromSetInteger()
    {
        $bitset = new ymcEzcPersistentObjectNamedBitSet;
        $integer = 42;

        $bitset->setInteger( $integer );
        $this->assertEquals( $integer, $bitset->getInteger() );
    }

    public function testBitSetMappingCanBeSetAndIsReturned()
    {
        $bitset = new ymcEzcPersistentObjectNamedBitSet;
        $bitset->setBitSetMapping( $this->goodBitSetMapping );
        $this->assertEquals( $this->goodBitSetMapping, $bitset->getNames() );
    }

    public function testBitsAreInitiallyFalse()
    {
        $bitset = new ymcEzcPersistentObjectNamedBitSet;
        $bitset->setBitSetMapping( $this->goodBitSetMapping );
        foreach( $this->goodBitSetMapping as $name )
        {
            $this->assertFalse( $bitset->$name );
        }
    }

    public function testSetBitByNameAndProperty()
    {
        $bitset = new ymcEzcPersistentObjectNamedBitSet;
        $bitset->setBitSetMapping( $this->goodBitSetMapping );

        $bitset->tuesday = TRUE;
        $this->assertTrue( $bitset->tuesday );
        $this->assertFalse( $bitset->wednesday );
    }

    public function testSetBitByNameAndArrayAccess()
    {
        $bitset = new ymcEzcPersistentObjectNamedBitSet;
        $bitset->setBitSetMapping( $this->goodBitSetMapping );

        $bitset['wednesday'] = TRUE;
        $this->assertTrue( $bitset['wednesday'] );
        $this->assertFalse( $bitset['tuesday'] );
    }

    public function testSetBitByPositionAndArrayAccess()
    {
        $bitset = new ymcEzcPersistentObjectNamedBitSet;
        $bitset->setBitSetMapping( $this->goodBitSetMapping );

        $bitset[1] = TRUE;
        $this->assertTrue( $bitset[1] );
        $this->assertFalse( $bitset[2] );
    }

    public function testFirstNameMappedToOne()
    {
        $bitset = new ymcEzcPersistentObjectNamedBitSet;
        $bitset->setBitSetMapping( $this->goodBitSetMapping );

        $bitset[0] = TRUE;
        $this->assertEquals( 1, $bitset->getInteger() );
    }

    public function testSetBitTrueAndBackToFalse()
    {
        $bitset = new ymcEzcPersistentObjectNamedBitSet;
        $bitset->setBitSetMapping( $this->goodBitSetMapping );

        $bitset[1] = TRUE;
        $bitset[1] = FALSE;
        $this->assertFalse( $bitset[1] );
    }

    public function testSetBitTrueAndBackToFalseWhileOthersRemainTrue()
    {
        $bitset = new ymcEzcPersistentObjectNamedBitSet;
        $bitset->setBitSetMapping( $this->goodBitSetMapping );

        $bitset[1] = TRUE;
        $bitset[2] = TRUE;
        $bitset[3] = TRUE;

        $bitset[2] = FALSE;

        $this->assertTrue( $bitset[1] );
        $this->assertFalse( $bitset[2] );
        $this->assertTrue( $bitset[3] );
    }

    public function testNamedBitSetIsSerializable()
    {
        $bitset = new ymcEzcPersistentObjectNamedBitSet;
        $bitset[1] = TRUE;
        $bitset[3] = TRUE;

        $serialized = serialize( $bitset );
        $newBitset = unserialize( $serialized );

        $this->assertEquals( $bitset->getInteger(), $newBitset->getInteger() );
    }
}
