<?php

class ymcHtmlFormElementCheckboxArray extends ymcHtmlFormElementBase
{
    protected function filter( ymcHtmlFormInputSource $inputSource )
    {
        $array = $inputSource->get( $this->name, FILTER_UNSAFE_RAW, FILTER_FORCE_ARRAY );

        // Workaround since FILTER_FORCE_ARRAY returns NULL instead of array() for no checkbox
        // set.
        if( NULL === $array )
        {
            $array = array();
        }
        
        if( !is_array( $array ) )
        {
            $failureClass = $this->options->filterFailure;
            $this->failures[] = new $failureClass( $this );
            $array = array();
        }

        $value = array();

        if( is_array( $this->values ) )
        {
            // Filter by predefined value field
            foreach( $this->values as $key )
            {
                $value[$key] = array_key_exists( $key, $array );
            }
        }
        else
        {
            // Return all checked checkboxes
            foreach( array_keys( $array ) as $key )
            {
                $value[$key] = TRUE;
            }
        }

        return $value;
    }
}
