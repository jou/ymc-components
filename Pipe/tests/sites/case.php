<?php

require_once dirname( __FILE__ ).'/../case.php';

abstract class ymcPipeSitesTestCase extends ymcPipeTestCase
{
    protected $input = array();
    protected $pipe;
    protected $inNode;

    public function setUp()
    {
        ymcPipeInputNode::setFetchCallback( array( $this, 'getInput' ) );
        $this->pipe = new ymcPipe;
        $this->inNode = new ymcPipeInputNode( $this->pipe );
        $this->inNode->config->inputName = 'html';
    }

    public function getInput( $name )
    {
        return $this->input[$name];
    }

    public static function getHtmlAndData( Array $filenames )
    {
        $result = array();
        foreach( $filenames as $filename )
        {
            $data = require dirname( $filename ).'/'.substr( basename( $filename ), 0, -5 ).'.php' ;
            $result[] = array(
              $filename,
              $data
            );
        }
        return $result;
    }
}
