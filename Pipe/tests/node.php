<?php

require_once "case.php";

class ymcPipeNodeTest extends ymcPipeTestCase
{
    public function testNodeGetPipe()
    {
        $pipe = new ymcPipe;
        $node = new ymcPipeBasicNodeMock( $pipe );
        $this->assertSame( $pipe, $node->pipe );
    }

    public function testNodeSetPipeFails()
    {
        $this->setExpectedException( 'ezcBasePropertyPermissionException' );
        $pipe = new ymcPipe;
        $node = new ymcPipeBasicNodeMock( $pipe );
        $node->pipe = null;
    }

    public function testXmlSerializationRoundtrip()
    {
        $node = new ymcPipeBasicNodeMock( $this->getMock( 'ymcPipe' ) );
        $node->config->setPropertiesTestHelper( array( 'hi' => 'du' ) );
        $document = new DOMDocument;
        $element = $document->createElement( 'xyzblub' );
        $document->appendChild( $element );
        $node->serializeToXml( $element );

        $newNode = ymcPipeNode::unserializeFromXml( $element, $this->getMock( 'ymcPipe' ) );
        $this->assertEquals( $node, $newNode );
    }

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite(__CLASS__);
    }
}
