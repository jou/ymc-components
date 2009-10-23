<?php

/**
 * Converter to store a list (array) in a database column.
 *
 * constraints:
 * 
 * - array( 0 => '' ) can not be saved and you'll get back an empty array on read.
 *
 * - you may have problems with several users concurrently editing the same list, e.g.
 *   modifications by user1 are overwritten by user2
 *
 * - The list elements obviously may not contain the separator character
 *
 * - Array keys are not saved
 * 
 */
class ymcEzcPersistentObjectSeparatedListConverter implements ezcPersistentPropertyConverter
{
    private $separator;

    public function __construct( $separator = ',' )
    {
        $this->separator = $separator;
    }

    public function fromDatabase( $databaseValue )
    {
        if ( !is_string( $databaseValue ) )
        {
            throw new ezcBaseValueException( 'databaseValue', $databaseValue, 'String' );
        }
        if( '' === $databaseValue )
        {
            return array();
        }

        return explode( $this->separator, $databaseValue );
    }

    public function toDatabase( $propertyValue )
    {
        if ( !is_array( $propertyValue ) )
        {
            throw new ezcBaseValueException( 'propertyValue', $propertyValue, 'Array' );
        }

        return implode( $this->separator, $propertyValue );
    }

    /**
     * Method for de-serialization after var_export().
     *
     * This methid must be implemented to allow proper de-serialization of
     * converter objects, when they are exported using {@link var_export()}.
     * 
     * @param array $state 
     * @return memoBaseArrayConverter
     */
    public static function __set_state( array $state )
    {
        //TODO
        throw new Exception;
    }
}
