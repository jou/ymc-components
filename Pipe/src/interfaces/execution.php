<?php

abstract class ymcPipeExecution
{
    /**
     * Possible states of an execution. 
     */
    const NOT_STARTED = 1;
    const SUSPENDED   = 2;
    const RUNNING     = 4;
    const CANCELLED   = 8;
    const FAILED      = 16;
    const FINISHED    = 32;

    /**
     * The pipe for this execution.
     *
     * Getting and Setting of this property must be handled by the extending class.
     * 
     * @var ymcPipe
     */
    protected $pipe;

    /**
     * Nodes of the pipe being executed that are activated.
     *
     * May contain either the node object itself or the id of the node.
     * A method accessing this property needs to lookup the node itself if the id is present.
     *
     * @var ymcPipeNode[]
     */
    protected $activatedNodes = array();

    /**
     * Indicates the current state of the execution. Can be one of the above constants.
     * 
     * @var integer
     */
    protected $executionState; 

    public $variables = array();

    /**
     * Stores the serialized exception received from $node->execute() in case of a failure.
     * 
     * @var Exception
     */
    protected $exception;

    /**
     * The interface nodes can use to call methods.
     *
     * Must be set before starting the execution.
     * 
     * @var ymcPipeExecutionApi
     */
    protected $api;

    public function __construct()
    {
        $this->executionState = self::NOT_STARTED;
    }

    /**
     * Activates all nodes which do not have start nodes and are therefor startNodes.
     * 
     * @access protected
     */
    protected function activateStartNodes()
    {
        foreach( $this->pipe->getStartNodes() as $node )
        {
            $node->activate( $this );
        }
    }

    /**
     * Add a note to the list of nodes to be executed.
     *
     * Should be called only from ymcPipeNode::activate().
     * 
     * @param ymcPipeNode $node The node calling this method.
     * @return void
     */
    public function addActivatedNode( ymcPipeNode $node )
    {
        $nodeId = $node->id;
        if( !is_integer( $nodeId ) )
        {
            throw new Exception( 'Node must have an id to be activated!' );
        }

        $this->activatedNodes[$nodeId] = $node;
    }

    /**
     * Starts the execution of the pipe and returns the execution id.
     *
     * $parentId is used to specify the execution id of the parent pipe
     * when executing subpipes. It should not be used when manually
     * starting pipes.
     *
     * @param int $parentId
     * @return mixed Execution ID if the pipe has been suspended,
     *               null otherwise.
     * @throws ezcPipeExecutionException
     *         If no pipe has been set up for execution.
     */
    public function start( $parentId = 0 )
    {
        if( self::NOT_STARTED !== $this->executionState )
        {
            throw new Exception( 'This execution has already been started! Resume?' );
        }

        if ( !$this->pipe instanceof ymcPipe )
        {
            throw new ymcPipeExecutionException(
              'No pipe or wrong type has been set up for execution.'
            );
        }

        // Start pipe execution by activating the start nodes.
        $this->activateStartNodes();

        // Continue pipe execution until there are no more
        // activated nodes.
        $this->execute();
    }

    /**
     * The pipe's main execution loop. It is started by start() and resume().
     *
     * @ignore
     */
    protected function execute()
    {
        $this->executionState = self::RUNNING;

        // Try to execute nodes while there are executable nodes on the stack.
        do
        {
            // Flag that indicates whether at least one node has finished it's execution during
            // the current iteration of the loop.
            $executed = false;

            $nodes = $this->__get( 'pipe' )->nodes;

            // Iterate the stack of activated nodes.
            foreach ( $this->activatedNodes as $nodeId => $nodeRef )
            {
                if( $nodeRef instanceof ymcPipeNode )
                {
                    $node = $nodeRef;
                }
                else
                {
                    $node = $nodes->getById( $nodeId );
                }
                // Execute the current node and check whether it finished
                // executing.
                try
                {
                    if ( $node->execute( $this ) )
                    {
                        // Remove current node from the stack of activated
                        // nodes.
                        unset( $this->activatedNodes[$nodeId] );

                        // Toggle flag (see above).
                        $executed = true;
                    }
                }
                catch( Exception $e )
                {
                    $this->executionState = self::FAILED;
                    $this->exception      = $e->getMessage();
                    $node->setActivationState( ymcPipeNode::EXECUTION_FAILED );
                    throw $e;
                }
            }
        }
        while ( !empty( $this->activatedNodes ) && $executed );

        // Maybe the state has already been set by a node.
        if ( self::RUNNING === $this->executionState )
        {
            if ( empty( $this->activatedNodes ) )
            {
                $this->executionState = self::FINISHED;
            }
            else
            {
                // The stack of activated nodes is not empty but at the moment none of
                // its nodes can be executed.
                $this->executionState = self::SUSPENDED;
            }
        }
    }

    public function __get( $name )
    {
        switch( $name )
        {
            case 'executionState':
            case 'exception':
            case 'pipe':
                return $this->$name;
            case 'api':
                if( !$this->api instanceof ymcPipeExecutionApi )
                {
                    throw new ymcPipeExecutionException( 'No api has been set!' );
                }
                $this->api->setExecution( $this );
                return $this->api;

            default:
                throw new ezcBasePropertyNotFoundException( $name );
        }
    }

    public function __set( $name, $value )
    {
        switch( $name )
        {
            case 'api':
                if( !$value instanceof ymcPipeExecutionApi )
                {
                    throw new ezcBaseValueException( 'api', $value, 'instance of ymcPipeExecutionApi' );
                }
            break;
            default:
                throw new ezcBasePropertyNotFoundException( $name );

        }
        $this->$name = $value;
    }

    public function isRunnable()
    {
        if( in_array( $this->executionState,
                      array( self::SUSPENDED, self::NOT_STARTED ) ) )
        {
            return TRUE;
        }
        return FALSE;
    }
}
