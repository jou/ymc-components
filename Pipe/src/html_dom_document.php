<?php

class ymcPipeHtmlDomDocument extends DOMDocument implements Serializable
{
    /**
     * The original HTML used to instantiate $this. Preserved for serialization.
     * 
     * @var string
     */
    protected $originHTML;

    /**
     * Encoding indicated in HTTP Response
     * 
     * @var string
     */
    protected $encoding;

    /**
     * Array of PHP Errors catched during DOMDocument->loadHTML.
     * 
     * @var array
     */
    public $parseErrors = array();

    /**
     * Serializes $this. Implementation of SPL Serializable interface.
     *
     * Simply save the original HTML. The parseErrors are regenerated on unserialization.
     * 
     * @return string
     */
    public function serialize()
    {
        return serialize( array( 
             'enc'  => $this->encoding,
             'html' => $this->originHTML 
        ) );
    }

    /**
     * Implementation of SPL Serializable interface.
     * 
     * @param string $serialized 
     * @return void
     */
    public function unserialize( $serialized )
    {
        // this is not the original DOMDocument loadHTML method!
        $data = unserialize( $serialized );
        $this->loadHTML( $data['html'], $data['enc'] );
    }

    /**
     * Returns a new instance of self with the $html parsed in a DOM tree.
     * 
     * @param string $html     HTML to parse.
     * @param string $encoding propagated to loadHTML and there to DOMDocument::encoding
     * @return self
     */
    public static function createFromHTML( $html, $encoding = NULL )
    {
        $dom = new self(); // ( version, encoding )
        $dom->formatOutput = true;
        $dom->loadHTML( $html, $encoding );
        return $dom;
    }

    /**
     * Overrided DOMDocument::loadHTML to catch and save PHP warnings.
     * 
     * @TODO replace dirty hack to set encoding, when PHP supports it.
     * @param string $html 
     * @param string $encoding propagated to DOMDocument::encoding
     */
    public function loadHTML( $html, $encoding = NULL )
    {
        $this->checkHtmlToParse( $html );

        $this->originHTML = $html;

        set_error_handler( array( $this, 'htmlParseErrorHandler' ) );
        $errorLevel = error_reporting( E_ALL );

        if( $encoding )
        {
            $this->encoding = $encoding;
            // setting the encoding could give a warning
            if( !empty( $this->parseErrors ) )
            {
                throw new Exception( 'Error while setting encoding '.$encoding.': '.$this->parseErrors[0] );
            }
            $html = "<meta http-equiv=\"content-type\" content=\"text/html; charset=$encoding\">\n$html";
        }

        parent::loadHTML( $html );
        error_reporting( $errorLevel );
        restore_error_handler();
    }

    protected function checkHtmlToParse( $html )
    {
        if( '' === trim( $html ) )
        {
            throw new Exception( 'Got empty HTML to parse.' );
        }
    }

    /**
     * PHP Error handler. Used from $this->loadHTML to catch HTML parsing errors.
     * 
     * @param integer $errNo 
     * @param string  $errStr 
     *
     * @return TRUE - avoids the call of additional error handlers.
     */
    public function htmlParseErrorHandler( $errNo, $errStr )
    {
        $this->parseErrors[] = $errStr;
        return TRUE;
    }

    public function removeNonTextNodes()
    {
        $xPath = new DOMXPath( $this );
        $nodes = $xPath->query( 'descendant::script or descendant::comment()' );
        foreach( $nodes as $node )
        {
            $node->parentNode->removeChild( $node );
        }
    }

    public static function isDescendantOf( DOMNode $parent, DOMNode $child )
    {
        $node = $child;

        while( !$node->isSameNode( $parent ) )
        {
            $node = $node->parentNode;
            if( !$node instanceOf DOMNode )
            {
                return FALSE;
            }
        }

        return TRUE;
    }

    public function getInnerTextRecursive( DOMNode $node,
                                                  Array $ignoreNodes = array( 'script',
                                                       'ul', 'a', 'ol', 'input', 'form' , 'style'
                                                ) )
    {
        $text = '';

        foreach( $node->childNodes as $child )
        {
            switch( $child->nodeType )
            {
                case XML_ELEMENT_NODE:
                    $nodeName = $child->nodeName;

                    if( !in_array( $nodeName, $ignoreNodes ) )
                    {
                        switch( $nodeName )
                        {
                            case 'br':
                                $text .= "\n";
                            break;

                            case 'div':
                            case 'tr':
                            case 'p':
                                $text .= $this->getInnerTextRecursive( $child );
                                $text .= "\n";
                            break;

                            default:
                                $text .= $this->getInnerTextRecursive( $child );
                        }
                    }
                break;

                case XML_TEXT_NODE:
                case XML_CDATA_SECTION_NODE:
                    $text .= trim( $child->textContent ).' ';
                break;

            }
//            echo $child->nodeName.' '.$child->nodeType."\n";
        }
        return $text;
    }

    /**
     * remove double whitespace or newlines
     *
     * @param string $text 
     * @return string
     */
    public static function removeDoubleWhiteSpace( $text )
    {
        // preg_replace with modifier u returns NULL if the text contains invalid UTF8 characters
        if( mb_check_encoding( $text, 'UTF8' ) )
        {
            $strippedText = preg_replace( array( '(\v{2,})u', '([\h\v]{2,})u' ), array( ' ', "\n" ), $text );
        }
        else
        {
            $strippedText = preg_replace( array( '(\v{2,})', '([\h\v]{2,})' ), array( ' ', "\n" ), $text );
        }

        // Make sure, that we didn't get an error
        if( !$strippedText )
        {
            $log = ezcLog::getInstance();
            $log->log( sprintf( 'error stripping text: ', substr( $text, 0, 200 ) ), ezcLog::ERROR );
            $strippedText = $text;
        }
        return $strippedText;
    }

    public function getLanguage( ymcCurlResponse $response )
    {
        // get Content-Language from CURL-Response
        $language = $response->contentLanguage;
        
        if( !empty( $language ) )
        {
          return $language;
        }
        
        
        // meta tag   <meta http-equiv="Content-Language" content="de" /> 
        $xPath = new DOMXPath( $this );
        $nodes = $xPath->query( '//meta[@http-equiv="Content-Language"]' );
        if( $nodes->length > 0 ) 
        {
          foreach ($nodes->item(0)->attributes as $attrName => $attrNode)
          {
            if( 'content' == $attrName ) return $attrNode->value;
          }
        }
        
        // meta tag   <meta name="language" content="de" />  
        $xPath = new DOMXPath( $this );
        $nodes = $xPath->query( '//meta[@name="language"]' );
        if( $nodes->length > 0 ) 
        {
          foreach ($nodes->item(0)->attributes as $attrName => $attrNode)
          {
            if( 'content' == $attrName ) return $attrNode->value;
          }
        }
        
        
        // lang="de"
        $xPath = new DOMXPath( $this );
        $nodes = $xPath->query( '//attribute::lang' );
        if( $nodes->length > 0 ) 
        {
          $languages = array();
          // if there are more languages count the most used language
          for($i=0; $i<$nodes->length; ++$i)
          {
            $language = trim($nodes->item($i)->value);
            if( !array_key_exists($language,$languages) ) $languages[$language] = false;
            ++$languages[$language];
          }

          asort( $languages );
          $keys = array_keys($languages);
          return array_shift($keys);
        }
        
        
        // xml:lang="de"
        $xPath = new DOMXPath( $this );
        $nodes = $xPath->query( '//attribute::xml:lang' );
        if( $nodes->length > 0 ) 
        {
          $languages = array();
          // if there are more languages count the most used language
          for($i=0; $i<$nodes->length; ++$i)
          {
            $language = trim($nodes->item($i)->value);
            if( !array_key_exists($language,$languages) ) $languages[$language] = false;
            ++$languages[$language];
          }

          asort( $languages );
          $keys = array_keys($languages);
          return array_shift($keys);
        }
        
        return false;
    }

    /**
     * Returns the href attribute of head/meta/base.
     *
     * Important to resolve relative links.
     * 
     * @return string / NULL
     */
    public function getBaseHref()
    {
        $xPath = new DOMXPath( $this );
        $nodes = $xPath->query( '/descendant::head/child::base' );

        foreach( $nodes as $node )
        {
            if( $node->hasAttribute( 'href' ) )
            {
                return $node->getAttribute( 'href' );
            }
        }
    }

    public function getTitle()
    {
        $xPath = new DOMXPath( $this );
        $nodes = $xPath->query( '/descendant::head/child::title' );

        foreach( $nodes as $node )
        {
            return trim( $node->textContent );
        }
        return NULL;
    }

    public function getHtml()
    {
        return $this->originHTML;
    }
}
