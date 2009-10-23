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
    private $position;
    private $varname;
    private $variable;
    private static $variables = array();

    public function stream_open( $path, $mode, $options, &$opened_path )
    {
        $url = parse_url($path);
        $this->varname  = $url["host"];

        // default, may be overridden in the switch
        $this->position = 0;

        switch( $mode )
        {
            case 'r': // Open for reading only; place the file pointer at the beginning of the file.
                if( !array_key_exists( $this->varname, self::$variables ) )
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
                self::$variables[$this->varname] = '';
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
        $this->variable =& self::$variables[$this->varname];

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
}
