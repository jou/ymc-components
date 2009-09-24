<?php

abstract class ymcPipeNode implements ymcPipeVisitable
{
    /**
     * The node is waiting to be activated.
     */
    const WAITING_FOR_ACTIVATION = 0;

    /**
     * The node is activated and waiting to be executed.
     */
    const WAITING_FOR_EXECUTION = 1;

    /**
     * The node has succesfully been executed and output can be fetched.
     */
    const OUTPUT_READY = 2;

    /**
     * The node has failed execution.
     */
    const EXECUTION_FAILED = 3;

    /**
     * The node can not be executed due to a failure in an innode.
     */
    const EXECUTION_FAILED_IN_INNODE = 4;

    /**
     * Typename used to describe the node type in user interfaces.
     * 
     * Should be overridden by implementing classes.
     * 
     * @var string
     */
    protected $typename = 'undefined';

    /**
     * The configuration of this node.
     *
     * @var ymcPipeNodeConfig
     */
    protected $config;

    /**
     * The state of this node.
     *
     * @var integer
     */
    protected $activationState = self::WAITING_FOR_ACTIVATION;

    /**
     * The incoming nodes of this node.
     *
     * @var array( int => ymcPipeNode )
     */
    protected $inNodes;

    /**
     * The outgoing nodes of this node.
     *
     * @var array( int => ymcPipeNode )
     */
    protected $outNodes;

    /**
     * Contains the output of this node after execution.
     * 
     * @see getOutput()
     * @var mixed
     * @access protected
     */
    protected $output;

    /**
     * Flag that indicates whether an add*Node() or remove*Node()
     * call is internal. This is necessary to avoid unlimited loops. 
     *
     * @var boolean
     */
    protected static $internalCall = false;

    /**
     * Constraint: The minimum number of incoming nodes this node has to have
     * to be valid. Set to false to disable this constraint.
     *
     * @var integer
     */
    protected $minInNodes = 1;

    /**
     * Constraint: The maximum number of incoming nodes this node has to have
     * to be valid. Set to false to disable this constraint.
     *
     * @var integer
     */
    protected $maxInNodes = 1;

    /**
     * The pipe this node belongs to. A node can not exists without belonging to a pipe.
     * 
     * @var ymcPipe
     */
    protected $pipe;

    /**
     * Name to refer to this node from another node. E.g. You need to name two nodes to append the
     * output of one node to the output of another node.
     * 
     * @var string
     */
    protected $name;

    /**
     * The id of this node.
     *
     * The id is only set, after the pipe this node belongs to, has been persisted (to xml or db).
     * It is not guarantied, that the id is unique over all pipes, but only inside the pipe of the
     * node.
     * 
     * @var int
     */
    protected $id;

    public $variables = array();

    public function __construct( ymcPipe $pipe, $name = '', ymcPipeNodeConfiguration $config = null )
    {
        $this->pipe = $pipe;
        $this->name = $name;
        $configurationClass = $this->getConfigurationClass();

        $pipe->addNode( $this );

        if( NULL !== $configurationClass )
        {
            if( NULL !== $config )
            {
                $this->config = $config;
                if( !$this->config instanceof $configurationClass )
                {
                    throw new ymcPipeNodeException( 
                        sprintf( 
                            'Configuration Object given to constructor of %s must be an instance of %s. Given Class is %s.',
                            get_class( $this),
                            $configurationClass,
                            get_class( $config )
                        )
                    );
                }
            }
            else
            {
                $this->config = new $configurationClass;
                if( !$this->config instanceof ymcPipeNodeConfiguration )
                {
                    throw new ymcPipeNodeException( 'Return Value of '.get_class( $this).'->getConfigurationObject() is not an instanceof ymcPipeNodeConfiguration' );
                }
            }
        }

        $this->inNodes = new ymcPipeNodeList;
        $this->outNodes = new ymcPipeNodeList;
    }

    /**
     * Adds a node to the incoming nodes of this node.
     *
     * Automatically adds $node to the pipe and adds
     * this node as an out node of $node.
     *
     * @param  ymcPipeNode $node The node that is to be added as incoming node.
     * @throws ymcPipeNodeDifferentPipesException if $node is not in the same pipe as $this.
     * @return ymcPipeNode $this
     */
    public function addInNode( ymcPipeNode $node )
    {
        // Check whether the node is already an incoming node of this node.
        if ( $this->inNodes->contains( $node ) === false )
        {
            // Add this node as an outgoing node to the other node.
            if ( !self::$internalCall )
            {
                // Fail, if $node is not in the same Pipe as $this
                if( !$this->isNodeFromSamePipe( $node ) )
                {
                    throw new ymcPipeNodeDifferentPipesException;
                }
                self::$internalCall = true;
                $node->addOutNode( $this );
            }
            else
            {
                self::$internalCall = false;
            }

            // Add the other node as an incoming node to this node.
            $this->inNodes[] = $node;
        }

        return $this;
    }

    /**
     * Adds a node to the outgoing nodes of this node.
     *
     * Automatically adds $node to the workflow and adds
     * this node as an in node of $node.
     *
     * @param  ymcPipeNode $node The node that is to be added as outgoing node.
     * @throws ymcPipeNodeDifferentPipesException if $node is not in the same pipe as $this.
     * @return ymcPipeNode $this
     */
    public function addOutNode( ymcPipeNode $node )
    {
        // Check whether the other node is already an outgoing node of this node.
        if ( $this->outNodes->contains( $node ) === false )
        {
            // Add this node as an incoming node to the other node.
            if ( !self::$internalCall )
            {
                // Fail, if $node is not in the same Pipe as $this
                if( !$this->isNodeFromSamePipe( $node ) )
                {
                    throw new ymcPipeNodeDifferentPipesException;
                }
                self::$internalCall = true;
                $node->addInNode( $this );
            }
            else
            {
                self::$internalCall = false;
            }

            // Add the other node as an outgoing node to this node.
            $this->outNodes[] = $node;
        }

        return $this;
    }

    /**
     * Checks whether $node is in the same pipe as $this node.
     * 
     * @param ymcPipeNode $node 
     * @return bool
     */
    public function isNodeFromSamePipe( ymcPipeNode $node )
    {
        return $this->pipe === $node->pipe;
    }

    /**
     * Returns the output of this node.
     * 
     * @access public
     * @return mixed
     */
    public function getOutput()
    {
        if( self::OUTPUT_READY === $this->activationState )
        {
            return $this->output;
        }
        return false;
    }

    protected function setOutput( $output )
    {
        $this->activationState = self::OUTPUT_READY;
        $this->output = $output;
    }

    /**
     * Returns the type of output produced by this node.
     *
     * This information is needed to check the graph creation and for error checking
     * during execution.
     * The returned string is either the name of a basic PHP type or the name of an
     * interface or a class.
     *
     * @access public
     * @return string
     */
    public function getOutputType()
    {
        return 'string';
    }

    /**
     * Returns the type of input required by this node.
     * 
     * @see getOutputType()
     * @access public
     * @return string
     */
    public function getInputType()
    {
        return 'string';
    }

    /**
     * Tries to get the outputs of all inNodes and returns the outputs as an array.
     *
     * Returns false if the outputs of the inNodes are not yet ready. 
     * Sets the activation state to self::EXECUTION_FAILED_IN_INNODE in case one of the inNodes
     * did fail.
     * 
     * @return array / false
     */
    protected function fetchInput()
    {
        $input = array();
        foreach( $this->inNodes as $inNode )
        {
            if( false === ( $output = $inNode->getOutput() ) )
            {
                // output is not (yet) ready
                // Check whether it simply waits or whether it has failed.
                if( $inNode->hasFailedExecution() )
                {
                    $this->setActivationState( self::EXECUTION_FAILED_IN_INNODE );
                }
                else
                {
                    //@todo Is this good? We wait again to be activated by the parent node.
                    $this->setActivationState( self::WAITING_FOR_EXECUTION );
                }
                return false;
            }

            // @todo make a function for typechecking, not inside node!

            $inNodeName = $inNode->name;
            if( $inNodeName )
            {
                $input[$inNodeName] = $output;
            }
            else
            {
                $input[] = $output;
            }
        }

        return $input;
    }

    /**
     * Runs the logic of the node. To be called from the executor.
     * 
     * @param ymcPipeExecution $execution 
     * @return bool Whether execution is completed.
     */
    public function execute( ymcPipeExecution $execution )
    {
        if( false === ( $input = $this->fetchInput() ) )
        {
            return false;
        }

        // ease the work for $this->processInput
        if( is_integer( $this->maxInNodes ) && $this->maxInNodes <= 1 )
        {
            $input = array_pop( $input );
        }

        if( false === ( $output = $this->processInput( $execution, $input ) ) )
        {
            return false;
        }

        $this->setOutput( $output );
        $this->activateOutNodes( $execution );

        return true;
    }

    /**
     * Activates all outNodes of $this node.
     *
     * Called only from $this->execute()
     * 
     * @param ymcPipeExecution $execution 
     */
    protected function activateOutNodes( ymcPipeExecution $execution )
    {
        foreach( $this->outNodes as $outNode )
        {
            $outNode->activate( $execution );
        }
    }

    /**
     * Produces output from input. Most nodes need to overwrite only this method.
     * 
     * @param ymcPipeExecution $execution 
     * @param mixed            $input     Array of $inNode outputs or directly the $inNode
                                          output if $this->maxInNodes === 1
     * @access public
     * @return mixed the output of type $this->getOutputType
     */
    public function processInput( ymcPipeExecution $execution, $input )
    {
        return $input;
    }

    /**
     * Sets the activation state for this node.
     *
     * @param int $activationState
     * @ignore
     */
    public function setActivationState( $activationState )
    {
        $activationState = ( int ) $activationState;

        // Not so nice to check against numbers instead of the constants, but it's faster
        // and shorter and we're still in the same class.
        if ( $activationState < 0 || $activationState > 4 )
        {
            //@todo
            throw new ymcPipeNodeException( 'Unknown aktivation state.' );
        }

        $this->activationState = $activationState;
    }

    public function hasFailedExecution()
    {
        return self::EXECUTION_FAILED === $this->activationState
            || self::EXECUTION_FAILED_IN_INNODE === $this->activationState;
    }

    protected abstract function getConfigurationClass();

    public function __isset( $name )
    {
        switch( $name )
        {
            case 'config':
            case 'id':
            case 'pipe':
            case 'name':
            case 'inNodes':
            case 'outNodes':
            case 'numInNodes':
            case 'numOutNodes':
                return isset( $this->$name );
            default:
                throw new ezcBasePropertyNotFoundException( $name ) ;
        }
    }

    public function __get( $name )
    {
        switch( $name )
        {
            case 'config':
            case 'id':
            case 'pipe':
            case 'name':
            case 'inNodes':
            case 'outNodes':
            case 'typename':
            case 'activationState':
                return $this->$name;
            case 'numInNodes':
                return $this->inNodes->count();
            case 'numOutNodes':
                return $this->outNodes->count();
            default:
                throw new ezcBasePropertyNotFoundException( $name ) ;
        }
    }

    public function __set( $name, $value )
    {
        switch( $name )
        {
            case 'name':
                $this->$name = $value;
                return;
            case 'id':
                if( !is_integer( $value ) )
                {
                    // @todo better Exception
                    throw new Exception( 'id must be an integer.' );
                }
                $this->id = $value;
                return;
            case 'pipe':
            case 'config':
                throw new ezcBasePropertyPermissionException( $name, ezcBasePropertyPermissionException::READ ) ;
            default:
                throw new ezcBasePropertyNotFoundException( $name ) ;
        }
    }

    /**
     * Serializes this node into the given DOMElement.
     *
     * Does not serialize the graphes edges ( in/out nodes ). This must be done by the pipe's
     * serialization method.
     * 
     * @param DOMElement $element Element to be populated.
     * @return void
     */
    public function serializeToXml( DOMElement $element )
    {
        $element->setAttribute( 'name', $this->name );
        $element->setAttribute( 'node-class', get_class( $this ) );
        if( $this->config instanceof ymcPipeNodeConfiguration )
        {
            $configuration = $element->appendChild( $element->ownerDocument->createElement( 'configuration' ) );
            $this->config->serializeToXml( $configuration );
        }
    }

    /**
     * Unserializes and returns a node from the given DOMElement.
     * 
     * @param DOMElement $element Where to fetch the node from.
     * @param ymcPipe    $pipe    The pipe is forwarded to the node's ctor.
     * @return ymcPipeNode
     */
    public static function unserializeFromXml( DOMElement $element, ymcPipe $pipe )
    {
        $className = $element->getAttribute( 'node-class' );

        if( !class_exists( $className ) )
        {
            throw new ymcPipeNodeException( "Class $className not found." );
        }

        $name = $element->getAttribute( 'name' );
        $configurationNode = $element->getElementsByTagName( 'configuration' );

        if( $configurationNode->length === 0 )
        {
            return new $className( $pipe, $name );
        }
        else
        {
            $configurationNode = $configurationNode->item( 0 );
            $configuration = ymcPipeNodeConfiguration::unserializeFromXml( $configurationNode );
            return new $className( $pipe, $name, $configuration );
        }

    }

    public function activate( ymcPipeExecution $execution )
    {
        $state =& $this->activationState;

        if( !in_array( $state, array( self::WAITING_FOR_ACTIVATION, self::WAITING_FOR_EXECUTION ), TRUE) )
        {
            //@todo
            throw new Exception( 'Node can only be activated when in state WAITING FOR ACTIVATION resp. already in state WAITING FOR EXECUTION.' );
        }

        $state = self::WAITING_FOR_EXECUTION;
        $execution->addActivatedNode( $this );
    }

    /**
     * Overridden implementation of accept() calls accept on the start nodes.
     *
     * @param ymcPipeVisitor $visitor
     */
    public function accept( ymcPipeVisitor $visitor )
    {
        if ( $visitor->visit( $this ) )
        {
            foreach ( $this->outNodes as $outNode )
            {
                $outNode->accept( $visitor );
            }
        }
    }

    public function delete()
    {
        foreach( $this->inNodes as $inNode )
        {
            $inNode->outNodes->remove( $this );
        }

        foreach( $this->outNodes as $outNode )
        {
            $outNode->inNodes->remove( $this );
        }

        unset( $this->config );
        unset( $this->inNodes );
        unset( $this->outNodes );
        unset( $this->id );
    }

    public function serializeState()
    {
        $state = array( 
            'v' => $this->variables,
            'a' => $this->activationState,
            'o' => $this->output
        );
        return serialize( $state );
    }

    public function unserializeState( $serialized )
    {
        try
        {
            $state = unserialize( $serialized );
        }
        catch( Exception $e )
        {
            throw new Exception( sprintf( 'Could not unserialize node id %s. Got %s with Message %s.',
                                     $this->id,
                                     gettype( $e ),
                                     $e->getMessage()
            ) );
        }
        $this->variables       = $state['v'];
        $this->activationState = $state['a'];
        $this->output          = $state['o'];
    }
}
