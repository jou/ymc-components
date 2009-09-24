<?php

require_once "case.php";

class ymcPipeExecutionNonSuspendableTest extends ymcPipeTestCase
{
    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite(__CLASS__);
    }
}
