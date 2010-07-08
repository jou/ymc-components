<?php

class ymcI18nDateFormatSymbols
{
    private $locale;

    private $instances = array();

    private $properties = array();

    public function __construct( $locale )
    {
        $this->locale = $locale;    
        $this->initMonths();
        $this->initDays();
    }

    private function initMonths()
    {
        $properties = array( 
            'monthsShort' => array(),
            'monthsLong'  => array()
        );

        $formatter = new IntlDateFormatter( $this->locale, IntlDateFormatter::FULL,
               IntlDateFormatter::FULL, 'GMT', IntlDateFormatter::GREGORIAN, 'LLL@LLLL' );

        $date = new DateTime( '1970-01-05', new DateTimeZone( 'GMT' ) );
        for( $i = 0; $i < 12; ++$i )
        {
            $names = $formatter->format( ( int )$date->format( 'U' ) );
            $date->modify( 'next month' );
            $splittedNames = explode( '@', $names );

            foreach( $properties as &$property )
            {
                $property[] = trim( array_shift( $splittedNames ), '. ' );
            }
        }
        $this->properties = array_merge( $this->properties, $properties );
    }

    private function initDays()
    {
        $properties = array( 
            'daysChar'   => array(),
            'daysMedium' => array(),
            'daysLong'   => array()
        );
        $daysShort = array();

        $formatter = new IntlDateFormatter( $this->locale, IntlDateFormatter::FULL,
               IntlDateFormatter::FULL, 'GMT', IntlDateFormatter::GREGORIAN, 'eeeee@eee@eeee' );

        $date = new DateTime( '1970-01-05', new DateTimeZone( 'GMT' ) );

        for( $i = 0; $i < 7; ++$i )
        {
            $names = $formatter->format( ( int )$date->format( 'U' ) );
            $date->modify( 'next day' );
            $splittedNames = explode( '@', $names );

            foreach( $properties as $key => &$property )
            {
                $property[] = trim( array_shift( $splittedNames ), '. ' );
            }
        }

        foreach( $properties['daysMedium'] as $name )
        {
            $daysShort[] = mb_substr( $name, 0, 2, 'UTF-8' );
        }
        $properties['daysShort'] = $daysShort;

        $this->properties = array_merge( $this->properties, $properties );
    }

    public function __get( $name )
    {
        if( !array_key_exists( $name, $this->properties ) )
        {
            throw new Exception( 'Unknown property '.$name );
        }
        return $this->properties[$name];
    }

    public function getInstance( $locale )
    {
        if( !array_key_exists( $locale, self::$instances ) )
        {
            $instance = new self( $locale );
            self::$instances[$locale] = $instance;
        }
        return self::$instances[$locale];
    }
}
