<?php

class ymcHtmlFormElementsGroupGeneric implements ymcHtmlFormElementsGroup
{
    /**
     * Elements of the group.
     * 
     * @var array( string => ymcHtmlFormElement )
     */
    protected $elements;

    /**
     * Validation failures 
     * 
     * @var array( ymcHtmlFormFailure )
     */
    protected $failures = array();

    /**
     * Form-unique name.
     * 
     * @var string
     */
    public $name;

    public function __construct( $name, $elements = array() )
    {
        $this->name = $name;
        $this->elements = $elements;
    }

    /**
     * Adds an element to the group.
     * 
     * @param ymcHtmlFormElement $element 
     */
    public function add( ymcHtmlFormElement $element )
    {
        $name = $element->getName();
        if( array_key_exists( $name, $this->elements ) )
        {
            throw new ymcHtmlFormDuplicateNameException( $name );
        }
        $this->elements[$name] = $element;
    }

    /**
     * Adds array of elements to this group.
     * 
     * @param Array $elements 
     */
    public function addElements( Array $elements )
    {
        foreach( $elements as $element ) 
        {
            $this->add( $element );
        }
    }

    /**
     * Initializes elements belonging to this group.
     * 
     * @param ymcHtmlForm            $form 
     * @param ymcHtmlFormInputSource $inputSource 
     * @param Array                  $elements    If given, limits the initialization to the given
     *                               element objects. 
     *
     * @return array( ymcHtmlFormFailure )
     */
    protected function validateElements( ymcHtmlFormInputSource $inputSource, Array $elements = NULL )
    {
        $failures = array();
        if( !$elements )
        {
            $elements = $this->elements;
        }

        foreach( $elements as $element )
        {
            $failures = array_merge( $failures, $element->validate( $inputSource ) );
        }
        return $failures;
    }

    /**
     * Initializes the group and all sub elements.
     * 
     * @param ymcHtmlForm            $form 
     * @param ymcHtmlFormInputSource $inputSource 
     *
     * @return array( ymcHtmlFormFailure )
     */
    public function init( ymcHtmlForm $form )
    {
        $form->registerOnInit( $this );
        foreach( $this->elements as $element )
        {
            $element->init( $form );
        }
    }

    public function validate( ymcHtmlFormInputSource $inputSource )
    {
        return $this->validateElements( $inputSource );
    }

    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Returns the form unique, html compliant name of this element.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns validation failures. May be called only after init().
     * 
     * @return array( ymcHtmlFormFailure )
     */
    public function getFailures()
    {
        return $this->failures;
    }

    /**
     * Whether the user entered data for this element group.
     *
     * The logic, what is called group input can be overridden.
     * 
     * @return boolean
     */
    public function hasData()
    {
        foreach( $this->elements as $element )
        {
            if( $element->hasData() )
            {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function __get( $name )
    {
        switch( $name )
        {
            case 'elements':
                return $this->elements;
        }
        throw new ezcBasePropertyNotFoundException( $name ) ;
    }
}
