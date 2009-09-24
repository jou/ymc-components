<?php

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'node.php';
require_once 'node_graph.php';
require_once 'input_node.php';
require_once 'node_configuration.php';
require_once 'pipe.php';
require_once 'definition_storage_xml.php';
require_once 'execution_abstract.php';
require_once 'node_list.php';
require_once 'visitor.php';
require_once 'node_preg_match.php';
require_once 'execution_non_suspendable.php';
require_once 'execution_database.php';
require_once 'get_execution_variable_node.php';
require_once 'definition_storage_database.php';
require_once 'html_dom_document.php';
require_once 'sites/suite.php';

class ymcPipeTestSuite extends PHPUnit_Framework_TestSuite
{
    public function __construct()
    {
        parent::__construct();
        $this->setName("ymcPipe");

        $this->addTest( ymcPipeNodeTest::suite() );
        $this->addTest( ymcPipeNodeGraphTest::suite() );
        $this->addTest( ymcPipeInputNodeTest::suite() );
        $this->addTest( ymcPipeNodeConfigurationTest::suite() );
        $this->addTest( ymcPipeTest::suite() );
        $this->addTest( ymcPipeDefinitionStorageXmlTest::suite() );
        $this->addTest( ymcPipeExecutionTest::suite() );
        $this->addTest( ymcPipeNodeListTest::suite() );
        $this->addTest( ymcPipeVisitorTest::suite() );
        $this->addTest( ymcPipePregMatchNodeTest::suite() );
        $this->addTest( ymcPipeExecutionNonSuspendableTest::suite() );
        $this->addTest( ymcPipeExecutionDatabaseTest::suite() );
        $this->addTest( ymcPipeGetExecutionVariableNodeTest::suite() );
        $this->addTest( ymcPipeDefinitionStorageDatabaseTest::suite() );
        $this->addTest( ymcPipeHtmlDomDocumentTest::suite() );
        $this->addTestSuite( ymcPipeSitesTestSuite::suite() );
    }

    public static function suite()
    {
        return new self;
    }
}
