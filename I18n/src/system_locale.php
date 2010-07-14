<?php

class ymcI18nSystemLocale
{
    private static $systemLocales; 

    public $systemLocale;

    public function __construct( $locale )
    {
        $this->systemLocale = self::getBestFit( $locale );
    }

    public static function getSystemLocales()
    {
        if( NULL === self::$systemLocales )
        {
            exec( 'locale -a', $locales );
            self::$systemLocales = $locales;
        }
        return self::$systemLocales;
    }

    public static function getBestFit( $locale )
    {
        $primary = Locale::getPrimaryLanguage( $locale );
        foreach( self::getSystemLocales() as $systemLocale )
        {
            if( $primary === Locale::getPrimaryLanguage( $systemLocale ) )
            {
                return $systemLocale;
            }
        }
    }

    public function setLocale()
    {
        $setLocaleResult = setlocale( LC_MESSAGES, $this->systemLocale );
        if( !$setLocaleResult )
        {
            throw new Exception( 'Could not set locale to '.$this->systemLocale );
        }
    }
}
