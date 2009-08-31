<?php

class ymcLongLiveForkRunner
{
    /**
     * singleton instance of this class.
     */
    private static $instance;

    /**
     * All processes forked by this runner.
     * 
     * @var array( pid => ymcLongLiveFork )
     */
    private $children = array();

    /**
     * Whether terminated children should be restarted.
     *
     * Must be set to false when receiving a SIGKILL. Otherwise the program is unstoppable.
     * 
     * @var boolean
     */
    protected $respawn = TRUE;

    /**
     * Starts $count clones of $fork
     * 
     * @param int $count 
     * @param ymcLongLiveFork $fork 
     * @param int $snooze 
     */
    public function multiFork( $count, ymcLongLiveFork $fork, $snooze = 0 )
    {
        while( $count-- )
        {
            $this->fork( clone( $fork ), $snooze );
        }
    }

    /**
     * Starts a fork
     * 
     * @param ymcLongLiveFork $fork contains the callback to run in the forked child
     * @param float $snooze   wait $snooze seconds before entering the callback
     *
     * @todo the exit() call could get a meaningful number
     * @todo anything to do with the return value?
     */
    public function fork( ymcLongLiveFork $fork, $snooze = 0 )
    {
        $fork->setStart();
        $pid = pcntl_fork();
        if( $pid === -1 )
        {
            throw new Exception('could not fork');
        } 
        else 
        {
            if( $pid === 0 )
            {
                //child
                //self::log( sprintf( 'In new child' ), ezcLog::INFO );
                if( $snooze > 0 )
                {
                    sleep( $snooze );
                }
                $return = $fork->run();
                exit();
            }
            else
            {
                // parent
                self::log( sprintf( 'New child with pid %d.', $pid ), ezcLog::DEBUG );
                $this->children[$pid] = $fork;
            }
        }
    }

    /**
     * Sends $signal to all forks started by this runner
     * 
     * @param int $signal 
     */
    public function propagateSignal( $signal )
    {
        //@TODO move this somewhere else, shouldn't be hardcoded here
        $this->respawn = FALSE;

        self::log( sprintf( 'Propagate Signal %d.', $signal ), ezcLog::INFO );
        foreach( $this->children as $pid => $fork )
        {
            posix_kill( $pid, $signal );
        }
    }

    /**
     * Supervise the children and react on finished processes.
     * 
     * @param callback $ticker callback to call during each loop
     */
    public function supervise( $ticker = NULL )
    {
        // loop and monitor children
        while( !empty( $this->children ) )
        {
            $start = time();
            // Check if a child exited
            foreach( $this->children as $pid => $fork )
            {
                $exited = pcntl_waitpid( $pid, $status, WNOHANG );
                switch( $exited )
                {
                    case $pid:
                        $fork->setStop();
                        unset( $this->children[$pid] );
                        switch( TRUE )
                        {
                            // signal which was not caught
                            case pcntl_wifsignaled( $status ):
                                self::log(
                                  sprintf( 
                                    'Child %d, %s terminated from signal %d after running %d seconds.',
                                    $pid,
                                    $fork->description,
                                    pcntl_wtermsig( $status ),
                                    $fork->getDurationSeconds()
                                  ), ezcLog::INFO );
                            break;

                            case pcntl_wifexited( $status ):
                                $exitstatus = pcntl_wexitstatus( $status );
                                self::log(
                                  sprintf( 
                                    'Child %d, %s exited with status %d after running %d seconds.',
                                    $pid,
                                    $fork->description,
                                    $exitstatus,
                                    $fork->getDurationSeconds()
                                  ), ezcLog::INFO );
                                  //@TODO make reforking configurable
                                  if( $this->respawn )
                                  {
                                      self::log( 'refork '.$fork->description, ezcLog::INFO );
                                      $this->fork( $fork );
                                  }
                            break;

                            case pcntl_wifstopped( $status ):
                                self::log(
                                  sprintf(
                                    'Child %d, %s stopped from signal %d after running %d seconds.',
                                    $pid,
                                    $fork->description,
                                    pcntl_wstopsig( $status ),
                                    $fork->getDurationSeconds()
                                  ), ezcLog::INFO );
                            break;
                        }
                    break;

                    case -1:
                        self::log( sprintf( 'Got -1 when checking pid %d', $pid ), ezcLog::ERROR );
                    break;

                    case 0:
                    break;

                    default:
                        throw new Exception( 'pcntl_waitpid returned '.$exited.' for pid '.$pid );
                }
            }
            // save CPU cycles
            // sleep( 5 );
            $this->runServer();
            if( is_callable( $ticker ) )
            {
                call_user_func( $ticker );
            }

            self::log( sprintf( 'fork runner supervise loop took %d seconds.', time() - $start ), ezcLog::DEBUG );
        }
        self::log( 'Leaving fork runner supervise function', ezcLog::DEBUG );
    }

    /**
     * having this method here in the fork runner is an ugly hack.
     * 
     */
    protected function runServer()
    {
        static $server;
        if( !$server )
        {
            $server = new ymcLongLiveSimpleServer( 5678 );
        }
        $line = $server->getLine( 5 );
        if( !$line )
        {
            ezcLog::getInstance()->log( 'got nothing from client', ezcLog::DEBUG );
            return;
        }

        switch( $line )
        {
            case 'status':
                $now = time();
                foreach( $this->children as $pid => $fork )
                {
                    $out = sprintf( "[%8d] %10d s %s\n", 
                                    (int)$pid,
                                    (int)$now - $fork->startTime->format( 'U' ),
                                    $fork->description
                                    );    
                    $server->write( $out );
                }
            break;
            default:
                $server->write( 'unknown command '.$line."\n" );
            break;
        }
    }

    public static function getInstance()
    {
        if( !self::$instance instanceof self )
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * would be better to use trigger_error as described in the eZ Components docs.
     * 
     */
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
