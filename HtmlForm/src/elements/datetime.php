<?php

class ymcHtmlFormElementDateTime extends ymcHtmlFormElementBase
{
    protected $type = 'datetime';

    protected function filter( ymcHtmlFormInputSource $inputSource )
    {
        $input = $inputSource->get( $this->name, FILTER_UNSAFE_RAW );

        try
        {
            return new DateTime( $input );
        }
        catch( Exception $e )
        {
            $failureClass = $this->options->filterFailure;
            $this->failures[] = new $failureClass( $this );
        }
    }
}
