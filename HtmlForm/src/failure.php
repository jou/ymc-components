<?php

/**
 * Class representing a validation failure.
 * 
 * @author     ymc-toko <thomas.koch@ymc.ch> 
 */
class ymcHtmlFormFailure
{
    /**
     * elements involved in the failure
     * 
     * @var array( ymcHtmlFormElement )
     * @access protected
     */
    protected $elements;

    /**
     * failure identifier 
     * 
     * @var string
     */
    protected $identifier;

    /**
     * Constructs a failure.
     * 
     * @param array/ymcHtmlFormElement $elements involved in the failure
     * @param string                   $identifier 
     */
    public function __construct( $elements, $identifier )
    {
        if( !is_array( $elements ) )
        {
            $elements = array( $elements );
        }
        foreach( $elements as $element )
        {
            if( !$element instanceof ymcHtmlFormElement )
            {
                throw new ymcHtmlFormException( 'Need instanceof ymcHtmlFormElement.' );
            }
        }
        $this->elements = $elements;
        $this->identifier  = $identifier;
    }

    /**
     * Returns the failure identifier
     * 
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns elements involved in the validation failure.
     * 
     * @return array( ymcHtmlFormElement )
     */
    public function getElements()
    {
        return $this->elements;
    }
}
