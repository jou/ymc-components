<?php

/**
 * Some helper methods around the RAM of a PHP process.
 * 
 */
class ymcLongLiveMemory
{
    /**
     * Returns the memory limit of the running PHP process in bytes 
     * 
     * @return int
     */
    public static function getMemoryLimit()
    {
        return self::normalizeMemoryValue( ini_get( 'memory_limit' ) );
    }

    /**
     * Converts a memory value with metric prefix into bytes.
     * 
     * @param string $mem 
     * @return int
     */
    public static function normalizeMemoryValue( $mem )
    {
        if ( preg_match( "#^([0-9]+) *([a-zA-Z]+)#", $mem, $matches ) )
        {
            $memBytes = (int)$matches[1];
            $unit = strtolower( $matches[2] );
            if ( $unit == 'k' )
            {
                $memBytes *= 1024;
            }
            else if ( $unit == 'm' )
            {
                $memBytes *= 1024*1024;
            }
            else if ( $unit == 'g' )
            {
                $memBytes *= 1024*1024*1024;
            }
        }
        else
        {
            $memBytes = (int)$memBytes;
        }
        return $memBytes;

    }

    /**
     * Checks, whether the running process has consumed more then $softLimit bytes of memory
     * 
     * @param int $softLimit 
     * @return bool
     */
    public static function hasExhausted( $softLimit )
    {
        return memory_get_usage() > ( int )$softLimit;
    }

    /**
     * Checks, whether more then $bytes memory is still free on this machine
     * 
     * @param int $bytes 
     * @return bool
     */
    public static function ensureFreeMemory( $bytes )
    {
        $memInfo = new ymcLongLiveMeminfo;

        return ( $memInfo->getApplicationFreeMemory() * 1024 ) > ( integer )$bytes;
    }
}
