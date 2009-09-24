<?php

class ymcCurlRequest
{
    /**
     * @var Array These options may not be set from outside 
     */
    private static $reservedOptions = array( 
        CURLOPT_HEADERFUNCTION  => 'CURLOPT_HEADERFUNCTION',
        CURLOPT_WRITEFUNCTION   => 'CURLOPT_WRITEFUNCTION',
    );

    public static $curlOptions = array(
        CURLOPT_AUTOREFERER  => TRUE, // TRUE to automatically set the Referer: field in requests where it follows a Location: redirect.  
        CURLOPT_BINARYTRANSFER => TRUE, //TRUE to return the raw output when CURLOPT_RETURNTRANSFER is used. 
        CURLOPT_COOKIESESSION => TRUE, //TRUE to mark this as a new cookie "session". It will force libcurl to ignore all cookies it is about to load that are "session cookies" from the previous session. By default, libcurl always stores and loads all cookies, independent if they are session cookies are not. Session cookies are cookies without expiry date and they are meant to be alive and existing for this "session" only. 
        CURLOPT_CRLF => FALSE, //TRUE to convert Unix newlines to CRLF newlines on transfers. 
        CURLOPT_DNS_USE_GLOBAL_CACHE => TRUE, //TRUE to use a global DNS cache. This option is not thread-safe and is enabled by default. 
        CURLOPT_FAILONERROR => FALSE, //TRUE to fail silently if the HTTP code returned is greater than or equal to 400. The default behavior is to return the page normally, ignoring the code. 
        CURLOPT_FILETIME => TRUE, //TRUE to attempt to retrieve the modification date of the remote document. This value can be retrieved using the CURLINFO_FILETIME option with curl_getinfo(). 
        CURLOPT_FOLLOWLOCATION => TRUE, //TRUE to follow any "Location: " header that the server sends as part of the HTTP header (note this is recursive, PHP will follow as many "Location: " headers that it is sent, unless CURLOPT_MAXREDIRS is set). 
        CURLOPT_FORBID_REUSE => FALSE, //TRUE to force the connection to explicitly close when it has finished processing, and not be pooled for reuse. 
        CURLOPT_FRESH_CONNECT => FALSE, //TRUE to force the use of a new connection instead of a cached one. 
        CURLOPT_HEADER => FALSE, //TRUE to include the header in the output. 
//        CURLOPT_MUTE => TRUE, //TRUE to be completely silent with regards to the cURL functions. 
        CURLOPT_RETURNTRANSFER => TRUE, //TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly. 
        CURLOPT_SSL_VERIFYPEER => FALSE, //FALSE to stop cURL from verifying the peer's certificate. Alternate certificates to verify against can be specified with the CURLOPT_CAINFO option or a certificate directory can be specified with the CURLOPT_CAPATH option. CURLOPT_SSL_VERIFYHOST may also need to be TRUE or FALSE if CURLOPT_SSL_VERIFYPEER is disabled (it defaults to 2). 
        CURLOPT_VERBOSE => FALSE, //TRUE to output verbose information. Writes output to STDERR, or the file specified using CURLOPT_STDERR. 

        // value should be an integer for the following values of the option parameter:
        CURLOPT_CONNECTTIMEOUT => 20, //The number of seconds to wait whilst trying to connect. Use 0 to wait indefinitely. 
        CURLOPT_DNS_CACHE_TIMEOUT => 360, //The number of seconds to keep DNS entries in memory. This option is set to 120 (2 minutes) by default. 
        CURLOPT_LOW_SPEED_LIMIT => 1, //The transfer speed, in bytes per second, that the transfer should be below during CURLOPT_LOW_SPEED_TIME seconds for PHP to consider the transfer too slow and abort. 
        CURLOPT_LOW_SPEED_TIME => 120, //The number of seconds the transfer should be below CURLOPT_LOW_SPEED_LIMIT for PHP to consider the transfer too slow and abort. 
        CURLOPT_MAXCONNECTS => 20, //The maximum amount of persistent connections that are allowed. When the limit is reached, CURLOPT_CLOSEPOLICY is used to determine which connection to close. 
        CURLOPT_MAXREDIRS => 5, //The maximum amount of HTTP redirections to follow. Use this option alongside CURLOPT_FOLLOWLOCATION. 
        CURLOPT_TIMEOUT => 360, //The maximum number of seconds to allow cURL functions to execute. 

        // value should be a string for the following values of the option parameter:
        CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.0.3) Gecko/2008092814 Iceweasel/3.0.3 (Debian-3.0.3-3)', //The contents of the "User-Agent: " header to be used in a HTTP request. 
    );

    public $url;

    /**
     * A depot that can be freely used to deposit any kind of information during a multi handle
     * execution.
     * 
     * @var mixed
     * @access public
     */
    public $depot;

    protected $curlHandle;

    protected $receivedBody = '';
    protected $receivedHeader  = '';
    public $response;
    protected $curlInfo;
    protected $options = array( 
        'maxSize' => NULL
    );

    public function __construct( $url, $options = array() )
    {
        $this->url = $url;

        foreach( $options as $option => $value )
        {
            if( !array_key_exists( $option, $this->options ) )
            {
                throw new Exception( 'Unknown option '.$option );
            }
            $this->options[$option] = $value;
        }
    }

    public function __destruct(  )
    {
        $this->closeHandle();
    }

    public function closeHandle()
    {
        if( is_resource( $this->curlHandle ) )
        {
            curl_close( $this->curlHandle );
        }
    }

    public function getHandle()
    {
        if( !is_resource( $this->curlHandle ) )
        {
            return $this->initCurl();
        }
        return $this->curlHandle;
    }

    protected function initCurl()
    {
        $ch = curl_init( trim( $this->url ) );
        $this->checkForError( $ch );

        foreach( self::$curlOptions as $option => $value )
        {
            $this->setOptionInternal( $option, $value, $ch );
        }
        $this->setOptionInternal( CURLOPT_HEADERFUNCTION, array( $this, '_receiveHeader' ), $ch );
        $this->setOptionInternal( CURLOPT_WRITEFUNCTION, array( $this, '_receiveBody' ), $ch );

        // value should be a stream resource (using fopen(), for example) for the following values of the option parameter:
//        CURLOPT_STDERR => , //An alternative location to output errors to instead of STDERR. 

        $this->curlHandle = $ch;
        return $ch;
    }

    public function _receiveHeader( $ch, $header )
    {
        $this->receivedHeader .= $header;

        // Check for too large files
        if( $this->options['maxSize'] )
        {
            if( preg_match( '/^Content-Length: (\d+)$/', trim( $header ), $matches ) )
            {
                $size = $matches[1];
                if( $size > $this->options['maxSize'] )
                {
                    // Chancel the transfer
                    ezcLog::getInstance()->log( $this->url.' Lenght in header: '.$size, ezcLog::ERROR );
                    return -1;
                }
            }
        }

        return strlen( $header );
    }

    public function _receiveBody( $ch, $string )
    {
        $this->receivedBody .= $string;

        if( $this->options['maxSize'] )
        {
            if( strlen( $this->receivedBody ) > $this->options['maxSize'] )
            {
                // Chancel the transfer
                ezcLog::getInstance()->log( $this->url.' Too much data.', ezcLog::ERROR );
                return -1;
            }
        }

        return strlen( $string );
    }

    /**
     * Sets an option for the curl handle of this request and checks for errors afterwards.
     *
     * There's a Bug ( #46711 ) in the PHP curl extension that leads to memory leaks.
     * 
     * @param integer $option 
     * @param mixed   $value 
     *
     * @throws
     */
    public function setOption( $option, $value )
    {
        if( array_key_exists( ( int )$option, self::$reservedOptions ) )
        {
            throw new Exception( 'You may not set '.self::$reservedOptions[( int )$option] );
        }
        $ch = $this->getHandle();
        $this->setOptionInternal( $option, $value, $ch );
    }

    protected function setOptionInternal( $option, $value, $ch )
    {
        if( curl_setopt( $ch, $option, $value ) ) return;
        $this->checkForError( $ch );

        // sth. went wrong
        throw new Exception( 'Could not set option '.$option.' with value '.$value.' of type '.gettype( $value ) );
    }

    public function onCompletion()
    {
        $ch = $this->curlHandle;
        $this->checkForError( $ch );
        $this->curlInfo = curl_getinfo( $ch );

        $response = new ymcCurlResponse;
        $response->url      = $this->url;
        $response->body     = $this->receivedBody;
        $response->header   = $this->receivedHeader;
        $response->received = new DateTime;
        $response->parseCurlInfo( $this->curlInfo );
        $response->contentLanguage = $response->getHeader('Content-Language', '\n' );

        $this->response = $response;
        $this->closeHandle();
    }

    public function run()
    {
        $ch = $this->getHandle();
        curl_exec( $ch );
        $this->onCompletion();
    }

    public function checkForError( $ch = NULL )
    {
        $ch = is_resource( $ch ) ? $ch : $this->curlHandle;

        if( CURLE_OK === ( $errNo = curl_errno( $ch ) ) ) return;

        throw new Exception( 
            sprintf(  
                'Curl error %d. %s %s',
                $errNo,
                ( NULL !== $this->url ? $this->url : '' ),
                curl_error( $ch )
            )
        );
    }

    public function __get( $name )
    {
        switch( $name )
        {
            case 'curlInfo':
                return $this->$name;
        }
    }
    
    public function wasSuccessful()
    {
        if( !$this->response ) return FALSE;

        if( 200 === !$this->response->httpCode ) return FALSE;

        return TRUE;
    }
}
