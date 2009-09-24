<?php

require_once "case.php";
require_once 'mock/node_basic.php';

class ymcPipeDefinitionStorageXmlTest extends ymcPipeTestCase
{
    public function testSaveToDocument()
    {
        $pipe = $this->getComplexPipe();

        $doc = ymcPipeDefinitionStorageXml::saveToDocument( $pipe );
        $this->assertXmlStringEqualsXmlFile( TESTPATH.'data/complex_pipe.xml', $doc->saveXML() );
    }

    public function testLoadFromDocument()
    {
        $document = new DOMDocument;
        $document->load( TESTPATH.'data/complex_pipe.xml' ) ;
        $pipeFromXml = ymcPipeDefinitionStorageXml::loadFromDocument( $document );
        $origPipe = $this->getComplexPipe();
        // To populate the id's
        ymcPipeDefinitionStorageXml::saveToDocument( $origPipe );
        
        $this->assertPipeEquals( $origPipe, $pipeFromXml );
    }

    public function testXmlSerializationRoundtrip()
    {
        $pipe = $this->getComplexPipe();

        $doc = ymcPipeDefinitionStorageXml::saveToDocument( $pipe );
        $newPipe = ymcPipeDefinitionStorageXml::loadFromDocument( $doc );
        $this->assertPipeEquals( $pipe, $newPipe );
    }

    public function testXmlSerializationSetsNodeIds()
    {
        $pipe = $this->getComplexPipe();
        ymcPipeDefinitionStorageXml::saveToDocument( $pipe );

        foreach( $pipe->nodes as $node )
        {
            $this->assertTrue( is_int( $node->id ) || is_string( $node->id ) );
        }
    }

    public function testXmlSerializationSetsUniqueNodeIds()
    {
        $pipe = $this->getComplexPipe();
        $ids = array();
        ymcPipeDefinitionStorageXml::saveToDocument( $pipe );

        foreach( $pipe->nodes as $node )
        {
            $this->assertNotContains( $node->id, $ids );
            $ids[] = $node->id;
        }
    }

    public function testXmlUnserializationSetsOutNodes()
    {
        $document = new DOMDocument;
        $document->load( TESTPATH.'data/pipe_with_outnodes.xml' ) ;
        $pipe = ymcPipeDefinitionStorageXml::loadFromDocument( $document );

        $outNodesCount = 0;
        foreach( $pipe->nodes as $node )
        {
            $outNodesCount += count( $node->outNodes );
        }
        $this->assertEquals( 6, $outNodesCount );
    }

    public function testNodeHaveIdsAfterSave()
    {
        //@todo
        $pipe = $this->getComplexPipe();

        $doc = ymcPipeDefinitionStorageXml::saveToDocument( $pipe );

    }

    public function testConstructorNeedsDirectoryParameter()
    {
        $this->setExpectedException( 'ymcPipeDefinitionStorageException' );
        new ymcPipeDefinitionStorageXml( 'NOTADIRECTORY' );
    }

    public function testGetDirectory()
    {
        $defStore=new ymcPipeDefinitionStorageXml( TESTPATH );
        $this->assertEquals( TESTPATH, $defStore->directory );
    }

    public function testLoadByNameReturnsPipe()
    {
        $defStore=new ymcPipeDefinitionStorageXml( TESTPATH.'data' );
        $pipe = $defStore->loadByName( 'complex_pipe' );
        $this->assertType( 'ymcPipe', $pipe );
    }

    public function testLoadByNameSetsPipeName(  )
    {
        $defStore=new ymcPipeDefinitionStorageXml( TESTPATH.'data' );
        $pipe = $defStore->loadByName( 'complex_pipe' );
        $this->assertEquals( 'complex_pipe', $pipe->name );

    }


    public function testSaveUnnamedPipeThrowsException()
    {
        $pipe = new ymcPipe;
        $pipe->name = '';
        $dir = $this->getTempDir();
        $defStore=new ymcPipeDefinitionStorageXml( $dir );
        $this->setExpectedException( 'ymcPipeDefinitionStorageException' );
        $defStore->save( $pipe );
    }

    public function testSaveCreatesXmlFile()
    {
        $dir = $this->getTempDir();
        $defStore=new ymcPipeDefinitionStorageXml( $dir );
        $pipe = $this->getComplexPipe();
        $pipe->name = 'testname';
        $defStore->save( $pipe );
        $dom = new DOMDocument;
        $this->assertTrue( $dom->load( $defStore->getFilename( $pipe->name, FALSE ) ) );
    }

    public function testNodeIdsRemainTheSame()
    {
        $pipe = $this->getComplexPipe();
        $newPipe = ymcPipeDefinitionStorageXml::loadFromDocument( 
            ymcPipeDefinitionStorageXml::saveToDocument( 
                ymcPipeDefinitionStorageXml::loadFromDocument( 
                    ymcPipeDefinitionStorageXml::saveToDocument( 
                        $pipe
                    )
                )
            )
        );
        $this->assertNodeListEquals( $pipe->nodes, $newPipe->nodes );
    }

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite(__CLASS__);
    }
}
