<?php

class ymcJobQueueInstance
{
    /**
     * Identifier of the instance that will be returned
     * when you call get() without arguments.
     *
     * @see ezcPersistentSessionInstance::get()
     * @var string
     */
    static private $defaultInstanceIdentifier = null;

    /**
     * Holds the session instances.
     *
     * Example:
     * <code>
     * array( 'server1' => [object],
     *        'server2' => [object] );
     * </code>
     *
     * @var array(string=>ezcPersistentSession)
     */
    static private $instances = array();

    /**
     * Returns the persistent session instance named $identifier.
     *
     * If $identifier is ommited the default persistent session
     * specified by chooseDefault() is returned.
     *
     * @throws ezcPersistentSessionNotFoundException if the specified instance is not found.
     * @param string $identifier
     * @return ezcPersistentSession
     */
    public static function get( $identifier = null )
    {
        if ( $identifier === null && self::$defaultInstanceIdentifier )
        {
            $identifier = self::$defaultInstanceIdentifier;
        }

        if ( !isset( self::$instances[$identifier] ) )
        {
            // The ezcInitPersistentSessionInstance callback should return an
            // ezcPersistentSession object which will then be set as instance.
            $ret = ezcBaseInit::fetchConfig( 'ymcJobQueueInstance', $identifier );
            if ( $ret === null )
            {
                throw new Exception( 'Did not find queue instance '.$identifier );
            }
            else
            {
                self::set( $ret, $identifier );
            }
        }

        return self::$instances[$identifier];
    }

    /**
     * Adds the persistent session $session to the list of known instances.
     *
     * If $identifier is specified the persistent session instance can be
     * retrieved later using the same identifier. If $identifier is ommited
     * the default instance will be set.
     *
     * @param ezcPersistentSession $session
     * @param string $identifier the identifier of the database handler
     * @return void
     */
    public static function set( $instance, $identifier = null )
    {
        if ( $identifier === null )
        {
            $identifier = self::$defaultInstanceIdentifier;
        }

        self::$instances[$identifier] = $instance;
    }

    /**
     * Sets the database $identifier as default database instance.
     *
     * To retrieve the default database instance
     * call get() with no parameters..
     *
     * @see ezcPersistentSessionInstance::get().
     * @param string $identifier
     * @return void
     */
    public static function chooseDefault( $identifier )
    {
        self::$defaultInstanceIdentifier = $identifier;
    }

    /**
     * Resets the default instance holder.
     *
     * @return void
     */
    public static function resetDefault()
    {
        self::$defaultInstanceIdentifier = false;
    }

    /**
     * Resets this object to its initial state.
     *
     * @return void
     */
    public function reset()
    {
        $this->defaultInstanceIdentifier = false;
        $this->instances = array();
    }
}

