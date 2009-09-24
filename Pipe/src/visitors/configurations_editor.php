<?php

class ymcPipeConfigurationEditorVisitor implements ymcPipeVisitor
{
    protected $visitedNodes;

    protected $inConfig;
    protected $outConfig;

    public function __construct( Array $configurations = array() )
    {
        $this->inConfig = $configurations;
        $this->visitedNodes = new ymcPipeNodeList;
    }

    public function visit( ymcPipeVisitable $visitable )
    {
        if( $visitable instanceof ymcPipe )
        {
            return TRUE;
        }

        if( !$this->visitedNodes->addIfNotContained( $visitable ) )
        {
            return FALSE;
        }
        $this->visitConfiguration( $visitable );

        return TRUE;
    }

    protected function visitConfiguration( $node )
    {
        $id     = ( int )$node->id;

        $inConfig = isset( $this->inConfig[$id] )
                    ? $this->inConfig[$id]
                    : array();

        if( isset( $inConfig['nodeName'] ) )
        {
            $node->name = $inConfig['nodeName'];
        }

        $outConfig = array( 'nodeName' => array( 'value' => $node->name ),
                            'nodeType' => array( 'value' => $node->typename ) );

        $config = $node->config;

        // Some nodes may not have a configuration
        if( $config instanceof ymcPipeNodeConfiguration )
        {
            foreach( $config->getDefinition() as $name => $def )
            {
                if( isset( $inConfig[$name] ) )
                {
                    $config->$name = $inConfig[$name];
                }

                $outConfig[$name] = array_merge( $def, array( 'value' => $config->$name ) );
            }
        }

        $this->outConfig[$id] = $outConfig;
    }

    public function getConfigurations()
    {
        return $this->outConfig;
    }
}
