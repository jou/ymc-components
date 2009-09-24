<?php

require_once dirname( __FILE__ ).'/../case.php';
require_once dirname( __FILE__ ).'/../../mock/node_input_provider.php';

class ymcPipeSiteFreesoftwaremagazineExtractTest extends ymcPipeSitesTestCase
{
    /**
     * @dataProvider htmlSitesInDataDir
     */
    public function testGetTitle( $html, $data )
    {
        $this->input['html'] = file_get_contents( $html );
        $regexpNode = new ymcPipePregMatchNode( $this->pipe );
        $regexpNode->config->regexp = '(<title>(.*?)\s*</title>)';
        
        $this->execute( $regexpNode );
        $this->assertEquals( $data['title'], $regexpNode->getOutput() );
    }

    /**
     * @dataProvider htmlSitesInDataDir
     */
    public function testGetAuthor( $html, $data )
    {
        $this->input['html'] = file_get_contents( $html );
        $regexpNode = new ymcPipePregMatchNode( $this->pipe );
        $regexpNode->config->regexp = '(li class="author">[^>]+>(.+?)</a>)';
        
        $this->execute( $regexpNode );
        $this->assertEquals( $data['author'], $regexpNode->getOutput() );
    }

    /**
     * @dataProvider htmlSitesInDataDir
     */
    public function testGetAuthorLink( $html, $data )
    {
        $this->input['html'] = file_get_contents( $html );
        $regexpNode = new ymcPipePregMatchNode( $this->pipe );
        $regexpNode->config->regexp = '(li class="author">[^"]+"(.+?)")';
        
        $this->execute( $regexpNode );
        $this->assertEquals( $data['author_link'], $regexpNode->getOutput() );
    }

    protected function execute( ymcPipeNode $node )
    {
        $inNode = $this->inNode;
        $inNode->addOutNode( $node );

        $execution = new ymcPipeExecutionNonSuspendable;
        $execution->pipe = $inNode->pipe;

        $inNode->pipe->accept( new ymcPipeSetIdVisitor );

        $execution->start();
    }

    public static function htmlSitesInDataDir()
    {
        $htmlFiles = glob( dirname( __FILE__ ).'/data/*.html' );
        return self::getHtmlAndData( $htmlFiles );
    }

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite(__CLASS__);
    }
}
