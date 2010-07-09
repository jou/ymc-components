<?php

class ymcI18nDateFormatSymbols
{
    private $localeArray;
    private $localeString;

    private static $instances = array();

    private $properties = array();

    private static $dateFieldDelimiter = array( 
    
    );

    public function __construct( $locale )
    {
        $this->localeArray = Locale::parseLocale( $locale );
        $this->localeString = Locale::composeLocale( $this->localeArray );

        $this->initMonths();
        $this->initDays();
        $this->initPattern();
    }

    private function initMonths()
    {
        $properties = array( 
            'monthsShort' => array(),
            'monthsLong'  => array()
        );

        $formatter = new IntlDateFormatter( $this->localeString, IntlDateFormatter::FULL,
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

        $formatter = new IntlDateFormatter( $this->localeString, IntlDateFormatter::FULL,
               IntlDateFormatter::FULL, 'GMT', IntlDateFormatter::GREGORIAN, 'eeeee@eee@eeee' );

        // start date must be sunday so that sunday will be the first element in the array
        $date = new DateTime( '1970-01-04', new DateTimeZone( 'GMT' ) );

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

    private function initPattern()
    {
        $formatter = new IntlDateFormatter( $this->localeString, IntlDateFormatter::SHORT,
               IntlDateFormatter::NONE);
        $pattern = $formatter->getPattern();

        $properties = array( 
            'dateFieldDelimiter' => NULL,
            'MDY_dayPosition' => NULL,
            'MDY_monthPosition' => NULL,
            'MDY_yearPosition' => NULL
        );

        $position = 0;
        for( $i=0; $i<strlen( $pattern ); ++$i )
        {
            switch( substr( $pattern, $i, 1 ) )
            {
                case 'y':
                case 'Y':
                case 'u':
                    if( NULL === $properties['MDY_yearPosition'] )
                    {
                        $properties['MDY_yearPosition'] = ++$position;
                    }
                break;

                case 'M':
                case 'L':
                    if( NULL === $properties['MDY_monthPosition'] )
                    {
                        $properties['MDY_monthPosition'] = ++$position;
                    }
                break;

                case 'd':
                    if( NULL === $properties['MDY_dayPosition'] )
                    {
                        $properties['MDY_dayPosition'] = ++$position;
                    }
                break;

                case ' ':
                    // ignore
                break;

                default:
                    if( NULL === $properties['dateFieldDelimiter'] )
                    {
                        $properties['dateFieldDelimiter'] = substr( $pattern, $i, 1 );
                    }
                break;
            }
        }
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

    public static function getInstance( $locale )
    {
        if( !array_key_exists( $locale, self::$instances ) )
        {
            $instance = new self( $locale );
            self::$instances[$locale] = $instance;
        }
        return self::$instances[$locale];
    }
}
