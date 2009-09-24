<?php

require_once "case.php";
class ymcPipeHtmlDomDocumentTest extends ymcPipeTestCase
{
    /**
     * @dataProvider getBaseHrefProvider
     */
    public function testGetUrlBase( $file, $base )
    {
        $html = file_get_contents( dirname( __FILE__ ).'/data/html/'.$file );

        $dom = ymcPipeHtmlDomDocument::createFromHTML( $html );

        $this->assertEquals( $base, $dom->getBaseHref() );
    }

    public function getBaseHrefProvider()
    {
        return array( 
                array( 'soaktuell_ch.html', 'http://www.soaktuell.ch/' ),
                array( 'empty.html', NULL )
        );
    }
}
