<?php

interface ymcPipeExecutionApi
{
    /**
     * Sets the execution object, this api ( currently ) belongs to.
     * 
     * @param ymcPipeExecution $execution 
     * @return void
     */
    public function setExecution( ymcPipeExecution $execution );

    /**
     * Fallback method for api calls not known.
     * 
     * @param string $name 
     * @param array  $arguments 
     *
     * @throws ymcPipeExecutionException if called method is not known
     * @return mixed
     */
    public function __call( $name, $arguments );
}
