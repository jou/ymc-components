<?php

/**
 * XML file based definition Storage for Pipes.
 *
 * @todo: implement versioning of files.
 * 
 */
class ymcPipeDefinitionStorageXml implements ymcPipeDefinitionStorage
{
    /**
     * Singleton instances of this class.
     * 
     * @see getInstance()
     * @var array
     */
    protected $instances = array();

    /**
     * The directory that holds the XML files.
     *
     * @var string
     */
    protected $directory;

    /**
     * Constructs a new definition loader that loads definitions from $directory.
     *
     * $directory must contain the trailing '/'
     *
     * @param  string $directory The directory that holds the XML files.
     */
    public function __construct( $directory )
    {
        if( !is_dir( $directory ) )
        {
            throw new ymcPipeDefinitionStorageException( __CLASS__.' needs to be instantiated with a directory. '.$directory.' is not a readable directory.' );
        }
        if( $directory[strlen($directory)-1] != DIRECTORY_SEPARATOR )
        {
          $directory .= DIRECTORY_SEPARATOR;
        }
        $this->directory = $directory;
    }

    /**
     * Loads a pipe definition from a file, identified by name and version.
     *
     * When the $pipeVersion argument is omitted, the most recent version is loaded.
     *
     * @param  string $pipeName
     * @param  int    $pipeVersion
     * @return ymcPipe
     * @throws ymcPipeDefinitionStorageException
     */
    public function loadByName( $pipeName, $pipeVersion = NULL )
    {
        $xmlString = $this->getXmlStringByName( $pipeName, $pipeVersion );
        return $this->loadFromXmlString( $xmlString );
    }

    /**
     * Loads a pipe definition from a file identified by filename.
     * 
     * @param string $filename relative to directory given to the constructor.
     * @throws ymcPipeDefinitionStorageException
     * @return ymcPipe
     */
    public function loadFromFile( $filename )
    {
        $xmlString = $this->loadFile( $filename );
        return $this->loadFromXmlString( $xmlString );
    }

    /**
     * Parses given xml string and returns a pipe definition.
     * 
     * @param string $xmlString 
     * @throws ymcPipeDefinitionStorageException
     * @return ymcPipe
     */
    public function loadFromXmlString( $xmlString )
    {
        // Parse the string into DOM.
        $document = new DOMDocument;

        libxml_use_internal_errors( true );

        $loaded = @$document->loadXML( $xmlString );

        if ( $loaded === false )
        {
            $message = '';

            foreach ( libxml_get_errors() as $error )
            {
                $message .= $error->message;
            }

            throw new ymcPipeDefinitionStorageException(
              sprintf(
                'Could not load pipe from xml string. Errors: %s',
                $message != '' ? "\n" . $message : ''
              )
            );
        }

        $pipe = self::loadFromDocument( $document );
        
        //$pipe->name = $pipeName;
        return $pipe;
    }

    /**
     * Loads the xml file indicated by $pipeName and $pipeVersion and returns the file's content.
     * 
     * @param string $pipeName 
     * @param int    $pipeVersion 
     * @return string
     */
    protected function getXmlStringByName( $pipeName, $pipeVersion )
    {
        if ( !$pipeVersion )
        {
            // Load the latest version of the pipe definition by default.
            //@todo test, implement
            $pipeVersion = $this->getCurrentVersion( $pipeName );
        }
        
        $filename = $this->getFilename( $pipeName, $pipeVersion );
        
        return $this->loadFile( $filename );
    }

    /**
     * Check if a pipe already exists
     * 
     * @param string $filename relative to directory given in the constructor.
     * @return bool
     */
    public function pipeExists( $pipeName, $pipeVersion = NULL )
    {
        $filename = $this->getFilename( $pipeName, $pipeVersion );
        return file_exists( $this->directory . DIRECTORY_SEPARATOR . $filename );
    }
    
    /**
     * Tries to load a file and returns it content.
     * 
     * @throws Exception if file doesn't exists.
     * @param string $filename relative to directory given in the constructor.
     * @return string
     */
    protected function loadFile( $filename )
    {
        $path = $this->directory.DIRECTORY_SEPARATOR.$filename;
        if ( !is_readable( $path) )
        {
            throw new ymcPipeDefinitionStorageException(
              sprintf(
                'Could not read file "%s" from directory "%s".',
                $filename,
                $this->directory
              )
            );
        }
        return file_get_contents( $path );
    }

    /**
     * Returns the filename with full path according to $pipeName and $pipeVersion.
     *
     * @Todo get the most recent version if pipeVersion is not given.
     * 
     * @param string $pipeName 
     * @param mixed  $pipeVersion 
     * @return string
     */
    public function getFilename( $pipeName, $pipeVersion )
    {
        return sprintf( 
           '%s_%04u.xml',
           $pipeName,
           $pipeVersion
        );
    }
    
    /**
     * Returns the most recent version of a pipe
     *
     * @param string $pipeName 
     * @return string
     */
    public function getCurrentVersion( $pipeName )
    {
        // get all versions of this pipe
        $pipes = glob( $this->directory . $pipeName . '*', GLOB_NOSORT );
        
        // sort them natural to avoid getting version 2 instead of version 10
        natsort( $pipes );
        
        // and now the current version
        $pipePath = array_pop($pipes);
        if( preg_match( '/_([0-9]*).xml/', $pipePath, $version ) ) return $version[1];
    }

    /**
     * Saves the given pipe as a XML file.
     * 
     * @throws ymcPipeDefinitionStorageException
     * @param ymcPipe $pipe
     * @param mixed $version
     */
    public function save( ymcPipe $pipe, $version = false )
    {
        $name = $pipe->name;

        if( !$name )
        {
            throw new ymcPipeDefinitionStorageException( 'The pipe has no name and thus can not be saved!' );
        }
        
        if( !$version )
        {
            $version = $pipe->version;
            if( !$version )
            {
              throw new ymcPipeDefinitionStorageException( 'The pipe has no version and thus can not be saved!' );
            }
        }

        $doc = self::saveToDocument( $pipe, $version );
        $filename = $this->getFilename( $name, $version );
        $bytes = $doc->save( $this->directory . $filename );
        if( FALSE === $bytes )
        {
            throw new ymcPipeDefinitionStorageException( 'I/O Error when writing xml file.' );
        }
    }
    
    /**
     * Delete an existing Pipe XML-File
     * 
     * @throws ymcPipeDefinitionStorageException
     * @param ymcPipe $pipe 
     * @param mixed $version
     */
    public function remove( ymcPipe $pipe, $version = false )
    {
        $name = $pipe->name;
        $filename = $this->getFilename( $name, $version );
        $path = $this->directory . DIRECTORY_SEPARATOR . $filename;
        
        if( !unlink( $path ) )
        {
            throw new ymcPipeDefinitionStorageException( 'Can\'t remove xml file.' );
        }
    }

    public static function loadFromDocument( DOMDocument $document )
    {
        $pipe = new ymcPipe( $document->documentElement->getAttribute( 'name' ) );

        // Create node objects.
        $nodes = array();
        $xmlNodes = $document->getElementsByTagName( 'node' );

        // unserialize nodes
        foreach ( $xmlNodes as $xmlNode )
        {
            $id   = (int)$xmlNode->getAttribute( 'id' );
            $node = ymcPipeNode::unserializeFromXml( $xmlNode, $pipe );
            $node->id = ( int )$id;

            $nodes[$id] = $node;
        }

        // Connect node objects.
        foreach ( $xmlNodes as $xmlNode )
        {
            $id   = (int)$xmlNode->getAttribute( 'id' );

            foreach ( $xmlNode->getElementsByTagName( 'outNode' ) as $outNode )
            {
                $nodes[$id]->addOutNode( $nodes[(int)$outNode->getAttribute( 'id' )] );
            }
        }
        //$pipe->definitionStorage = $this;
        $pipe->version = $document->documentElement->getAttribute( 'version' );
        //$pipe->verify();

        return $pipe;
    }

    /**
     * Converts the given pipe to a DOMDocument.
     * 
     * @todo Does it make sense to give version as parameter or should it be taken from
     *       $pipe->version?
     * @param ymcPipe $pipe 
     * @param mixed $version 
     * @return DOMDocument
     */
    public static function saveToDocument( ymcPipe $pipe, $version = false )
    {
        $document = new DOMDocument( '1.0', 'UTF-8' );
        $document->formatOutput = true;
        $root = $document->createElement( 'ymcpipe' );
        $root->setAttribute( 'name', $pipe->name );
        $root->setAttribute( 'version', $version );
        $document->appendChild( $root );

        foreach( $pipe->nodes as $key => $node )
        {
            $xmlNode  = $document->createElement( 'node' );

            // the id is needed to reconnect the nodes on unserialization
            // A node must not have an id of 0, because I wanna use node 0 for the state of the
            // execution in the database execution.
            $nodeId = $key + 1;
            $node->id = $nodeId;
            $xmlNode->setAttribute( 'id', $nodeId );

            $node->serializeToXml( $xmlNode );
            $root->appendChild( $xmlNode );

            // now add the edges ( we save the outNodes of all nodes )
            foreach( $node->outNodes as $outNode )
            {
                $xmlOutNode = $document->createElement( 'outNode' );
                $xmlOutNode->setAttribute( 'id', $pipe->nodes->contains( $outNode ) + 1 );
                $xmlNode->appendChild( $xmlOutNode );
            }
        }
        return $document;
    }

    public function __get( $name )
    {
        switch( $name )
        {
            case 'directory':
                return $this->$name;
            default:
                throw new ezcBasePropertyNotFoundException( $name ) ;
        }
    }

    /**
     * Returns an instance of self for the given path.
     *
     * The lazy_initialization mechanism of eZ Components can be used to prepend the given path.
     * 
     * @param string $path optional, 
     * @return ymcPipeDefinitionStorageXml
     */
    public static function getInstance( $path = '' )
    {
        // ':' may not be a path, so we can use it to store the default instance
        $key = $path ? $path : ':';

        if ( !array_key_exists( $key, self::$instances ) )
        {
            $instance = ezcBaseInit::fetchConfig( __CLASS__, $path );
            self::$instances[$key] = $instance ? $instance : new self( $path );
        }
        return self::$instances[$key];

    }
}
