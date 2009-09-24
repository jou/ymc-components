<?php

require_once "case.php";

class ymcPipeNodeGraphTest extends ymcPipeTestCase
{
    public function testAddInNode()
    {
        $nodeParent = $this->getNode();
        $nodeChild  = $this->getNode();

        $nodeChild->addInNode( $nodeParent );

        // assertions
        $parentInNodes  = $nodeParent->inNodes;
        $parentOutNodes = $nodeParent->outNodes;
        $childInNodes   = $nodeChild->inNodes;
        $childOutNodes  = $nodeChild->outNodes;

        $this->assertSame( $nodeChild, $parentOutNodes[0] );
        $this->assertSame( $nodeParent, $childInNodes[0] );

        $this->assertEquals( 0, count( $parentInNodes ) );
        $this->assertEquals( 1, count( $parentOutNodes ) );
        $this->assertEquals( 0, count( $childOutNodes ) );
        $this->assertEquals( 1, count( $childInNodes ) );

        $this->assertEquals( 0, $nodeParent->numInNodes );
        $this->assertEquals( 1, $nodeParent->numOutNodes );
        $this->assertEquals( 1, $nodeChild->numInNodes );
        $this->assertEquals( 0, $nodeChild->numOutNodes );
    }

    public function testAddOutNode()
    {
        $nodeParent = $this->getNode();
        $nodeChild  = $this->getNode();

        $nodeParent->addOutNode( $nodeChild );

        // assertions
        $parentInNodes  = $nodeParent->inNodes;
        $parentOutNodes = $nodeParent->outNodes;
        $childInNodes   = $nodeChild->inNodes;
        $childOutNodes  = $nodeChild->outNodes;

        $this->assertSame( $nodeChild, $parentOutNodes[0] );
        $this->assertSame( $nodeParent, $childInNodes[0] );
        $this->assertEquals( 0, count( $parentInNodes ) );
        $this->assertEquals( 1, count( $parentOutNodes ) );
        $this->assertEquals( 0, count( $childOutNodes ) );
        $this->assertEquals( 1, count( $childInNodes ) );

        $this->assertEquals( 0, $nodeParent->numInNodes );
        $this->assertEquals( 1, $nodeParent->numOutNodes );
        $this->assertEquals( 1, $nodeChild->numInNodes );
        $this->assertEquals( 0, $nodeChild->numOutNodes );
    }

    public function testAddMultipleOutNodes()
    {
        $nodeParent = $this->getNode();
        $nodeChild1  = $this->getNode();
        $nodeChild2  = $this->getNode();
        $nodeChild3  = $this->getNode();

        $nodeParent->addOutNode( $nodeChild1 );
        $nodeParent->addOutNode( $nodeChild2 );
        $nodeParent->addOutNode( $nodeChild3 );

        // assertions
        $parentInNodes  = $nodeParent->inNodes;
        $parentOutNodes = $nodeParent->outNodes;
        $childInNodes   = $nodeChild2->inNodes;
        $childOutNodes  = $nodeChild2->outNodes;

        $this->assertSame( $nodeChild2, $parentOutNodes[1] );
        $this->assertSame( $nodeParent, $childInNodes[0] );

        $this->assertNotSame( $nodeChild2, $parentOutNodes[0] );
        $this->assertNotSame( $nodeChild2, $parentOutNodes[2] );

        $this->assertEquals( 0, count( $parentInNodes ) );
        $this->assertEquals( 3, count( $parentOutNodes ) );
        $this->assertEquals( 0, count( $childOutNodes ) );
        $this->assertEquals( 1, count( $childInNodes ) );

        $this->assertEquals( 0, $nodeParent->numInNodes );
        $this->assertEquals( 3, $nodeParent->numOutNodes );
        $this->assertEquals( 1, $nodeChild2->numInNodes );
        $this->assertEquals( 0, $nodeChild2->numOutNodes );
    }

    public function testConnectNodesOfDifferentPipesFails()
    {
        $this->setExpectedException('ymcPipeNodeDifferentPipesException');
        $nodeParent = $this->getNode('a');
        $nodeChild  = $this->getNode('b');

        $nodeParent->addOutNode( $nodeChild );
    }

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite(__CLASS__);
    }

}
