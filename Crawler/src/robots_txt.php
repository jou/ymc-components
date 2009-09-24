<?php

/**
 * Class to parse a robots.txt file.
 *
 * @copyright  2009 Young Media Concepts GmbH. All rights reserved.
 * @author     Thomas Koch <thomas.koch@ymc.ch> 
 * 
 * @TODO parse the following keywords:
 * crawl-delay
 * request-rate
 * visit-time
 * host
 *
 * <sitemap_location>/sitemap.asp</sitemap_location>
 *
 */
class ymcCrawlerRobotsTxt
{
    protected $rules = array();

    protected $sitemaps = array();

    protected $knownRules = array( 
            'DISALLOW' => 'Disallow',
            'ALLOW'    => 'Allow'
    );

    public function __construct( $txt = NULL )
    {
        if( $txt )
        {
            $this->parse( $txt );
        }
    }

    /**
     * Parses the given robots txt string.
     * 
     * @param string $txt 
     * @return void
     */
    public function parse( $txt )
    {
        $knownRules = &$this->knownRules;
        $lines = explode( "\n", $txt );
        $userAgent = NULL;

        foreach( $lines as $line )
        {
            $lineElements = explode( ':', $line, 2 );
            if( count( $lineElements ) < 2 ) continue;

            $property = strtoupper( trim( $lineElements[0] ) );
            $value = trim( $lineElements[1] );

            if( !$property || !$value ) continue;

            switch( $property )
            {
                case 'USER-AGENT':
                    $userAgent = $value;
                    $this->addUserAgent( $value );
                break;

                case 'SITEMAP':
                    $this->addSitemap( $value );
                break;
            }
            
            if( !$userAgent ) continue;

            if( array_key_exists( $property, $knownRules ) )
            {
                $this->setRule( $userAgent, $property, $value );
            }
        }
    }

    public function setRule( $userAgent, $rule, $value )
    {
        $this->rules[$userAgent][$rule][] = $value;
    }

    public function addUserAgent( $userAgent )
    {
        if( array_key_exists( $userAgent, $this->rules ) )
        {
            return;
        }
        $rules = array();

        foreach( array_keys( $this->knownRules ) as $rule )
        {
            $rules[$rule] = array();
        }
        $this->rules[$userAgent] = $rules;
    }

    /**
     * Adds a sitemap url to the robots txt.
     * 
     * @todo: Check whether $sitemap is a valid url.
     * @param string $sitemap A valid url
     * @return void
     */
    public function addSitemap( $sitemap )
    {
        $this->sitemaps[] = $sitemap;
    }

    /**
     * Returns, whether a path may be crawled.
     * 
     * @param string $path 
     * @param string $userAgent 
     * @return boolean
     */
    public function mayCrawl( $path, $userAgent = '*' )
    {
        if( '*' !== $userAgent && $this->checkPath( $userAgent, 'ALLOW', $path ) )
        {
            return TRUE;
        }

        if( $this->checkPath( '*', 'ALLOW', $path ) )
        {
            return TRUE;
        }

        if( '*' !== $userAgent && $this->checkPath( $userAgent, 'DISALLOW', $path ) )
        {
            return FALSE;
        }

        if( $this->checkPath( '*', 'DISALLOW', $path ) )
        {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Returns, whether a matching could be found.
     * 
     * @param string $userAgent 
     * @param string $forWhat 
     * @param string $pathToCheck 
     * @return bool
     */
    public function checkPath( $userAgent, $forWhat, $pathToCheck )
    {
        if( !isset( $this->rules[$userAgent] ) || !isset( $this->rules[$userAgent][$forWhat] ) )
        {
            return FALSE;
        }

        $paths = $this->rules[$userAgent][$forWhat];

        foreach( $paths as $path )
        {
            if( 0 === strncasecmp( $pathToCheck, $path, strlen( $path ) ) )
            {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function __toString()
    {
        $txt = '';

        foreach( $this->sitemaps as $sitemap )
        {
            $txt .= "Sitemap: $sitemap\n";
        }

        foreach( $this->rules as $userAgent => $rules )
        {
            $txt .= "User-agent: $userAgent\n";
            foreach( $rules as $rule => $values )
            {
                foreach( $values as $value )
                {
                    $txt .= $this->knownRules[$rule].": $value\n";
                }
            }
        }
        return $txt;
    }
}
