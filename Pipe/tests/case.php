<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Util/Filter.php';

if( !function_exists( '__autoload' ) ) require_once 'ezc/Base/ezc_bootstrap.php';
ezcBase::addClassRepository( dirname( __FILE__ ).'/../src' );

define( 'TESTPATH', dirname( __FILE__ ).DIRECTORY_SEPARATOR );

require_once "mock/node_basic.php";

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

abstract class ymcPipeTestCase extends PHPUnit_Framework_TestCase
{
    private $_tempdir;

    protected $pipes = array();

    protected function getNode( $identifier = 'default' )
    {
        return new ymcPipeBasicNodeMock( $this->getPipe( $identifier ) );
    }

    protected function getPipe( $identifier = 'default' )
    {
        if( !array_key_exists( $identifier, $this->pipes ) )
        {
            $this->pipes[$identifier] = new ymcPipe;
        }
        return $this->pipes[$identifier];
    }

    protected function assertPipeEquals( $pipe1, $pipe2 )
    {
        //$this->assertEquals( $pipe1, $pipe2 );
        $nodes1 = $pipe1->nodes;
        $nodes2 = $pipe2->nodes;

        $this->assertEquals( count( $nodes1 ), count( $nodes2 ), 'Number of nodes in pipes different.' );
        foreach( $pipe1->nodes as $key => $node1 )
        {
            $node2 = $nodes2->getById( $node1->id );
            $this->assertTrue( $node2 instanceof ymcPipeNode, 'Did not find node with id '.$node1->id.' in pipe2.' );
            $this->assertNodeEquals( $node1, $node2 );
            unset( $nodes2[$key] );
        }

        $this->assertEquals( 0, count( $nodes2 ), 'NodeList of pipe2 was longer then NodeList of
        pipe1' );
    }

    protected function assertNodeEquals( ymcPipeNode $node1, ymcPipeNode $node2 )
    {
        $this->assertEquals( $node1->config, $node2->config, 'Configuration of Nodes different.' );
        $this->assertEquals( $node1->id, $node2->id, 'IDs of Nodes different.' );
        $inNodes1 = $node1->inNodes;
        $inNodes2 = $node2->inNodes;
        $outNodes1 = $node1->outNodes;
        $outNodes2 = $node2->outNodes;

        $this->assertEquals( count( $inNodes1 ), count( $inNodes2 ), 'Number of inNodes different.' );
        $this->assertEquals( count( $outNodes1 ), count( $outNodes2 ), 'Number of outNodes different.' );

        $this->assertNodeListEquals( $inNodes1, $inNodes2 );
        $this->assertNodeListEquals( $outNodes1, $outNodes2 );
    }

    protected function assertNodeListEquals( ymcPipeNodeList $list1, ymcPipeNodeList $list2 )
    {
        $count = count( $list1 );
        for( $i = 0; $i < $count; ++$i )
        {
            $found = false;
            $node1 = $list1[$i];
            $id = $node1->id;

            foreach( $list2 as $key => $node2 )
            {
                if( $node2->id === $id )
                {
                    $found = true;
                    unset( $list2[$key] );
                }
            }
            $this->assertTrue( $found, 'Did not found a node with id '.$id.' in list2' );
        }
        $this->assertEquals( 0, count( $list2 ), '$list2 longer then $list1 in '.__FUNCTION__ );
    }

    protected function getComplexPipe()
    {
        $pipe = new ymcPipe;
        $i = 8;
        while( --$i )
        {
            $node[$i] = new ymcPipeBasicNodeMock( $pipe );
            $node[$i]->config->setPropertiesTestHelper( array( 'test' => 'value' ) );
        }

        // $node[0] is not connected!

        $node[1]->addOutNode( $node[3] );
        $node[2]->addOutNode( $node[4] );
        $node[5]->addInNode(  $node[3] );
        $node[5]->addInNode(  $node[4] );
        $node[5]->addOutNode( $node[6] );
        $node[5]->addOutNode( $node[7] );

        return $pipe;
    }

    protected function getPipeWithXNodes( $x )
    {
        $pipe = new ymcPipe;
        $node = null;
        while( --$x )
        {
            if( $node )
            {
                $node->addOutNode( $node = new ymcPipeBasicNodeMock( $pipe ) );
            }
            else
            {
                $node = new ymcPipeBasicNodeMock( $pipe );
            }
        }
        return $pipe;
    }

    protected function getTempDir()
    {
        if( is_dir( $this->_tempdir ) )
        {
            return $this->_tempdir;
        }
        $tmpfile = tempnam(sys_get_temp_dir(), 'ymctest');
        unlink( $tmpfile );
        mkdir( $tmpfile );
        $this->_tempdir = $tmpfile;
        return $tmpfile;
    }

    protected function removeTempDir()
    {
        if( !is_dir( $this->_tempdir ) )
        {
            return; // Nothing to remove
        }
        if( !preg_match( '(.+/ymctest[^/]*$)', $this->_tempdir ) )
        {
            echo( 'Not a testdir: '.$this->_tempdir );
            die(  );
        }
        if ( $dh = opendir( $this->_tempdir ) ) 
        {
            while ( ( $file = readdir( $dh ) ) !== false ) 
            {
                if ( $file !== '.' && $file !== '..' )
                {
                    $this->removeRecursively( $this->_tempdir . "/" . $file );
                }
            }
            unset( $dh );
            rmdir( $this->_tempdir );
        }
    }

    private function removeRecursively( $entry )
    {
        if ( is_file( $entry ) || is_link( $entry ) )
        {
            // Some extra security that you're not erasing your harddisk :-).
            if ( strncmp( $this->_tempdir, $entry, strlen( $this->_tempdir ) ) == 0 )
            {
                return unlink( $entry );
            }
        }

        if ( is_dir( $entry ) )
        {
            if ( $dh = opendir( $entry ) )
            {
                while ( ( $file = readdir( $dh ) ) !== false )
                {
                    if ( $file != "." && $file != '..' )
                    {
                        $this->removeRecursively( $entry . "/" . $file );
                    }
                }

                closedir( $dh );
                rmdir( $entry );
            }
        }
    }
    
    public function tearDown()
    {
        $this->removeTempDir();
    }
}
