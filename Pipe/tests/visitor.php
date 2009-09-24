<?php

require_once "case.php";
require_once "mock/visitor.php";

class ymcPipeVisitorTest extends ymcPipeTestCase
{
    public function testAllNodesVisited(  )
    {
        $pipe = $this->getComplexPipe();
        // populate nodes with id's
        ymcPipeDefinitionStorageXml::saveToDocument( $pipe );
        $visitor = new ymcPipeVisitorMock;
        $pipe->accept( $visitor );
        $this->assertNodeListEquals( $pipe->nodes, $visitor->visitedNodes );
    }

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite(__CLASS__);
    }
}
