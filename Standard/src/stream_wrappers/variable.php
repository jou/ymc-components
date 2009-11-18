<?php

/**
 * VariableStream 
 * 
 * Example:
 *
 *
 *

stream_wrapper_register("ymcvar", "VariableStream")
    or die("Failed to register protocol");


$fp = fopen("ymcvar://myvar", "w");

fputcsv( $fp, array( 'hi1ä', 123, "'my', 'dreams'", 'hi, "over", there' ) );
fputcsv( $fp, array( 'hi2', 123, "hi\thi\\@", "asdf\nasdf" ) );
fputcsv( $fp, array( 'hi2', 123, addcslashes( "ähi\thi\\@", "\0..\37!@" ), "asdf\nasdf" ) );
fclose($fp);

$fp = fopen("ymcvar://myvar", "r");
$out = "";
while (!feof($fp)) {
    $out .= fgets($fp);
}
var_dump( $out );
fclose($fp);

 */
class ymcStandardStreamWrapperVariable
{
    /**
     * Position in the current stream.
     * 
     * @var integer
     */
    private $position;

    /**
     * Variable name of the current stream.
     * 
     * @var string
     */
    private $varname;

    /**
     * Scheme name of the current stream.
     * 
     * @var string
     */
    private $scheme;

    /**
     * Data of the current stream.
     * 
     * @var string
     */
    private $variable;

    /**
     * Variables separated by scheme
     *
     * @var array( scheme => array( name => data ) )
     */
    private static $variables = array();

    public function stream_open( $path, $mode, $options, &$opened_path )
    {
        $url = parse_url($path);
        $this->scheme   = $url["scheme"];
        if( !self::isRegisteredInternal( $this->scheme ) )
        {
            throw new Exception( 'Scheme '.$this->scheme.' has not been registered with class '.__CLASS__ );
        }
        $this->varname  = $url["host"];

        // default, may be overridden in the switch
        $this->position = 0;

        switch( $mode )
        {
            case 'r': // Open for reading only; place the file pointer at the beginning of the file.
                if( !array_key_exists( $this->varname, self::$variables[$this->scheme] ) )
                {
                    return FALSE;
                }
            break;
            case 'r+': //Open for reading and writing; place the file pointer at the beginning of the file.
                throw new Exception( 'NOT YET IMPLEMENTED' );
            break;

            case 'w+': // Open for reading and writing; place the file pointer at the beginning of
                       // the file and truncate the file to zero length. If the file does not exist, attempt to
                       // create it.
            case 'w': // Open for writing only; place the file pointer at the beginning of the file
                      // and truncate the file to zero length. If the file does not exist, attempt to create
                      // it.
                self::$variables[$this->scheme][$this->varname] = '';
            break;

            case 'a': //Open for writing only; place the file pointer at the end of the file. If the file does not exist, attempt to create it.
                throw new Exception( 'NOT YET IMPLEMENTED' );
            break;
            case 'a+': //Open for reading and writing; place the file pointer at the end of the file. If the file does not exist, attempt to create it.
                throw new Exception( 'NOT YET IMPLEMENTED' );
            break;

            case 'x': //Create and open for writing only; place the file pointer at the beginning of the file. If the file already exists, the fopen() call will fail by returning FALSE and generating an error of level E_WARNING. If the file does not exist, attempt to create it. This is equivalent to specifying O_EXCL|O_CREAT flags for the underlying open(2) system call.
                throw new Exception( 'NOT YET IMPLEMENTED' );
            break;
            case 'x+': //Create and open for reading and writing; place the file pointer at the beginning of the file. If the file already exists, the fopen() call will fail by returning FALSE and generating an error of level E_WARNING. If the file does not exist, attempt to create it. This is equivalent to specifying O_EXCL|O_CREAT flags for the underlying open(2) system call. 
                throw new Exception( 'NOT YET IMPLEMENTED' );
            break;
            default:
                throw new Exception( 'Unknown mode '.$mode );
        }
        $this->variable =& self::$variables[$this->scheme][$this->varname];

        return true;
    }

    public function stream_read($count)
    {
        $ret = substr($this->variable, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    public function stream_write($data)
    {
        $left  = substr( $this->variable, 0, $this->position );
        $right = substr( $this->variable, $this->position + strlen( $data ) );
        $this->variable = $left . $data . $right;
        $this->position += strlen($data);
        return strlen( $data );
    }

    public function stream_tell()
    {
        return $this->position;
    }

    public function stream_eof()
    {
        return $this->position >= strlen( $this->variable );
    }

    public function stream_seek($offset, $whence)
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen($this->variable) && $offset >= 0) {
                     $this->position = $offset;
                     return true;
                } else {
                     return false;
                }
                break;

            case SEEK_CUR:
                if ($offset >= 0) {
                     $this->position += $offset;
                     return true;
                } else {
                     return false;
                }
                break;

            case SEEK_END:
                if (strlen($this->variable) + $offset >= 0) {
                     $this->position = strlen($this->variable) + $offset;
                     return true;
                } else {
                     return false;
                }
                break;

            default:
                return false;
        }
    }


    /**
     * Returns information about a handle implemented by this wrapper.
     *
     * Needed so that stream_get_contents() on this wrapper works.
     * 
     * @return array()
     */
    public function stream_stat()
    {
        return array( 
        
        );
    }

    // Direct access methods

    /**
     * Returns the content of a variable indicated by scheme://path.
     * 
     * @param string $path 
     * @return string
     */
    public static function getContent( $path )
    {
        return self::getVariable( $path );
    }

    /**
     * Directly sets the content of a variable indicated by scheme://path.
     * 
     * @param string $path 
     * @param string $content 
     */
    public static function putContent( $path, $content )
    {
        $variable =& self::getVariable( $path, TRUE );
        $variable = $content;
    }

    protected static function &getVariable( $path, $create = FALSE )
    {
        $url = parse_url( $path );
        $scheme  = $url['scheme'];
        $varname = $url['host'];

        if( !self::isRegisteredInternal( $scheme ) )
        {
            throw new Exception( 'Scheme has not been registered with class '.__CLASS__ );
        }
        if( !array_key_exists( $url['host'], self::$variables[$scheme] ) )
        {
            if( $create )
            {
                self::$variables[$scheme][$varname] = '';
            }
            else
            {
                throw new Exception( 'Unknown variable'.__CLASS__ );
            }
        }
        return self::$variables[$scheme][$varname];
    }

    // Registration methods

    public static function registerInternal( $scheme )
    {
        if( self::isRegisteredInternal( $scheme ) )
        {
            throw new Exception( 'Scheme '.$scheme.' already registered.' );
        }
        self::$variables[$scheme] = array();
    }

    public static function unregisterInternal( $scheme )
    {
        if( !self::isRegisteredInternal( $scheme ) )
        {
            throw new Exception( 'Scheme '.$scheme.' not registered.' );
        }
        unset( self::$variables[$scheme] );
    }

    public static function isRegisteredInternal( $scheme )
    {
        return array_key_exists( $scheme, self::$variables );
    }

    public static function register( $scheme )
    {
        self::registerInternal( $scheme );
        if( !stream_wrapper_register( $scheme, __CLASS__ ) )
        {
            self::unregisterInternal( $scheme );
            throw new Exception( 'Scheme is already registered with PHP' );
        }
    }
    
    public static function unregister( $scheme )
    {
        self::unregisterInternal( $scheme );
        if( !stream_wrapper_unregister( $scheme ) )
        {
            throw new Exception( 'Could not unregister Scheme with PHP' );
        }
    }
}
