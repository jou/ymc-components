<?php

class ymcI18nIso3166
{
    const XMLPATH = '/usr/share/xml/iso-codes/iso_3166.xml';

    private static $countries;

    private $locale;

    private $doNotTranslate = FALSE;

    public function __construct( ymcI18nSystemLocale $locale )
    {
        $this->locale = $locale;
        if( !$locale->systemLocale )
        {
            $this->doNotTranslate = TRUE;
        }
        self::initCountries();
    }

    public function getLocaleCountryName( $countryAlpha2 )
    {
        if( !$this->doNotTranslate )
        {
            $this->locale->setLocale();
        }

        if( !array_key_exists( $countryAlpha2, self::$countries ) )
        {
            throw new Exception( 'Unknown Alpha2 country code '.$countryAlpha2 );
        }
        $country = self::$countries[$countryAlpha2];

        if( !array_key_exists( 'name', $country ) )
        {
            throw new Exception( 'Country has no name '.$countryAlpha2 );
        }

        $translation = $this->translate( $country['name'] );

        return $translation;
    }

    public function getCountryList()
    {
        if( !$this->doNotTranslate )
        {
            $this->locale->setLocale();
        }

        $countryList = array();
        foreach( self::$countries as $alpha2Code => $country )
        {
            if( !array_key_exists( 'name', $country ) )
            {
                continue;
            }
            $countryList[$alpha2Code] = $this->translate( $country['name'] );
        }
        return $countryList;
    }

    private function translate( $countryName )
    {
        if( $this->doNotTranslate )
        {
            return $countryName;
        }
        return dgettext( 'iso_3166', $countryName );
    }

    private function initCountries()
    {
        $reader = new XMLReader();
        $reader->open( self::XMLPATH );

        $countries = array();

        while( $reader->read() )
        {
            if( $reader->name != "iso_3166_entry" )
            {
                continue;
            }
            $country = array();
            while( $reader->moveToNextAttribute() )
            {
                $country[$reader->name] = $reader->value;
            }

            if( array_key_exists( 'alpha_2_code', $country ) )
            {
                $countries[$country['alpha_2_code']] = $country;
            }
        }
        $reader->close();
        self::$countries = $countries;
    }
}
