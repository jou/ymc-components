<?php

/**
 * Base class for jobs.
 *
 * @property-read DateTime $executeAt When to execute the job or null if there's no time constraint
 * @property-write DateTime|int|string|null $executeAt Integers are interpreted as unix timestamps, strings are passed to DateTime::__construct().
 */
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

	/**
	 * When to execute this job. NULL says it can be executed any time.
	 * __set() takes care of the conversion to DateTime.
	 *
	 * @var DateTime
	 */
	protected $executeAt;

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
        switch ( $property ) 
        {
            case 'executeAt':
				if ( is_int( $value ) || is_numeric( $value ) )
                {
                    $value = DateTime::createFromFormat( 'U', (int)$value );
                }
				elseif ( is_string( $value ) )
                {
                    $value = new DateTime($value);
                }

				// Still neither a DateTime nor a null? So long!
                if ( ( $value !== null ) && !( $value instanceof DateTime ) )
                {
                    throw new Exception( "No valid time given" );
                }

                $this->executeAt = $value;
                break;
            default:
                $this->state[$property] = $value;
                break;
        }
    }

    public function __get( $property )
    {
		switch ( $property )
		{
			case 'executeAt':
				return $this->$property;
			default:
				if( isset( $this->state[$property]) )
				{
					return $this->state[$property];
				}

				return NULL;
		}
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
