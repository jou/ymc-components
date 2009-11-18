<?php

require_once dirname( __FILE__ ).'/../../src/stream_wrappers/variable.php';

class ymcStandardStreamWrapperVariableTest extends PHPUnit_Framework_TestCase
{
    private static $textdata = 'hallo welt. hello world."\\ö"';

    public function setUp()
    {
        ymcStandardStreamWrapperVariable::register( 'ymcvar' );
    }

    public function testWriteCsv()
    {
        $data = array( 
            array( 1, 2, 3 ),
            array( 4, 5, 6 ),
            array( 7, 8, 9 ),
            array( 'hallo', 'welt', ',' ),
            array( '"', '""', 'test\\",' ),
        );
        $csv = <<<EOCSV
1,2,3
4,5,6
7,8,9
hallo,welt,","
"""","""""","test\","

EOCSV;

        $fp = fopen('ymcvar://'.__FUNCTION__, 'w+');

        foreach( $data as $line )
        {
            fputcsv( $fp, $line );
        }

        rewind( $fp );
        $this->assertEquals( $csv, stream_get_contents($fp) );
    }

    public function testGetContent()
    {
        $stream = 'ymcvar://'.__FUNCTION__;
        $fp = fopen( $stream, 'w');

        fputs( $fp, self::$textdata );

        $this->assertEquals( self::$textdata, ymcStandardStreamWrapperVariable::getContent( $stream ) );
    }

    public function testPutAndGetContent()
    {
        $stream = 'ymcvar://'.__FUNCTION__;
        ymcStandardStreamWrapperVariable::putContent( $stream, self::$textdata );
        $this->assertEquals( self::$textdata, ymcStandardStreamWrapperVariable::getContent( $stream ) );
    }

    public function testPutAndReadContent()
    {
        $stream = 'ymcvar://'.__FUNCTION__;
        ymcStandardStreamWrapperVariable::putContent( $stream, self::$textdata );

        $fp = fopen( $stream, 'r');
        $this->assertEquals( self::$textdata, stream_get_contents( $fp ) );
    }

    public function tearDown()
    {
        ymcStandardStreamWrapperVariable::unregister( 'ymcvar' );
    }
}
