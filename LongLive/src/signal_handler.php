<?php

// Deprecated from PHP 5.3. on but needed in PHP 5.2
if( version_compare( PHP_VERSION, '5.3.0', '<' ) )
{
    declare( ticks=1 );
}

/**
 * Class to register and execute POSIX signal handler.
 * 
 */
class ymcLongLiveSignalHandler
{
    /**
     * Registry of callbacks for posix signals
     *
     * @var array( int => array( callback ) ) 
     */
    private static $callbacks = array();

    /**
     * Signals received and waiting for dispatching
     *
     * @var array( int ) 
     */
    private static $waitingSignals = array();

    /**
     * Register a callback to be executed on $signal.
     * 
     * @param int      $signal    POSIX signal, like SIGTERM
     * @param callback $callback  signal handler
     */
    public static function registerCallback( $signal, $callback )
    {
        if( !is_callable( $callback ) )
        {
            throw new Exception( 'Not Callable' );
        }

        if( !isset( self::$callbacks[$signal] ) )
        {
            self::$callbacks[$signal] = array();
            pcntl_signal( $signal, array( __CLASS__, 'handleSignal' ) );
        }

        self::$callbacks[$signal][] = $callback;
    }

    /**
     * Handler method called by the PHP process when receiving a registered signal.
     *
     * Registers the signal as waiting for execution.
     * 
     * @param int $signal 
     */
    public static function handleSignal( $signal )
    {
        ezcLog::getInstance()->log( 'Received signal '.$signal, ezcLog::DEBUG );
        self::$waitingSignals[$signal] = $signal;
    }

    /**
     * Executes all callbacks for all waiting signals.
     * 
     */
    public static function dispatchAll()
    {
        foreach( self::$waitingSignals as $signal )
        {
            self::dispatch( $signal );
        }
    }

    /**
     * Executes callbacks and resets waiting flags for $signal
     * 
     * @param int $signal 
     */
    public static function dispatch( $signal )
    {
        if( isset( self::$waitingSignals[$signal] ) )
        {
            unset( self::$waitingSignals[$signal] );
        }

        ezcLog::getInstance()->log( 'Dispatching signal '.$signal, ezcLog::DEBUG );
        foreach( self::$callbacks[$signal] as $callback )
        {
            call_user_func( $callback, $signal );
        }
    }

    /**
     * Wrapper around PHP's exit statement to be registered as callback.
     *
     * This method is here only as a convenience. Feel free to ignore it.
     * 
     * @param integer $signal 
     */
    public static function halt( $signal )
    {
        ezcLog::getInstance()->log( 'halt in signal handler', ezcLog::DEBUG );
        exit();
    }
}
