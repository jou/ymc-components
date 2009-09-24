<?php

require_once "case.php";
require_once "mock/node_for_config_visitor.php";

class ymcPipeVisitorConfigurationEditorTest extends ymcPipeTestCase
{
    public function testVisitOneNodeAndGetEmptyConfig(  )
    {
        $pipe = new ymcPipe;
        $pipe->createNode( 'ymcPipeNodeForConfigTest' );

        $visitor = new ymcPipeConfigurationEditorVisitor;
        $pipe->accept( $visitor );

        // nodes do not have id's yet! This could fail sometime
        $this->assertEquals( array( array(  ) ), $visitor->getConfigurations(    ) );
    }

    public function testVisitOneNodeAndGetConfig(  )
    {
        $pipe = new ymcPipe;
        $node = $pipe->createNode( 'ymcPipeNodeForConfigTest' );
        $node->_initConfiguration( array( 'item' => array( 'type' => ymcPipeNodeConfiguration::TYPE_STRING ) ) );

        $visitor = new ymcPipeConfigurationEditorVisitor;
        $pipe->accept( $visitor );

        // nodes do not have id's yet! This could fail sometime
        $this->assertEquals( array(
                               array( 
                                 'item' => array( 
                                   'type' =>  ymcPipeNodeConfiguration::TYPE_STRING,
                                   'value' => ''
                                 ) 
                               ) 
                             ),
                             $visitor->getConfigurations(    ) );
    }
}
