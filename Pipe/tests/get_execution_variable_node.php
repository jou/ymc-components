<?php

require_once "case.php";
require_once 'mock/definition_storage.php';

class ymcPipeGetExecutionVariableNodeTest extends ymcPipeTestCase
{
    public function testConfigure()
    {
        $node = new ymcPipeGetExecutionVariableNode( new ymcPipe );
        $node->config->variable = 'test';
        $this->assertEquals( 'test', $node->config->variable );
    }

    public function testExecute()
    {
        $pipe = new ymcPipe;

        $node = $pipe->createNode( 'ymcPipeGetExecutionVariableNode' );
        $node->config->variable = 'test';
        $execution = new ymcPipeExecutionNonSuspendable;
        $execution->pipe = $pipe;

        $execution->variables['test'] = 'vvv';
        $pipe->accept( new ymcPipeSetIdVisitor );
        $execution->start();
        $this->assertEquals( 'vvv', $node->getOutput() );
    }

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite(__CLASS__);
    }
}
