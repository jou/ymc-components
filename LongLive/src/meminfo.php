<?php

/**
 * Wrapper around /proc/meminfo.
 * 
 */
class ymcLongLiveMeminfo
{
    /**
     * filename to parse, defaults to /proc/meminfo
     *
     * Can be changed in the constructor mostly for testing
     * 
     * @var string
     */
    private $filename;

    /**
     * The parsed meminfo
     * 
     * @todo is it save to cast to int in parseMemInfo?
     * @var array( string => number string )
     */
    private $memInfo;

    public function __construct( $filename = '/proc/meminfo' )
    {
        $this->filename = $filename;
        $this->parseMemInfo();
    }

    /**
     * Parses the meminfo.
     * 
     * @return void
     */
    private function parseMemInfo()
    {
        $memInfo = array();
        $fh = fopen( $this->filename, 'r' );
        while( !feof( $fh ))
        {
            if( fscanf( $fh, '%[^:]%*[: ]%u%*[^0-9]', $key, $value ) ) 
            {
                $memInfo[strtolower( $key )] = $value;
            }
        }
        $this->memInfo = $memInfo;
    }

    public function reset()
    {
        $this->parseMemInfo();
    }

    /**
     * Returns the value from a meminfo line.
     * 
     * @param string $key 
     * @return integer
     */
    public function get( $key )
    {
        $lowerKey = strtolower( $key );
        if( array_key_exists( $lowerKey, $this->memInfo ) )
        {
            return $this->memInfo[$lowerKey];
        }
        throw new Exception( 'Unknown key: '.$key );
    }

    /**
     * Returns the amount of RAM available to applications.
     *
     * The amount is higher then the free memory, since Linux uses free RAM for IO caching, but
     * makes it available on application request.
     * 
     * @return integer
     */
    public function getApplicationFreeMemory()
    {
        return $this->memInfo['memfree']
             + $this->memInfo['buffers']
             + $this->memInfo['cached'];
    }
}
