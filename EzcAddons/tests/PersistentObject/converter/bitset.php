<?php

require_once 'ezc/Base/ezc_bootstrap.php';
require_once dirname( __FILE__ ).'/../../../src/PersistentObject/converter/bitset.php';
require_once dirname( __FILE__ ).'/../mocks/weekdays_bitset.php';

class ymcEzcPersistentObjectBitSetConverterTest extends PHPUnit_Framework_TestCase
{
    public function testZeroConvertsToAllFalse()
    {
        $conv = new ymcEzcPersistentObjectBitSetConverter( 'mockymcEzcPersistentObjectWeekdaysBitSet' );
        $weekdays = $conv->fromDatabase( 0 );

        for( $i = 0; $i < 7; ++$i )
        {
            $this->assertFalse( $weekdays[$i] );
        }
    }

    public function testThreeConvertsToMondayTuesdayTrue()
    {
        $conv = new ymcEzcPersistentObjectBitSetConverter( 'mockymcEzcPersistentObjectWeekdaysBitSet' );
        $weekdays = $conv->fromDatabase( 3 );

        for( $i = 0; $i < 2; ++$i )
        {
            $this->assertTrue( $weekdays[$i] );
        }
        for( $i = 2; $i < 7; ++$i )
        {
            $this->assertFalse( $weekdays[$i] );
        }
    }

    public function testAllFalseConvertsToZero()
    {
        $weekdays = new mockymcEzcPersistentObjectWeekdaysBitSet;
        $conv = new ymcEzcPersistentObjectBitSetConverter( 'mockymcEzcPersistentObjectWeekdaysBitSet' );

        $this->assertEquals( 0, $conv->toDatabase( $weekdays ) );
    }

    public function testMondayTuesdayTrueConvertsToThree()
    {
        $weekdays = new mockymcEzcPersistentObjectWeekdaysBitSet;
        $conv = new ymcEzcPersistentObjectBitSetConverter( 'mockymcEzcPersistentObjectWeekdaysBitSet' );

        $weekdays->monday  = TRUE;
        $weekdays->tuesday = TRUE;

        $this->assertEquals( 3, $conv->toDatabase( $weekdays ) );
    }
}
