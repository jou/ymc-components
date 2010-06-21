<?php

abstract class ymcJobQueueJob implements Serializable
{
    public $id;

    public $priority = 100;

    /**
     * State of the job.
     * 
     * @var array
     */
    protected $state = array();

    private $_needsUpdate_ = FALSE;

    abstract public function run();

    public function createFollowUps()
    {
        $followUp = $this->createFollowUp();

        if( $followUp instanceof ymcJobQueueJob )
        {
            return array( $followUp );
        }
    }

    /**
     * Copies the state values from $this to $job.
     * 
     * @param self $job 
     */
    public function copyState( self $job )
    {
        foreach( $this->state as $property => $value )
        {
            $job->$property = $value;
        }
    }

    public function createFollowUp()
    {
    }

    public function clean()
    {
    }

    public function serialize()
    {
        if( empty( $this->state ) )
        {
            return '';
        }
        return serialize( $this->state );
    }

    public function unserialize( $state )
    {
        if( '' !== $state && is_string( $state ) )
        {
            $this->state = unserialize( $state );
        }
    }

    public function __set( $property, $value )
    {
        $this->state[$property] = $value;
    }

    public function __get( $property )
    {
        if( isset( $this->state[$property]) )
        {
            return $this->state[$property];
        }

        return NULL;
    }

    public function __isset( $property )
    {
        return isset( $this->state[$property] );
    }

    public function needsUpdate()
    {
        return $this->_needsUpdate_;
    }

    public function flagForUpdate()
    {
        $this->_needsUpdate_ = TRUE;
    }
}
