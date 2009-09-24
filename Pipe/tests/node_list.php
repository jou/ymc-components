<?php

require_once "case.php";

class ymcPipeNodeListTest extends ymcPipeTestCase
{
    public function testAddIfNotContainedTrueOnEmptyList()
    {
        $list = new ymcPipeNodeList;
        $this->assertTrue( $list->addIfNotContained( $this->getNode(  )  ) );
        $this->assertEquals( 1, count( $list ) );
    }

    public function testAddIfNotContainedTrueOnFilledList()
    {
        $list = new ymcPipeNodeList;
        $list[] = $this->getNode();
        $list[] = $this->getNode();
        $this->assertTrue( $list->addIfNotContained( $this->getNode(  )  ) );
        $this->assertEquals( 3, count( $list ) );
    }

    public function testAddIfNotContainedFalse()
    {
        $list = new ymcPipeNodeList;
        $node = $this->getNode();
        $list[] = $node;
        $this->assertFalse( $list->addIfNotContained( $node ) );
        $this->assertEquals( 1, count( $list ) );
    }

    public function testExceptionOnWrongValueSet()
    {
        $this->setExpectedException( 'ymcPipeNodeListException' );
        $list = new ymcPipeNodeList;
        $list[] = null;
    }

    public function testExceptionOnWrongOffsetSet()
    {
        $this->setExpectedException( 'ymcPipeNodeListException' );
        $list = new ymcPipeNodeList;
        $list[5.6] = null;
    }

    public function testExceptionOnNonExistingOffsetGet()
    {
        $this->setExpectedException( 'ymcPipeNodeListException' );
        $list = new ymcPipeNodeList;
        $list[5];
    }

    public function testSetNodeWithoutErrors()
    {
        $list = new ymcPipeNodeList;
        $list[] = $this->getNode();
        $list[] = $this->getNode();
        $list[0] = $this->getNode();
        $list[9] = $this->getNode();
    }

    public function testSetAndGetNodes()
    {
        $list = new ymcPipeNodeList;
        $node = $this->getNode();
        $list[0] = $node;
        $this->assertNodeEquals( $node, $list[0] );
    }

    public function testOffsetExistsFalse()
    {
        $list = new ymcPipeNodeList;
        $this->assertFalse( isset( $list[0] ) );
    }

    public function testOffsetExistsTrue()
    {
        $list = new ymcPipeNodeList;
        $node = $this->getNode();
        $list[0] = $node;
        $this->assertTrue( isset( $list[0] ) );
    }

    public function testOffsetUnsetOnNonExisting()
    {
        $this->setExpectedException( 'ymcPipeNodeListException' );
        $list = new ymcPipeNodeList;
        unset( $list[1] );
    }

    public function testOffsetUnsetOnExisting()
    {
        $list = new ymcPipeNodeList;
        $list[1] = $this->getNode(  );
        unset( $list[1] );
        $this->assertFalse( isset( $list[1] ) );
    }

    public function testOffsetSetWithNullOffsetWorks()
    {
        $list = new ymcPipeNodeList;
        $i=4;
        while( --$i )
        {
            $list[] = $this->getNode();
        }
        $this->assertTrue( isset( $list[2] ) );
    }

    public function testIterationWorks()
    {
        $list = new ymcPipeNodeList;
        $i=4;
        while( --$i )
        {
            $list[] = $this->getNode();
        }
        foreach( $list as $node )
        {
            ++$i;
            $this->assertTrue( $node instanceof ymcPipeNode );
        }
        $this->assertEquals( 3, $i );
    }

    public function testCountOnEmptyList()
    {
        $list = new ymcPipeNodeList;
        $this->assertEquals( 0, count( $list ) );
    }

    public function testCountOnFilledList()
    {
        $list = new ymcPipeNodeList;
        $i=4;
        while( --$i )
        {
            $list[] = $this->getNode();
        }
        $this->assertEquals( 3, count( $list ) );
    }

    public function testGetById()
    {
        $list = new ymcPipeNodeList;
        $i=4;
        while( --$i )
        {
            $node =  $this->getNode();
            $node->id = $i;
            $list[] = $node;
            $nodes[] = $node;
        }
        $this->assertNodeEquals( $nodes[2], $list->getById( $nodes[2]->id ) );
    }

    public function testIsNodeInListFalse()
    {
        $list = new ymcPipeNodeList;
        $i=4;
        while( --$i )
        {
            $node =  $this->getNode();
            $node->id = $i;
            $list[] = $node;
            $nodes[] = $node;
        }
        $this->assertFalse( $list->contains( $this->getNode(  ) ));
    }

    public function testIsNodeInListTrue()
    {
        $list = new ymcPipeNodeList;
        $i=4;
        while( --$i )
        {
            $node =  $this->getNode();
            $node->id = $i;
            $list[] = $node;
            $nodes[] = $node;
        }
        $this->assertTrue( ( bool )$list->contains( $nodes[2] ));
    }

    public function testRemoveTrue()
    {
        $list = new ymcPipeNodeList;
        $node = $this->getNode();
        $list[] = $node;
        $list->remove( $node );
        $this->assertFalse( $list->contains( $node ) );
    }

    public function testRemoveNotContainedNodeException()
    {
        $this->setExpectedException( 'ymcPipeNodeListException' );
        $list = new ymcPipeNodeList;
        $node = $this->getNode();
        $list->remove( $node );
    }

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite(__CLASS__);
    }
}
