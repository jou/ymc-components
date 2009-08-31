<?php

/**
 * ymcLongLiveBatchRunnerOptions 
 * 
 * @property maxExecutionTime The maximum duration in seconds after which no new batch run should
 *                            be started.
 */
class ymcLongLiveBatchRunnerOptions extends ezcBaseOptions
{
    protected $properties = array( 
        'callback'            => NULL,
        'arguments'           => array(),
        'sleep'               => 0,
        'maxExecutionTime'    => 0,
        'memoryLimit'         => 0,
        'relativeMemoryLimit' => 0.2,
        'gracefulSigterm'     => TRUE,
        'freeSystemMemory'    => 104857600 // 100MB
    );

    /**
     * Sets the option $name to $value.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if the property $name is not defined
     * @throws ezcBaseValueException
     *         if $value is not correct for the property $name
     * @param string $name
     * @param mixed $value
     * @ignore
     */
    public function __set( $name, $value )
    {
        if( !array_key_exists( $name, $this->properties ) )
        {
            throw new ezcBasePropertyNotFoundException( $name );
        }

        switch( $name )
        {
            case 'sleep':
            case 'maxExecutionTime':
            case 'memoryLimit':
            case 'freeSystemMemory':
                if( !is_int( $value ) )
                {
                    throw new ezcBaseValueException( $name, $value, 'integer' );
                }
            break;

            case 'relativeMemoryLimit':
                if( !is_float( $value ) )
                {
                    throw new ezcBaseValueException( $name, $value, 'float' );
                }
            break;
            case 'gracefulSigterm':
                if( !is_bool( $value ) )
                {
                    throw new ezcBaseValueException( $name, $value, 'bool' );
                }
            break;
            case 'callback':
                if( !is_callable( $value ) )
                {
                    throw new ezcBaseValueException( $name, $value, 'callback' );
                }
            break;
            case 'arguments':
                if( !is_array( $value ) )
                {
                    throw new ezcBaseValueException( $name, $value, 'array' );
                }
            break;
        }

        $this->properties[$name] = $value;
    }
}
