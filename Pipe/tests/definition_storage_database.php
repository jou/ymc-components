<?php

require_once "case.php";

class ymcPipeDefinitionStorageDatabaseTest extends ymcPipeTestCase
{
    protected function getEmptyDb()
    {
        $schema = ezcDbSchema::createFromFile( 'xml', TESTPATH.'../src/schema.xml' );
//        $db = ezcDbFactory::create("sqlite://:memory:");
        $db = ezcDbFactory::create("sqlite:///home/ymc-toko/sqlite");
        $schema->writeToDb( $db );

        return $db;
    }

    public function testSaveLoadComplexPipe(  )
    {
        $db = $this->getEmptyDb();
        $pipe = $this->getComplexPipe();
        $defStorage = new ymcPipeDefinitionStorageDatabase( $db );
        $defStorage->save( $pipe );

        $newPipe = $defStorage->loadByName( $pipe->name, $pipe->version );
        $this->assertPipeEquals( $pipe, $newPipe );
    }

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite(__CLASS__);
    }
}
