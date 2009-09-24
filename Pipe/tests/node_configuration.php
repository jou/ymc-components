<?php

require_once "case.php";
require_once 'mock/node_with_bad_configuration_object.php';
require_once 'mock/node_configuration_basic.php';

class ymcPipeNodeConfigurationTest extends ymcPipeTestCase
{
    public function testNodeMustProvideConfigurationObject()
    {
        $this->setExpectedException( 'ymcPipeNodeException' );
        new ymcPipeNodeWithBadConfigurationObject( $this->getPipe() );
    }

    public function testXmlSerializationRoundtrip()
    {
        $config = new ymcPipeBasicNodeConfigurationMock;
        $config->setPropertiesTestHelper( array( 
            'eins' => 'zwei',
            3      => 4,
            'fuenf' => 6,
            7     => 'acht',
            'neun' => false,
            'zehn' => true,
            'float' => 6.78,
            'sub' => array( 
                'hi' => 'du',
                'subi' => array(  )
            )
        ) );

        $document = new DOMDocument();
        $document->formatOutput = true;

        $elem = $document->createElement( 'config' );
        $config->serializeToXml( $elem );
        $document->appendChild( $elem );

        $newConfig = ymcPipeNodeConfiguration::unserializeFromXml( $elem );
        $this->assertEquals( $config, $newConfig );
    }

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite(__CLASS__);
    }
}
