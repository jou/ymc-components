<?php

/**
 * Abstract element class to ease the creation of elements.
 * 
 * @author     Thomas Koch <thomas.koch@ymc.ch> 
 */
abstract class ymcHtmlFormElementBase implements ymcHtmlFormElement
{
    /**
     * form unique, html compliant name of the element
     * 
     * @var string
     */
    protected $name;

    /**
     * user submitted value 
     * 
     * @var mixed
     */
    protected $value;

    /**
     * possible Values for radio buttons, selectboxes, etc
     * 
     * @var array
     */
    protected $values;

    /**
     * html input type of this element.
     * 
     * @var string
     */
    protected $type;

    /**
     * Element options 
     * 
     * @var ymcHtmlElementOptions
     */
    protected $options;

    /**
     * Validation failures 
     * 
     * @var array( ymcHtmlFormFailure )
     */
    protected $failures = array();

    /**
     * Whether this element should be shown as failed 
     * 
     * @var boolean
     */
    protected $failed = FALSE;

    /**
     * The unfiltered, unsafe, raw, user submitted value 
     * 
     * @var mixed
     */
    protected $unsafeRawValue;

    /**
     * Map of possible values for $this->type. 
     *
     * @var array
     */
    protected static $validHtmlTypes = array( 
        'text',
        'password',
        'radio',
        'checkbox',
        'file',
        'hidden'
    );

    /**
     * Constructor.
     * 
     * @param string                      $name    form unique, html compliant
     * @param array/ymcHtmlElementOptions $options 
     */
    public function __construct( $name, $options = array() )
    {
        $this->name = $name;

        if( is_array( $options ) || is_null( $options ) )
        {
            $options = new ymcHtmlFormElementOptions( $options );
        }
        elseif( !$options instanceof ymcHtmlFormElementOptions )
        {
            throw new Exception( 'options' );
        }
        $this->options = $options;
    }

    /**
     * Register element in $form and eventually fetch input from $inputSource.
     * 
     * @param ymcHtmlForm            $form 
     * @param ymcHtmlFormInputSource $inputSource 
     *
     * @return array( ymcHtmlFormFailure )
     */
    public function init( ymcHtmlForm $form )
    {
        $form->registerOnInit( $this );
    }

    public function validate( ymcHtmlFormInputSource $inputSource )
    {
        $this->value = $this->filter( $inputSource );
        return $this->failures;
    }

    /**
     * Called by init() to do the filtering and validation.
     * 
     * @param ymcHtmlFormInputSource $inputSource 
     *
     * @return mixed The filtered value
     */
    protected function filter( ymcHtmlFormInputSource $inputSource )
    {
        $options      = $this->options;
        $emptyFailure = $options->emptyFailure;

        $value = $inputSource->get( $this->name, 
                                    $options->filter,
                                    $options->filterOptions );

        $this->unsafeRawValue = $inputSource->getUnsafeRaw( $this->name );
        
        $valid = FALSE !== $value;

        if( !$valid )
        {
            $this->failures[] = new $options->filterFailure( $this );
        }
        elseif( $emptyFailure && empty( $value ) )
        {
            $this->failures[] = new $emptyFailure( $this );
        }
        return $value;
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
     * Whether the user entered data for this element.
     * 
     * @return boolean
     */
    public function hasData()
    {
        return ( bool )$this->unsafeRawValue;
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
     * Called by ymcHtmlForm to mark this element as failed.
     *
     */
    public function markFailed()
    {
        $this->failed = TRUE;
    }

    public function __get( $name )
    {
        switch( $name )
        {
            case 'name':
            case 'failed':
            case 'type':
            case 'value':
            case 'values':
                return $this->$name;
        }
        throw new ezcBasePropertyNotFoundException( $name ) ;
    }

    public function __set( $name, $property )
    {
        switch( $name )
        {
            case 'value':
            case 'values':
            case 'name':
                $this->$name = $property;
                return;
            case 'htmlType':
                if( !in_array( $property, self::$validHtmlTypes ) )
                {
                    throw new Exception( $property.' is not a valid HTML type.' );
                }
                $this->htmlType = $property;
                return;
        }
        throw new ezcBasePropertyNotFoundException( $name ) ;
    }
}
