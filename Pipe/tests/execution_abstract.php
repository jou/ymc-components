<?php

require_once "case.php";
require_once 'mock/execution_node_activation.php';
require_once 'mock/node_remember_execution.php';
require_once 'mock/node_for_execution.php';

class ymcPipeExecutionTest extends ymcPipeTestCase
{
    public function testGetSetVariable()
    {
        $execution = new ymcPipeExecutionActivationMock;
        $variable = array( 'TEST' );
        $execution->variables['testvar'] = $variable;
        $this->assertSame( $variable, $execution->variables['testvar'] );
    }

    public function testActivateStartNodesOneNode()
    {
        $node = $this->getNode();
        $execution = new ymcPipeExecutionActivationMock;
        $execution->setPipe( $node->pipe );
        $node->pipe->accept( new ymcPipeSetIdVisitor );
        $activatedNodes = $execution->publicActivateStartNodes();

        foreach( $activatedNodes as $activatedNode )
        {
            $this->assertNodeEquals( $node, $activatedNode );
        }
    }

    public function testActivateStartNodesMultipleNodesOneStartNode()
    {
        $pipe = $this->getPipeWithXNodes( 4 );
        $execution = new ymcPipeExecutionActivationMock;
        $execution->setPipe( $pipe );
        $pipe->accept( new ymcPipeSetIdVisitor );
        $activatedNodes = $execution->publicActivateStartNodes();

        $this->assertEquals( 1, count( $activatedNodes ) );
    }

    public function testActivateStartNodesMultipleStartNodes()
    {
        $pipe = new ymcPipe;
        $i = 4;
        while( --$i )
        {
            new ymcPipeBasicNodeMock( $pipe );
        }
        $execution = new ymcPipeExecutionActivationMock;
        $execution->setPipe( $pipe );
        $pipe->accept( new ymcPipeSetIdVisitor );
        $activatedNodes = $execution->publicActivateStartNodes();

        $this->assertEquals( 3, count( $activatedNodes ) );
    }

    public function testExecutionExecutesAllNodes()
    {
        $pipe = new ymcPipe;
        $nodes = array();
        $i = 8;
        while( --$i )
        {
            $nodes[] = new ymcPipeNodeRememberExecutionMock( $pipe );
        }

        $nodes[1]->addOutNode( $nodes[2] );
        $nodes[1]->addOutNode( $nodes[3] );
        $nodes[2]->addOutNode( $nodes[3] );
        $nodes[2]->addOutNode( $nodes[6] );
        $nodes[3]->addOutNode( $nodes[6] );

        $execution = new ymcPipeExecutionActivationMock;
        $execution->setPipe( $pipe );
        $pipe->accept( new ymcPipeSetIdVisitor );
        $execution->start();

        foreach( $nodes as $key => $node )
        {
            $this->assertTrue( $node->executed, 'Node '.$key.' has not been executed.' );
        }
    }

    public function testExceptionFromNodeIsSaved()
    {
        $pipe = new ymcPipe;
        $node = $pipe->createNode( 'ymcPipeNodeForExecutionMock' );
        $node->todo = null;
        $execution = new ymcPipeExecutionActivationMock;
        $execution->setPipe( $pipe );
        $pipe->accept( new ymcPipeSetIdVisitor );
        try{
            $execution->start();
        }catch( Exception $e ){ }

        $this->assertEquals( ymcPipeExecution::FAILED, $execution->executionState );
        $this->assertType( 'string', $execution->exception );
        $this->assertEquals( ymcPipeNode::EXECUTION_FAILED, $node->activationState );
    }

    public function testExceptionFromNodeIsRethrown()
    {
        $pipe = new ymcPipe;
        $node = $pipe->createNode( 'ymcPipeNodeForExecutionMock' );
        $node->todo = null;
        $execution = new ymcPipeExecutionActivationMock;
        $execution->setPipe( $pipe );
        $pipe->accept( new ymcPipeSetIdVisitor );
        try{
            $execution->start();
        }catch( Exception $e ){
            return;
        }
        $this->fail( 'expected exception from node' );
    }

    public function testUnfinishedExecutionSetsStateToSuspended()
    {
        $pipe = new ymcPipe;
        $node = $pipe->createNode( 'ymcPipeNodeForExecutionMock' );
        $node->todo = false;
        $execution = new ymcPipeExecutionActivationMock;
        $execution->setPipe( $pipe );
        $pipe->accept( new ymcPipeSetIdVisitor );
        $execution->start();

        $this->assertEquals( ymcPipeExecution::SUSPENDED, $execution->executionState );
    }

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite(__CLASS__);
    }
}
