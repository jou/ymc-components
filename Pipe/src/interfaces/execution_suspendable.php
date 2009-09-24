<?php

abstract class ymcPipeExecutionSuspendable extends ymcPipeExecution
{
    public function resume()
    {
        if( self::SUSPENDED !== $this->executionState )
        {
            throw new Exception( 'Can only resume suspended executions.' );
        }
        $this->execute();
    }

    public function run()
    {
        switch( $this->executionState )
        {
            case self::SUSPENDED:
                return $this->resume();
            break;

            case self::NOT_STARTED:
                return $this->start();
            break;

            default:
                throw new ymcPipeExecutionException( 'Execution is neither suspended nor not started.' );
            break;
        }
    }
}
