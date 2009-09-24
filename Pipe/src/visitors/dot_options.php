<?php
/**
 * Options class for ymcPipeDotVisitor.
 *
 * @property string $dpi
 *           dpi attribute of graph ( 0.0 )
 */
class ymcPipeDotVisitorOptions extends ezcBaseOptions
{
    /**
     * Properties.
     *
     * @var array(string=>mixed)
     */
    protected $properties = array(
        'dpi'               => '0.0'
    );

    /**
     * Property write access.
     *
     * @param string $propertyName  Name of the property.
     * @param mixed  $propertyValue The value for the property.
     *
     * @throws ezcBasePropertyNotFoundException
     *         If the the desired property is not found.
     * @ignore
     */
    public function __set( $propertyName, $propertyValue )
    {
        switch ( $propertyName )
        {
            case 'dpi':
                if ( !is_numeric( $propertyValue ) )
                {
                    throw new ezcBaseValueException(
                        $propertyName,
                        $propertyValue,
                        'float'
                    );
                }
                break;
            default:
                throw new ezcBasePropertyNotFoundException( $propertyName );
        }
        $this->properties[$propertyName] = $propertyValue;
    }
}
?>
