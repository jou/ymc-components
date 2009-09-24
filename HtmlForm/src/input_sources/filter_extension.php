<?php

final class ymcHtmlFormInputSourceFilterExtension implements ymcHtmlFormInputSource
{
    private $inputSource;

    private static $validSources = array( INPUT_GET, INPUT_POST, INPUT_COOKIE ); 

    public function __construct( $inputSource = INPUT_POST )
    {
        if (  !in_array( $inputSource, self::$validSources ) )
        {
            throw new ezcInputFormWrongInputSourceException( $this->inputSource );
        }
        $this->inputSource = $inputSource;
    }

    public function has( $name )
    {
        return filter_has_var( $this->inputSource, $name );
    }

    public function get( $name, $filter = FILTER_DEFAULT, $options = NULL )
    {
        $value = filter_input( $this->inputSource, $name, $filter, $options );
        return $value;
    }

    public function getUnsafeRaw( $name )
    {
        return filter_input( $this->inputSource, $name, FILTER_UNSAFE_RAW );
    }

    public function hasData()
    {
        switch( $this->inputSource )
        {
            case INPUT_GET:
                return count( $_GET ) > 0;
            case INPUT_POST:
                return count( $_POST ) > 0;
        }
        throw new Exception;
    }
}
