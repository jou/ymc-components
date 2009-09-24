<?php

abstract class ymcPipeNodeConfiguration extends ezcBaseOptions
{
    const TYPE_INTEGER = 'integer';
    const TYPE_STRING  = 'string';
    const TYPE_TEXT    = 'text'; // longer text ( textarea! )
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_FLOAT   = 'float';

    protected $properties;

    public function __construct( Array $properties = array() )
    {
        foreach( $this->getDefinition() as $name => $def )
        {
            $properties[$name] = isset( $properties[$name] )
                                 ? $properties[$name]
                                 : ( isset( $def['default'] )
                                     ? $def['default']
                                     : '' );
        }
        $this->properties = $properties;
    }

    public function __set( $name, $value )
    {
        if ( $this->__isset( $name ) === true )
        {
            $this->properties[$name] = $value;
            return;
        }
        throw new ezcBasePropertyNotFoundException( $name );
    }

    /**
     * Populates the DOMElement with the node configuration.
     *
     * Example:
     *
     * <xyz configuration-class="ymcPipeBasicNodeConfigurationMock">
     *   <property key-type="string" key-name="eins" value-type="string"><![CDATA[zwei]]></property>
     *   <property key-type="integer" key-name="3" value-type="integer">4</property>
     *   <property key-type="string" key-name="fuenf" value-type="integer">6</property>
     *   <property key-type="integer" key-name="7" value-type="string"><![CDATA[acht]]></property>
     *   <property key-type="string" key-name="neun" value-type="boolean">0</property>
     *   <property key-type="string" key-name="zehn" value-type="boolean">1</property>
     *   <property key-type="string" key-name="float" value-type="float">6.78</property>
     *   <property key-type="string" key-name="sub" value-type="array">
     *     <property key-type="string" key-name="hi" value-type="string"><![CDATA[du]]></property>
     *     <property key-type="string" key-name="subi" value-type="array"/>
     *   </property>
     * </xyz>
     * 
     * @param DOMElement $element The element xyz.
     * @return void
     */
    public function serializeToXml( DOMElement $element )
    {
        $element->setAttribute( 'configuration-class', get_class( $this ) );
        $this->serializeArrayToXml( $this->properties, $element );
    }

    protected function serializeArrayToXml( Array $array, DOMElement $element )
    {
        foreach( $array as $name => $value )
        {
            $property = $element->appendChild( $element->ownerDocument->createElement( 'property' ) );
            $property->setAttribute( 'key-type', is_string( $name ) ? 'string' : 'integer' );
            $property->setAttribute( 'key-name', $name );

            switch( $type = gettype( $value ) )
            {
                case 'double':
                    $type = 'float';
                case 'integer':
                    // $type has already been set
                    $property->appendChild( $element->ownerDocument->createTextNode( (string)$value ) );
                    break;
                case 'boolean':
                    // $type has already been set
                    $property->appendChild( $element->ownerDocument->createTextNode( $value ?  '1' : '0' ) );
                    break;
                case 'string':
                    $property->appendChild( $element->ownerDocument->createCDATASection( $value ) );
                    break;

                case 'array':
                    // $type has been set
                    // recursively serialize the array
                    $this->serializeArrayToXml( $value, $property );
                    break;

                default:
                    //@todo
                    throw new Exception( 'Can not serialize type '.getType( $value ).' in configuration setting '.$name );

            }
            $property->setAttribute( 'value-type', $type );

        }
    }

    public static function unserializeFromXml( DOMElement $element )
    {
        $className = $element->getAttribute( 'configuration-class' );
        $properties = self::unserializeXmlToArray( $element );
        return new $className( $properties );
    }
    
    protected static function unserializeXmlToArray( DOMElement $element )
    {
        $array = array();

        foreach( $element->childNodes as $propertyNode )
        {
            if( 'property' !== $propertyNode->nodeName )
            {
                continue;
            }

            $key = $propertyNode->getAttribute( 'key-name' );
            settype( $key, $propertyNode->getAttribute( 'key-type' ) );

            switch( $type = $propertyNode->getAttribute( 'value-type' ) )
            {
                case 'float':
                case 'integer':
                case 'boolean':
                case 'string':
                    $value = $propertyNode->nodeValue;
                    settype( $value, $type );
                    break;

                case 'array':
                    // $type has been set
                    // recursively serialize the array
                    $value = self::unserializeXmlToArray( $propertyNode );
                    break;

                default:
                    //@todo
                    throw new Exception;

            }
            $array[$key] = $value;
        }
        return $array;
    }

    /**
     * Returns an array describing all configuration entries of this node.
     * 
     * @return array()
     */
    abstract public function getDefinition();
}
