<?php

class ymcLongLiveBatchRunner
{
    /**
     * Counts the number of times the while loop iterated
     * 
     * @var integer
     */
    protected $numberOfPerformedJobs = 0;

    /**
     * options 
     * 
     * @var ymcLongLiveBatchRunnerOptions
     */
    protected $options;

    public function __construct( ymcLongLiveBatchRunnerOptions $options = NULL )
    {
        if( NULL === $options )
        {
            $options = new ymcLongLiveBatchRunnerOptions;
        }
        $this->options = $options;
    }

    public function run( $callback = NULL, $arguments = NULL )
    {
        // get options
        $options = $this->options;
        if( !is_callable( $callback ) )
        {
            $callback = $options->callback;
            if( !is_callable( $callback ) )
            {
                throw new Exception( 'no callable callback given.' );
            }
        }

        if( !is_array( $arguments ) )
        {
            $arguments = $options->arguments;
        }

        $sleep               = $options->sleep;
        $maxExecutionTime    = $options->maxExecutionTime;
        $gracefulSigterm     = $options->gracefulSigterm;
        $freeSystemMemory    = $options->freeSystemMemory;

        $memoryLimit         = $options->memoryLimit;
        $relativeMemoryLimit = $options->relativeMemoryLimit;
        
        if( !$memoryLimit && 0.0 !== $relativeMemoryLimit )
        {
            $memoryLimit = ( int ) ( ymcLongLiveMemory::getMemoryLimit() * $relativeMemoryLimit );
        }

        //@todo move this somewhere else
        if( $gracefulSigterm )
        {
            ymcLongLiveSignalHandler::registerCallback( SIGTERM, array( 'ymcLongLiveSignalHandler', 'halt' ) );
        }

        $endTime = $maxExecutionTime ? time() + $maxExecutionTime : NULL;

        $callbackString = self::callbackToString( $callback );
        self::log( 'Enter batch runner while loop with sleep '.$sleep );
        while( TRUE )
        {
            //self::log( 'start batch loop', ezcLog::DEBUG );
            if( $endTime && time() > $endTime )
            {
                self::log( $callbackString.' batch runner exit due to time limit of '.$maxExecutionTime, ezcLog::DEBUG );
                return TRUE;
            }

            if( ymcLongLiveMemory::hasExhausted( $memoryLimit ) )
            {
                self::log( $callbackString.' batch runner exit due to memory limit of '.$memoryLimit, ezcLog::DEBUG );
                return TRUE;
            }

            if( $freeSystemMemory && !ymcLongLiveMemory::ensureFreeMemory( $freeSystemMemory ) )
            {
                self::log( $callbackString.' batch runner exit due to ensured free memory '.$freeSystemMemory, ezcLog::DEBUG );
                return TRUE;
            }

            if( $gracefulSigterm )
            {
                ymcLongLiveSignalHandler::dispatchAll();
            }

            self::log( sprintf( 'Start Function %s', $callbackString ), ezcLog::DEBUG );
            try
            {
                $return = call_user_func_array( $callback, $arguments );
            } catch ( Exception $e )
            {
                self::log( (string)$e, ezcLog::ERROR );
                $return = FALSE;
            }
            ++$this->numberOfPerformedJobs;
            self::log( sprintf( 'Function %s returned %s', $callbackString, $return ? 'TRUE' : 'FALSE' ), ezcLog::DEBUG );

            //@todo allow other break conditions
            if( !$return )
            {
                if( 0 === $sleep )
                {
                    self::log( 'batch runner exit due to return value', ezcLog::DEBUG );
                    return FALSE;
                }
                self::log( sprintf( 'sleep %d seconds', ( int )$sleep ), ezcLog::DEBUG );
                sleep( ( int )$sleep );
            }
            //self::log( 'End batch loop', ezcLog::DEBUG );
        }
    }

    public function getNumberOfPerformedJobs()
    {
        return $this->numberOfPerformedJobs;
    }

    public static function runWithDefaults( $callback = NULL, $arguments = NULL )
    {
        $runner = new self;
        return $runner->run( $callback, $arguments );
    }

    public function setOption( $name, $value )
    {
        $this->options->$name = $value;
    }

    public function getOption( $name )
    {
        return $this->options->$name;
    }

    public static function callbackToString( $callback )
    {
        if( is_string( $callback ) )
        {
            return $callback;
        }

        if( is_array( $callback ) && count( $callback ) === 2 )
        {
            if( is_string( $callback[0] ) )
            {
                return $callback[0].'::'.$callback[1];
            }elseif( is_object( $callback[0] ) )
            {
                return get_class( $callback[0] ).'->'.$callback[1];
            }
        }

        return 'not a callback';
    }

    protected static function log( $message, $severity = ezcLog::DEBUG, Array $attributes = array() )
    {
        static $log;
        if( !$log )
        {
            $log = ezcLog::getInstance();
        }

        if( !array_key_exists( 'source', $attributes ) )
        {
            $attributes['source'] = __CLASS__;
        }

        $log->log( $message, $severity, $attributes );
    }
}
