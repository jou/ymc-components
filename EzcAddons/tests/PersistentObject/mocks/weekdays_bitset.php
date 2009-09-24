<?php

require_once dirname( __FILE__ ).'/../../../src/PersistentObject/named_bitset.php';

class mockymcEzcPersistentObjectWeekdaysBitSet extends ymcEzcPersistentObjectNamedBitSet
{
    public $weekdaysBitSetMapping = array( 
        0 => 'monday',
        1 => 'tuesday',
        2 => 'wednesday',
        3 => 'thursday',
        4 => 'friday',
        5 => 'saturday',
        6 => 'sunday'
    );

    public function __construct()
    {
        $this->setBitSetMapping( $this->weekdaysBitSetMapping );
    }
}
