<?php

/**
 * A queue is a stack of jobs that need to be done.
 * 
 */
class ymcJobQueue
{
    /**
     * The physical storage of the jobs
     * 
     * @var QueueStorage
     */
    private $storage;

    /**
     * Constructs a queue
     * 
     * @param QueueStorage $storage 
     */
    public function __construct( /*ymcJobQueueStorage*/ $storage )
    {
        $this->storage = $storage;
    }

    /**
     * Pushes a new job to the queue.
     * 
     * @param ymcJobQueueJob $job 
     */
    public function push( ymcJobQueueJob $job, $activate = FALSE )
    {
        if( $job instanceof Serializable )
        {
            $state = $job->serialize();
        }
        else
        {
            $state = '';
        }
        $id = $this->storage->push( get_class( $job ), $state, $activate ? $job->priority : 0 );
        $job->id = $id;
    }

    /**
     * Returns a job and locks it but doesn't deletes it from the DB.
     * 
     * @param array $jobClasses optional list of job types I'd like to receive.
     * @param bool $randomizeJobList Whether to shuffle job list before trying to aquire a lock.
     * @return ymcJobQueueJob
     */
    public function pop( $jobClasses = array(), $randomizeJobList = true )
    {
        $job = $this->storage->pop( $jobClasses, $randomizeJobList );

        if( !$job ) return NULL;
        
        return $this->arrayToJob( $job );
    }

    /**
     * Deletes the job from the DB and releases the lock on it.
     * 
     */
    public function done( ymcJobQueueJob $job )
    {
        $followUpJobs = $job->createFollowUps();

        $followUps = array();
        if( is_array( $followUpJobs ) )
        {
            foreach( $followUpJobs as $followUpJob )
            {
                $followUps[] = $this->jobToArray( $followUpJob );
            }
        }

        $this->storage->done( $job->id, $followUps );
    }

    public function activate( ymcJobQueueJob $job )
    {
        $this->storage->activate( $job->id, $job->priority );
    }

    /**
     * Cancel a job
     * @param ymcJobQueueJob $job 
     */
    public function cancel( ymcJobQueueJob $job )
    {
        $this->storage->cancel( $job->id );
    }

    /**
     * Returns the data of a job object.
     * 
     * @param ymcJobQueueJob $job 
     * @return array
     */
    protected function jobToArray( ymcJobQueueJob $job )
    {
        return array( 
            'class' => get_class( $job ),
            'priority' => $job->priority,
            'state' => $job instanceof Serializable
                       ? $job->serialize()
                       : ''
        );
    }

    /**
     * Returns a job object from the given data array.
     * 
     * @param Array $jobArray with keys id, class, priority, state
     * @return ymcJobQueueJob
     */
    protected function arrayToJob( Array $jobArray )
    {
        $class = $jobArray['class'];
        if( !class_exists( $class ) )
        {
            throw new Exception( 'Class '.$class.' not found!' );
        }
        $job = new $class; 
        if( $job instanceof Serializable && $jobArray['state'] )
        {
            $job->unserialize( $jobArray['state'] );
        }
        $job->id = $jobArray['id'];
        $job->priority = $jobArray['priority'];

        return $job;
    }

    /**
     * Updates a job. 
     * 
     * @param ymcJobQueueJob $job 
     */
    public function update( ymcJobQueueJob $job )
    {
        if( $job instanceof Serializable )
        {
            $state = $job->serialize();
        }
        else
        {
            $state = '';
        }
        $this->storage->update( $job->id, get_class( $job ), $state, $job->priority );
    }

    /**
     * Run the job 
     */
    public function run( $classes = NULL )
    {
        static $job;
    
        if( !$job )
        {
            $queue = ymcJobQueueInstance::get();
            $job = $queue->pop( $classes ? $classes : array() );

            if( !$job ) return FALSE;
        }

        try
        {
            $result = $job->run();
        }
        catch( Exception $e )
        {
            // Jobs throwing exceptions will be deleted!
            ezcLog::getInstance()->log( 'Exception while running job '.$e, ezcLog::ERROR );
            $queue = ymcJobQueueInstance::get();
            $queue->cancel( $job );
            $job = NULL;
            return TRUE;
        }

        // Done with this job!
        if( !$result )
        {
            $queue = ymcJobQueueInstance::get();
            $queue->done( $job );
            $job->clean();
            $job = NULL;
        }
        elseif( $job->needsUpdate() )
        {
            $this->update( $job );
        }

        return TRUE;
    }
}
