<?php

class ymcCurlStoragePoDefinitionManager extends ezcPersistentDefinitionManager
{
    protected $definitions = array();

    public function __construct( $prefix = '' )
    {
        $definitions = array(

            'ymcCurlResponse' => new ezcPersistentObjectDefinition(
                $prefix.'curl_response',
                'ymcCurlResponse',
                array( // column, property, type, converter, databaseType
                    'url'          => new ezcPersistentObjectProperty( 'url', 'url' ),
                    'body'         => new ezcPersistentObjectProperty( 'body', 'body' ),
                    'header'       => new ezcPersistentObjectProperty( 'response_header', 'header' ),
                    'effectiveUrl' => new ezcPersistentObjectProperty( 'effective_url', 'effectiveUrl' ),
                    'contentType'  => new ezcPersistentObjectProperty( 'content_type', 'contentType' ),
                    'httpCode'     => new ezcPersistentObjectProperty( 'http_code', 'httpCode', ezcPersistentObjectProperty::PHP_TYPE_INT ),
                    'filetime'     => new ezcPersistentObjectProperty( 'filetime', 'filetime', ezcPersistentObjectProperty::PHP_TYPE_INT ),
                    'totalTime'    => new ezcPersistentObjectProperty( 'total_time', 'totalTime', ezcPersistentObjectProperty::PHP_TYPE_FLOAT ),
                    'namelookupTime' => new ezcPersistentObjectProperty( 'namelookup_time', 'namelookupTime', ezcPersistentObjectProperty::PHP_TYPE_FLOAT ),
                    'speedDownload' => new ezcPersistentObjectProperty( 'speed_download', 'speedDownload', ezcPersistentObjectProperty::PHP_TYPE_FLOAT ),
                ), array( // relations
                ),
                new ezcPersistentObjectIdProperty( '_id' , '_id', null, new ezcPersistentGeneratorDefinition( 'ezcPersistentNativeGenerator' ) )
            )
        );

        foreach( $definitions as $class => $definition )
        {
            $this->definitions[$class] = $this->setupReversePropertyDefinition( $definition );
        }
    }

    public function fetchDefinition( $class )
    {
        if( array_key_exists( $class, $this->definitions) )
        {
            return $this->definitions[$class];
        }
        throw new Exception( 'Unknown class '.$class );
    }
}

