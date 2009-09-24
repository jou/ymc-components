<?php

require_once dirname( __FILE__ )."/../src/classes/robots_txt.php";

class memoBaseRobotsTxtTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider parseProvider
     */
    public function testParse( $file, $lines )
    {
        $txt = $this->getFile( $file );
        $robotsTxt = new memoBaseRobotsTxt( $txt );
    }

    public function parseProvider()
    {
        return array( 
            array( 'faz_net', '' ),
            array( 'fr-online_de', '' )
        );
    }

    /**
     * @dataProvider mayCrawlProvider
     */
    public function testMayCrawl( $file, $tests )
    {
        $txt = $this->getFile( $file );
        $robotsTxt = new memoBaseRobotsTxt( $txt );

        foreach( $tests as $test )
        {
            $this->assertEquals( $tests[1], $robotsTxt->mayCrawl( $tests[2], $tests[0] ) );
        }
    }

    public function mayCrawlProvider()
    {
        return array(
            array( 'faz_net', array( 
                '*', TRUE, '/hallo',
                '*', FALSE, '/p/',
            ) ),

            array( 'fr-online_de', array( 
                '*', TRUE, '/hallo',
                'Slurp', FALSE, '/hallo',
                'Slurp', FALSE, '/',
                '*', FALSE, '/bin',
                'Googlebot', FALSE, '/bin/',
                '*', FALSE, '/bin/',
                'Googlebot', TRUE, '/hallo',
            ) )
        );
    }

    protected function getFile( $file )
    {
        return file_get_contents( dirname( __FILE__ ).'/data/robots_txt/'.$file );
    }
}
