<?php

require_once 'case.php';
require_once 'mock/definition_storage.php';
require_once 'mock/node_basic.php';
require_once 'mock/node_for_execution.php';

class ymcPipeExecutionDatabaseTest extends ymcPipeTestCase
{
    protected function getEmptyDb()
    {
        $schema = ezcDbSchema::createFromFile( 'xml', TESTPATH.'../src/schema.xml' );
        $db = ezcDbFactory::create("sqlite://:memory:");
//        $db = ezcDbFactory::create("sqlite:///home/ymc-toko/sqlite");
        $schema->writeToDb( $db );

        return $db;
    }

    public function testInstallSchema()
    {
        $db = $this->getEmptyDb();
        $this->assertThat( $db, $this->isInstanceOf( 'ezcDbHandler' ) );
    }

    public function testActivatedNodesArePersistedAndRecreated()
    {
        $db = $this->getEmptyDb();
        $execution = new ymcPipeExecutionDatabase($db);
        $execution->setPipe( 'testPipe', 11 );
        $defStorage = new ymcPipeDefinitionStorageMock;
        $execution->definitionStorage = $defStorage;
        $pipe = new ymcPipe;
        $node = $pipe->createNode( 'ymcPipeNodeForExecutionMock' );
        $node->id = 1;
        $node->todo = false;
        $defStorage->pipe = $pipe;

        $execution->start();
        $execution->store();

        $newExecution = new ymcPipeExecutionDatabase( $db, $execution->id );

        // make sure the pipe is instantiated
        $newExecution->definitionStorage = $defStorage;
        $node->hasBeenExecuted = false;
        $newExecution->resume();

        $this->assertEquals( 1, count( $this->readAttribute( $newExecution, 'activatedNodes' ) ) );
        $this->assertTrue( $node->hasBeenExecuted );
    }

    public function testGetSetPipeName()
    {
        $execution = new ymcPipeExecutionDatabase( $this->getEmptyDb() );
        $execution->setPipe( 'test', 3 );
        $this->assertEquals( 'test', $execution->pipeName );
        $this->assertEquals( 3, $execution->pipeVersion );
    }

    public function testSetDefinitionStorage()
    {
        $execution = new ymcPipeExecutionDatabase( $this->getEmptyDb() );

        $defStorage = new ymcPipeDefinitionStorageMock;
        $execution->definitionStorage = $defStorage;

        $this->assertSame( $defStorage, $execution->definitionStorage );
    }

    public function testSuspendEmptyNotStartedExecution()
    {
        $db = $this->getEmptyDb();
        $created = new DateTime( 'now' );
        $execution = new ymcPipeExecutionDatabase( $db );
        $execution->setPipe( 'testPipe', 11 );
        $execution->created = $created;
        $execution->store();

        $result = $db->query( 'SELECT * from pipe_execution' )->fetchAll( PDO::FETCH_ASSOC );
        $result = array_pop( $result );

        $this->assertEquals( $created->format( 'Y-m-d G:i:s' ), $result['created'] );
        $this->assertEquals( 'testPipe', $result['pipe_name'] );
        $this->assertEquals( '11', $result['pipe_version'] );
        $this->assertEquals( $execution->id, ( int )$result['id'] );
        $this->assertEquals( '0', $result['parent'] );
    }

    public function testSuspendSetsExecutionId(  )
    {
        $db = $this->getEmptyDb();
        $created = new DateTime( 'now' );
        $execution = new ymcPipeExecutionDatabase( $db );
        $execution->setPipe( 'testPipe', 11 );
        $execution->created = $created;
        $execution->store();

        $this->assertType( 'integer', $execution->id );
    }

    public function testSuspendAndResumeNotStartedExecution()
    {
        $db = $this->getEmptyDb();
        $created = new DateTime( 'now' );
        $execution = new ymcPipeExecutionDatabase( $db );
        $execution->setPipe( 'testPipe', 11 );
        $execution->created = $created;
        $execution->store();

        $newExecution = new ymcPipeExecutionDatabase( $db, $execution->id );

        $this->assertEquals( $created->format( 'U' ), $newExecution->created->format( 'U' ) );
        $this->assertEquals( 'testPipe', $newExecution->pipeName );
        $this->assertEquals( 11, $newExecution->pipeVersion );
        $this->assertEquals( $execution->id, $newExecution->id );
    }

    public function testSuspendAndResumeNotStartedExecutionWithExecutionVariables()
    {
        $db = $this->getEmptyDb();
        $execution = new ymcPipeExecutionDatabase( $db );
        $execution->setPipe( 'testPipe', 11 );
        $execution->variables['test1'] = 'blub1';
        $execution->variables['test2'] = 12;
        $execution->variables['test3'] = array( 1 );
        $execution->store();

        $newExecution = new ymcPipeExecutionDatabase( $db, $execution->id );

        $this->assertEquals( 'blub1',    $newExecution->variables['test1'] );
        $this->assertEquals( 12,         $newExecution->variables['test2'] );
        $this->assertEquals( array( 1 ), $newExecution->variables['test3'] );
    }

    public function testGetPipe()
    {
        $db = $this->getEmptyDb();
        $execution = new ymcPipeExecutionDatabase( $db );
        $execution->setPipe( 'testPipe', 11 );
        $defStorage = new ymcPipeDefinitionStorageMock;
        $execution->definitionStorage = $defStorage;
        $pipe = new ymcPipe;
        $defStorage->pipe = $pipe;

        $this->assertSame( $pipe, $execution->pipe );
    }

    public function testSetSuspendGetNodeVariable()
    {
        $db = $this->getEmptyDb();
        $execution = new ymcPipeExecutionDatabase( $db );
        $execution->setPipe( 'testPipe', 11 );
        $defStorage = new ymcPipeDefinitionStorageMock;
        $execution->definitionStorage = $defStorage;

        $pipe = new ymcPipe;
        $node = $pipe->createNode( 'ymcPipeBasicNodeMock', 'basicNode' );

        $node->id = 1;
        $node->variables['test1'] = 'bla';
        $node->variables['test2'] = 12;

        $defStorage->pipe = $pipe;

        // execution needs to "load" the pipe from definition storage
        $execution->pipe;
        $execution->store();


        $newExecution = new ymcPipeExecutionDatabase( $db, $execution->id );

        $newExecution->definitionStorage = $defStorage;
        $defStorage->pipe = new ymcPipe;
        $defStorage->pipe->createNode( 'ymcPipeBasicNodeMock', 'basicNode' )->id = 1;

        $newNode = $newExecution->pipe->nodes->getById( 1 );

        $this->assertEquals( 'bla', $newNode->variables['test1'] );
        $this->assertEquals( 12,    $newNode->variables['test2'] );
    }

    public function testBasicPipeExecutesAndFishes()
    {
        $db = $this->getEmptyDb();
        $execution = new ymcPipeExecutionDatabase( $db );
        $execution->setPipe( 'testPipe', 11 );
        $defStorage = new ymcPipeDefinitionStorageMock;
        $defStorage->pipe = new ymcPipe;
        $execution->definitionStorage = $defStorage;

        // execution needs to "load" the pipe from definition storage
        $execution->start();
        $this->assertEquals( ymcPipeExecution::FINISHED, $execution->executionState );

        $execution->store();

        $newExecution = new ymcPipeExecutionDatabase( $db, $execution->id );

        $newExecution->definitionStorage = $defStorage;
        $this->assertEquals( ymcPipeExecution::FINISHED, $newExecution->executionState );
    }

    public function testCanNotResumeExecutionBeforeStart(  )
    {
        $db = $this->getEmptyDb();
        $execution = new ymcPipeExecutionDatabase( $db );
        $execution->setPipe( 'testPipe', 11 );
        $defStorage = new ymcPipeDefinitionStorageMock;
        $defStorage->pipe = new ymcPipe;
        $execution->definitionStorage = $defStorage;

        try
        {
            $execution->resume();
        }
        catch( Exception $e )
        {
            return;
        }
        $this->fail('Expected an exception.');
    }

    public function testResumeSuspendedWithoutStoring()
    {
        $db = $this->getEmptyDb();
        $execution = new ymcPipeExecutionDatabase( $db );
        $execution->setPipe( 'testPipe', 11 );
        $defStorage = new ymcPipeDefinitionStorageMock;
        $pipe = new ymcPipe;
        $defStorage->pipe = $pipe;
        $node = $pipe->createNode( 'ymcPipeNodeForExecutionMock' );
        $execution->definitionStorage = $defStorage;

        $node->todo = false;
        $pipe->accept( new ymcPipeSetIdVisitor );
        $execution->start();
        $this->assertEquals( ymcPipeExecution::SUSPENDED, $execution->executionState );

        $node->todo = true;
        $execution->resume();
        $this->assertEquals( ymcPipeExecution::FINISHED, $execution->executionState );
    }

    public function testResumingFinishedExecutionThrowsException()
    {
        $db = $this->getEmptyDb();
        $execution = new ymcPipeExecutionDatabase( $db );
        $execution->setPipe( 'testPipe', 11 );
        $defStorage = new ymcPipeDefinitionStorageMock;
        $pipe = new ymcPipe;
        $defStorage->pipe = $pipe;
        $node = $pipe->createNode( 'ymcPipeNodeForExecutionMock' );
        $execution->definitionStorage = $defStorage;

        $node->todo = true;
        $pipe->accept( new ymcPipeSetIdVisitor );
        $execution->start();
        $this->assertEquals( ymcPipeExecution::FINISHED, $execution->executionState );

        try
        {
            $execution->resume();
        }
        catch( Exception $e )
        {
            return;
        }
        $this->fail( 'Expected exception' );
    }

    public function testStartSuspendStoreResume()
    {
        $db = $this->getEmptyDb();
        $execution = new ymcPipeExecutionDatabase( $db );
        $execution->setPipe( 'testPipe', 11 );
        $defStorage = new ymcPipeDefinitionStorageMock;
        $pipe = new ymcPipe;
        $defStorage->pipe = $pipe;
        $node = $pipe->createNode( 'ymcPipeNodeForExecutionMock' );
        $execution->definitionStorage = $defStorage;

        $node->todo = false;
        $node->id = 1;
        $execution->start();
        $execution->store();

        $newExecution = new ymcPipeExecutionDatabase( $db, $execution->id );
        $newExecution->definitionStorage = $defStorage;
        $node->todo = true;

        $this->assertEquals( ymcPipeExecution::SUSPENDED, $newExecution->executionState );
        $newExecution->resume();
        $this->assertEquals( ymcPipeExecution::FINISHED, $newExecution->executionState );
    }

    public function testUpdateExecution()
    {
        $db = $this->getEmptyDb();
        $execution = new ymcPipeExecutionDatabase( $db );
        $execution->setPipe( 'testPipe', 11 );
        $defStorage = new ymcPipeDefinitionStorageMock;
        $pipe = new ymcPipe;
        $defStorage->pipe = $pipe;
        $node = $pipe->createNode( 'ymcPipeNodeForExecutionMock' );
        $execution->definitionStorage = $defStorage;

        $node->todo = false;
        $node->id = 1;
        $execution->start();
        $execution->store();

        $newExecution = new ymcPipeExecutionDatabase( $db, $execution->id );
        $newExecution->definitionStorage = $defStorage;
        $newExecution->pipe;
        $node->todo = true;
        $node->variables['test'] = 'value';
        $newExecution->store();

        $newnewExecution = new ymcPipeExecutionDatabase( $db, $execution->id );
        $pipe = new ymcPipe;
        $defStorage->pipe = $pipe;
        $node = $pipe->createNode( 'ymcPipeNodeForExecutionMock' );
        $node->id = 1;
        $newnewExecution->definitionStorage = $defStorage;
        $newnewExecution->pipe;
        $newnewExecution->store();

        $this->assertEquals( 'value', $node->variables['test'] );
    }

    public function testDeleteExecution()
    {
        $db = $this->getEmptyDb();
        $execution = new ymcPipeExecutionDatabase( $db );
        $execution->setPipe( 'testPipe', 11 );
        $execution->store();

        // Before Deletion
        $result = $db->query( 'SELECT count( * ) from pipe_execution' )->fetchAll( PDO::FETCH_NUM );
        $result = array_pop( $result );
        $result = array_pop( $result );
        $this->assertEquals( 1, ( int )$result );

        $result = $db->query( 'SELECT count( * ) from pipe_execution_state' )->fetchAll( PDO::FETCH_NUM );
        $result = array_pop( $result );
        $result = array_pop( $result );
        $this->assertEquals( 1, ( int )$result );

        // DELETE!
        ymcPipeExecutionDatabase::deleteById( $db, $execution->id );

        $result = $db->query( 'SELECT count( * ) from pipe_execution' )->fetchAll( PDO::FETCH_NUM );
        $result = array_pop( $result );
        $result = array_pop( $result );
        $this->assertEquals( 0, ( int )$result );

        $result = $db->query( 'SELECT count( * ) from pipe_execution_state' )->fetchAll( PDO::FETCH_NUM );
        $result = array_pop( $result );
        $result = array_pop( $result );
        $this->assertEquals( 0, ( int )$result );
    }

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite(__CLASS__);
    }
}
