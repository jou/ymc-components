<?php

/**
 * Class to represent a fork of the PHP process.
 * 
 */
class ymcLongLiveFork
{
    /**
     * The timestamp in seconds, when this fork has been started.
     *
     * May be useful for debugging to know how much time it has been run.
     * 
     * @var DateTime
     */
    protected $startTime;

    /**
     * The timestamp in seconds, when this fork has stopped execution.
     *
     * May be useful for debugging to know how much time it has been run.
     * 
     * @var DateTime
     */
    protected $stopTime;

    /**
     * The callback that is executed by the fork.
     *
     * It should be possible to use the same callback multiple times to respawn a fork if
     * necessary.
     * 
     * @var callback
     */
    protected $forkCallback;

    /**
     * Parameters to give to the fork callback.
     * 
     * @var array
     */
    protected $forkCallbackParameters;

    protected $description;

    public function __construct( $callback = NULL, $parameters = array(), $description = '' )
    {
        if( is_array( $callback ) && is_object( $callback[0] ) )
        {
            $this->forkCallback = array( clone( $callback[0] ), $callback[1] );
        }
        else
        {
            $this->forkCallback = $callback;
        }

        $this->forkCallbackParameters = $parameters;
        $this->description            = $description;
    }

    public function run()
    {
        return call_user_func_array( $this->forkCallback, $this->forkCallbackParameters );
    }

    /**
     * Sets the starttime of this fork.
     * 
     * @param DateTime $now 
     */
    public function setStart( DateTime $now = NULL )
    {
        if( $now instanceof DateTime )
        {
            $this->startTime = clone( $now );
        }
        else
        {
            $this->startTime = new DateTime;
        }
    }

    /**
     * Sets the stoptime of this fork.
     * 
     * @param DateTime $now 
     */
    public function setStop( DateTime $now = NULL )
    {
        if( $now instanceof DateTime )
        {
            $this->stopTime = clone( $now );
        }
        else
        {
            $this->stopTime = new DateTime;
        }
    }

    /**
     * Returns the runtime duration of this fork after it has returned.
     * 
     * @return int
     */
    public function getDurationSeconds()
    {
        if( !$this->startTime instanceof DateTime or !$this->stopTime instanceof DateTime )
        {
            throw new Exception;
        }
        return ( int )$this->stopTime->format( 'U' ) - ( int )$this->startTime->format( 'U' );
    }

    public function __get( $name )
    {
        switch( $name )
        {
            case 'description':
            case 'startTime':
                return $this->$name;
        }
    }
}
