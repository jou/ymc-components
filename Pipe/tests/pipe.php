<?php

require_once "case.php";
require_once 'mock/node_basic.php';

class ymcPipeTest extends ymcPipeTestCase
{
    public function testCreateNode()
    {
        $pipe = new ymcPipe;
        $node = $pipe->createNode( 'ymcPipeBasicNodeMock', 'myname' );
        $this->assertTrue( false !== $pipe->nodes->contains( $node ) );
        $this->assertEquals( 'myname', $node->name );
    }

    public function testDeleteNode()
    {
        $pipe = new ymcPipe;
        $node = new ymcPipeBasicNodeMock( $pipe );
        $pipe->deleteNode( $node );
        $this->assertFalse( $pipe->nodes->contains( $node ) );
    }

    public function testDeleteNodeDeletesNodeProperties()
    {
        $pipe = new ymcPipe;
        $node = new ymcPipeBasicNodeMock( $pipe );
        $pipe->deleteNode( $node );
        $this->assertFalse( isset( $node->config ) );
        $this->assertFalse( isset( $node->inNodes ) );
        $this->assertFalse( isset( $node->outNodes ) );
        $this->assertFalse( isset( $node->id ) );
    }

    public function testDeleteNodeDeletesReferencesInConnectedNodes()
    {
        $i = 3;
        while( $i-- )
        {
            $node[$i] = $this->getNode();
        }

        $node[0]->addOutNode( $node[1] );
        $node[0]->addInNode( $node[2] );

        $node[0]->pipe->deleteNode( $node[0] );
        $this->assertFalse( $node[1]->inNodes->contains( $node[0] ) );
        $this->assertFalse( $node[2]->outNodes->contains( $node[0] ) );
    }

    public function testNodesReturnsAllNodes()
    {
        $pipe = $this->getPipeWithXNodes( 6 );
        $nodes = $pipe->nodes;

        $this->assertEquals( 5, count( $nodes ) );
        foreach( $nodes as $node )
        {
            $this->assertTrue( $node instanceof ymcPipeNode );
        }
    }

    public function testGetStartNodes()
    {
        $i = 4;
        while( $i-- )
        {
            $node[$i] = $this->getNode();
        }

        $node[0]->addOutNode( $node[1] );
        $node[2]->addOutNode( $node[3] );
        $this->assertEquals( 2, count( $node[0]->pipe->getStartNodes() ) );
    }

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite(__CLASS__);
    }
}
