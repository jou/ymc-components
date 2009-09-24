<?php

require_once "case.php";

class ymcPipeInputNodeTest extends ymcPipeTestCase
{
    public $input = array( 'testField' => 'testInput' );
    public $callback;

    public function setUp()
    {
        parent::setUp();
        $this->callback = array( $this, 'provideInputCallback' );
    }

    public function testInputEqualsOutput()
    {
        $node = new ymcPipeInputNode( $this->getMock('ymcPipe') );
        ymcPipeInputNode::setFetchCallback( $this->callback );
        $node->config->inputName = 'testField';
        //@todo fix the type
        $node->config->inputType = 'string';
        $this->assertTrue( $node->execute( $this->getMock( 'ymcPipeExecution' ) ) );
        $this->assertEquals( $this->provideInputCallback( 'testField' ), $node->getOutput() );
    }

    public function testSetGetInputType()
    {
        $node = new ymcPipeInputNode( $this->getMock('ymcPipe') );
        $node->config->inputType = 'string';
        $this->assertEquals( 'string', $node->config->inputType );
    }

    public function provideInputCallback( $name )
    {
        return $this->input[$name];
    }

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite(__CLASS__);
    }
}
