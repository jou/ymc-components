<?php

class ymcPipeDotVisitor implements ymcPipeVisitor
{
    protected $visitedNodes;

    protected $pipeName;

    protected $nodes = array();

    public $options;

    public function __construct( ymcPipeDotVisitorOptions $options = null )
    {
        $this->visitedNodes = new ymcPipeNodeList;
        $this->options = null !== $options 
                            ? $options
                            : new ymcPipeDotVisitorOptions;
    }

    public function visit( ymcPipeVisitable $visitable )
    {
        if( $visitable instanceof ymcPipe )
        {
            $this->pipeName = $visitable->name;
            return TRUE;
        }

        if( !$this->visitedNodes->addIfNotContained( $visitable ) )
        {
            return FALSE;
        }
        $this->addNode( $visitable );

        return TRUE;
    }

    protected function addNode( ymcPipeNode $node )
    {
        $this->nodes[$node->id] = array( 
                  'label' => sprintf( '{ %s | %s | %s }',
                                      $node->id,
                                      addcslashes( $node->name, ' ' ),
                                      addcslashes( $node->typename, ' ' ) ),
                  'comment' => get_class( $node )
        );
    }

    protected function getEdges()
    {
        $edges = array();

        foreach( $this->visitedNodes as $node )
        {
            $outNodes = array();

            foreach ( $node->outNodes as $outNode )
            {
                $label = ''; // The label for the edge ( outputtype? )
                $outNodes[] = array( 'id'    => $outNode->id,
                                     'label' => $label );
            }

            $edges[$node->id] = $outNodes;
        }

        return $edges;
    }

    public function getDot()
    {
        $dot = <<<EOT
digraph {$this->pipeName} {
dpi="{$this->options->dpi}"
rankdir=LR

EOT;
        foreach ( $this->nodes as $id => $data )
        {
            $dot .= sprintf(
              "node%s [shape=record label=\"%s\" comment=\"%s\"];\n",
              $id,
              $data['label'],
              $data['comment']
            );
        }

        $dot .= "\n";

        foreach ( $this->getEdges() as $fromNode => $toNodes )
        {
            foreach ( $toNodes as $toNode )
            {
                $dot .= sprintf(
                  "node%s -> node%s[label=\"%s\"];\n",

                  $fromNode,
                  $toNode['id'],
                  $toNode['label']
                );
            }
        }
        return $dot . "}\n";
    }

}
