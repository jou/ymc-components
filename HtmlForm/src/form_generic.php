<?php

class ymcHtmlFormGeneric implements ymcHtmlForm, ArrayAccess
{
    protected $button;

    protected $group;

    protected $elements = array();

    protected $failures = array();

    public function __construct( ymcHtmlFormElementsGroup $group = NULL )
    {
        $this->group = $group !== NULL
                          ? $group
                          : new ymcHtmlFormElementsGroupGeneric( 'root_group' );
    }

    public function isValid()
    {
        return count( $this->failures ) === 0;
    }

    public function getFailures()
    {
        return $this->failures;
    }

    /**
     * Standard init procedure.
     * 
     * @param ymcHtmlFormInputSource $inputSource 
     * @return array( ymcHtmlFailure )
     */
    public function init()
    {
        $this->group->init( $this );
    }

    public function validate( ymcHtmlFormInputSource $inputSource )
    {
        $failures = $this->group->validate( $inputSource );
        $this->failures = $failures;

        self::markFailedElements( $failures );

        return $failures;
    }

    /**
     * Calls the markFailed method on all elements of all failures.
     * 
     * @param Array $failures 
     */
    public static function markFailedElements( Array $failures )
    {
        foreach( $failures as $failure )
        {
            foreach( $failure->getElements() as $element )
            {
                $element->markFailed();
            }
        }
    }

    public function setButton( ymcHtmlFormElement $button )
    {
        $this->button = $button;
    }

    public function registerOnInit( ymcHtmlFormElement $element )
    {
        $name = $element->getName();
        if( array_key_exists( $name, $this->elements ) )
        {
            throw new ymcHtmlFormDuplicateNameException( $name );
        }
        $this->elements[$name] = $element;
    }

    public function __get( $name )
    {
        switch( $name )
        {
            case 'button':
            case 'failures':
            case 'group':
                return $this->$name;
        }
        throw new ezcBasePropertyNotFoundException( $name ) ;
    }

    public function __set( $name, $property )
    {
        switch( $name )
        {
            case 'group':
                if( !$property instanceof ymcHtmlFormElementsGroup )
                {
                    throw new ezcBaseSettingValueException( 'elements', $property, 'ymcHtmlFormElementsGroup' );
                }
                $this->group = $property;
                return;
        }
        throw new ezcBasePropertyNotFoundException( $name ) ;
    }

    public function offsetSet($offset, $value) {
        throw new Exception;
    }
    public function offsetExists($offset) {
        throw new Exception;
    }
    public function offsetUnset($offset) {
        throw new Exception;
    }
    public function offsetGet($offset) {
        if( array_key_exists( $offset, $this->elements ) )
        {
            return $this->elements[$offset];
        }
        throw new Exception( 'Unknown Element: '.$offset );
    }
}
