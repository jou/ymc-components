<?php

require_once "case.php";
require_once 'mock/node_input_provider.php';
require_once 'mock/node_configuration_basic.php';

class ymcPipePregMatchNodeTest extends ymcPipeTestCase
{
    public function testSetRegularExpression()
    {
        $node = new ymcPipePregMatchNode( $this->getPipe() );
        $node->config->regexp = '/^test$/';
        $this->assertEquals( '/^test$/', $node->config->regexp );
    }

    public function testRegularExpressionMatches()
    {
        $pipe = new ymcPipe;
        $node = new  ymcPipePregMatchNode( $pipe );
        $node->addInNode( new ymcPipeNodeInputProviderMock( $pipe, 'START text END' ) );
        $node->config->regexp = '/[a-z]+/';
        $node->execute( $this->getMock( 'ymcPipeExecution' ) );
        $this->assertEquals( 'text', $node->getOutput() );
    }

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite(__CLASS__);
    }
}
