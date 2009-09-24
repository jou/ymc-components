<?php
// +-----------------------------------------------------------------------+
// | Copyright (c) 2007-2008, Christian Schmidt, Peytz & Co. A/S           |
// | All rights reserved.                                                  |
// |                                                                       |
// | Redistribution and use in source and binary forms, with or without    |
// | modification, are permitted provided that the following conditions    |
// | are met:                                                              |
// |                                                                       |
// | o Redistributions of source code must retain the above copyright      |
// |   notice, this list of conditions and the following disclaimer.       |
// | o Redistributions in binary form must reproduce the above copyright   |
// |   notice, this list of conditions and the following disclaimer in the |
// |   documentation and/or other materials provided with the distribution.|
// | o The names of the authors may not be used to endorse or promote      |
// |   products derived from this software without specific prior written  |
// |   permission.                                                         |
// |                                                                       |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
// |                                                                       |
// +-----------------------------------------------------------------------+
// | Author: Christian Schmidt <schmidt at php dot net>                    |
// +-----------------------------------------------------------------------+

// This code is released under the BSD License - http://www.opensource.org/licenses/bsd-license.php
/**
 *
 * equvialent in PERL: http://cpansearch.perl.org/src/GAAS/URI-1.37
 *
 * @license BSD License
 */
class ymcCrawlerUrl
{
    /**
     * Do strict parsing in resolve() (see RFC 3986, section 5.2.2). Default
     * is true.
     */
    const OPTION_STRICT           = 'strict';

    /**
     * Represent arrays in query using PHP's [] notation. Default is true.
     */
    const OPTION_USE_BRACKETS     = 'use_brackets';

    /**
     * URL-encode query variable keys. Default is true.
     */
    const OPTION_ENCODE_KEYS      = 'encode_keys';

    /**
     * Query variable separators when parsing the query string. Every character
     * is considered a separator. Default is specified by the
     * arg_separator.input php.ini setting (this defaults to "&").
     */
    const OPTION_SEPARATOR_INPUT  = 'input_separator';

    /**
     * Query variable separator used when generating the query string. Default
     * is specified by the arg_separator.output php.ini setting (this defaults
     * to "&").
     */
    const OPTION_SEPARATOR_OUTPUT = 'output_separator';

    /**
     * Default options corresponds to how PHP handles $_GET.
     */
    private $options = array(
        self::OPTION_STRICT           => true,
        self::OPTION_USE_BRACKETS     => true,
        self::OPTION_ENCODE_KEYS      => true,
        self::OPTION_SEPARATOR_INPUT  => 'x&',
        self::OPTION_SEPARATOR_OUTPUT => 'x&',
        );

    private $properties = array(
                'scheme'   => NULL,
                'userinfo' => NULL,
                'host'     => NULL,
                'port'     => NULL,
                'path'     => NULL,
                'pass'     => NULL,
                'query'    => NULL,
                'fragment' => NULL
    );

    /**
     * @param string $url     an absolute or relative URL
     * @param array  $options
     */
    public function __construct($url = null, $options = null)
    {
        $this->setOption(self::OPTION_SEPARATOR_INPUT,
                         ini_get('arg_separator.input'));
        $this->setOption(self::OPTION_SEPARATOR_OUTPUT,
                         ini_get('arg_separator.output'));
        if (is_array($options)) {
            foreach ($options as $optionName => $value) {
                $this->setOption($optionName);
            }
        }

        if( $url )
        {
            $this->parseUrl( $url );
        }
    }

    public function parseUrl( $url )
    {
        if (preg_match('@^([a-z][a-z0-9.+-]*):@i', $url, $reg)) {
            $this->scheme = $reg[1];
            $url = substr($url, strlen($reg[0]));
        }

        if (preg_match('@^//([^/#?]+)@', $url, $reg)) {
            $this->setAuthority($reg[1]);
            $url = substr($url, strlen($reg[0]));
        }

        $i = strcspn($url, '?#');
        $this->path = substr($url, 0, $i);
        $url = substr($url, $i);

        if (preg_match('@^\?([^#]*)@', $url, $reg)) {
            $this->query = $reg[1];
            $url = substr($url, strlen($reg[0]));
        }

        if ($url) {
            $this->fragment = substr($url, 1);
        }
    }

    public function __get( $name )
    {
        switch( $name )
        {
            case 'scheme':
            case 'userinfo':
            case 'host':
            case 'port':
            case 'path':
            case 'pass':
            case 'query':
            case 'fragment':
                return $this->properties[$name];
            case 'user':
                return $this->getUser();
            case 'password':
                return $this->getPassword();
        }
    }

    public function __set( $name, $property )
    {
        switch( $name )
        {
            case 'scheme':
            case 'userinfo':
            case 'host':
            case 'path':
            case 'pass':
            case 'query':
            case 'fragment':
                $this->properties[$name] = $property;
            case 'port':
                $this->properties['port'] = intval( $property );
        }
    }

    /**
     * Returns the user part of the userinfo part (the part preceding the first
     *  ":"), or false if there is no userinfo part.
     *
     * @return  string|bool
     */
    public function getUser()
    {
        return $this->userinfo ? preg_replace('@:.*$@', '', $this->userinfo) : null;
    }

    /**
     * Returns the password part of the userinfo part (the part after the first
     *  ":"), or false if there is no userinfo part (i.e. the URL does not
     * contain "@" in front of the hostname) or the userinfo part does not
     * contain ":".
     *
     * @return  string|bool
     */
    public function getPassword()
    {
        return $this->userinfo ? substr(strstr($this->userinfo, ':'), 1) : null;
    }

    /**
     * Sets the userinfo part. If two arguments are passed, they are combined
     * in the userinfo part as username ":" password.
     *
     * @param string|bool $userinfo userinfo or username
     * @param string|bool $password
     *
     * @return void
     */
    public function setUserinfo( $userinfo, $password = null )
    {
        $this->userinfo = $userinfo;
        if ($password ) {
            $this->userinfo .= ':' . $password;
        }
    }

    /**
     * Returns the authority part, i.e. [ userinfo "@" ] host [ ":" port ], or
     * false if there is no authority none.
     *
     * @return string|bool
     */
    public function getAuthority()
    {
        if (!$this->host) {
            return null;
        }

        $authority = '';

        if ($this->userinfo ) {
            $authority .= $this->userinfo . '@';
        }

        $authority .= $this->host;

        if ($this->port ) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * @param string|false $authority
     *
     * @return void
     */
    public function setAuthority($authority)
    {
        $this->user = null;
        $this->pass = null;
        $this->host = null;
        $this->port = null;
        if (preg_match('@^(([^\@]+)\@)?([^:]+)(:(\d*))?$@', $authority, $reg)) {
            if ($reg[1]) {
                $this->userinfo = $reg[2];
            }

            $this->host = $reg[3];
            if (isset($reg[5])) {
                $this->port = intval($reg[5]);
            }
        }
    }

    /**
     * Returns the query string like an array as the variables would appear in
     * $_GET in a PHP script.
     *
     * @return  array
     */
    public function getQueryVariables()
    {
        $pattern = '/[' .
                   preg_quote($this->getOption(self::OPTION_SEPARATOR_INPUT), '/') .
                   ']/';
        $parts   = preg_split($pattern, $this->query, -1, PREG_SPLIT_NO_EMPTY);
        $return  = array();

        foreach ($parts as $part) {
            if (strpos($part, '=') !== false) {
                list($key, $value) = explode('=', $part, 2);
            } else {
                $key   = $part;
                $value = null;
            }

            if ($this->getOption(self::OPTION_ENCODE_KEYS)) {
                $key = rawurldecode($key);
            }
            $value = rawurldecode($value);

            if ($this->getOption(self::OPTION_USE_BRACKETS) &&
                preg_match('#^(.*)\[([0-9a-z_-]*)\]#i', $key, $matches)) {

                $key = $matches[1];
                $idx = $matches[2];

                // Ensure is an array
                if (empty($return[$key]) || !is_array($return[$key])) {
                    $return[$key] = array();
                }

                // Add data
                if ($idx === '') {
                    $return[$key][] = $value;
                } else {
                    $return[$key][$idx] = $value;
                }
            } elseif (!$this->getOption(self::OPTION_USE_BRACKETS)
                      && !empty($return[$key])
            ) {
                $return[$key]   = (array) $return[$key];
                $return[$key][] = $value;
            } else {
                $return[$key] = $value;
            }
        }

        return $return;
    }

    /**
     * @param array $array (name => value) array
     *
     * @return void
     */
    public function setQueryVariables(array $array)
    {
        if (!$array) {
            $this->query = null;
        } else {
            foreach ($array as $name => $value) {
                if ($this->getOption(self::OPTION_ENCODE_KEYS)) {
                    $name = rawurlencode($name);
                }

                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $parts[] = $this->getOption(self::OPTION_USE_BRACKETS)
                            ? sprintf('%s[%s]=%s', $name, $k, $v)
                            : ($name . '=' . $v);
                    }
                } elseif (!is_null($value)) {
                    $parts[] = $name . '=' . $value;
                } else {
                    $parts[] = $name;
                }
            }
            $this->query = implode($this->getOption(self::OPTION_SEPARATOR_OUTPUT),
                                   $parts);
        }
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return  array
     */
    public function setQueryVariable($name, $value)
    {
        $array = $this->getQueryVariables();
        $array[$name] = $value;
        $this->setQueryVariables($array);
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function unsetQueryVariable($name)
    {
        $array = $this->getQueryVariables();
        unset($array[$name]);
        $this->setQueryVariables($array);
    }

    /**
     * Returns a string representation of this URL.
     *
     * @return  string
     */
    public function getUrl()
    {
        // See RFC 3986, section 5.3
        $url = "";

        if ($this->scheme ) {
            $url .= $this->scheme . ':';
        }

        $authority = $this->getAuthority();
        if ($authority ) {
            $url .= '//' . $authority;
        }
        $url .= $this->path;

        if ($this->query ) {
            $url .= '?' . $this->query;
        }

        if ($this->fragment ) {
            $url .= '#' . $this->fragment;
        }
    
        return $url;
    }

    public function __toString()
    {
        return $this->getUrl();
    }

    /** 
     * Returns a normalized string representation of this URL. This is useful
     * for comparison of URLs.
     *
     * @return  string
     */
    public function getNormalizedURL()
    {
        $url = clone $this;
        $url->normalize();
        return $url->getUrl();
    }

    /** 
     * Normalizes $this.
     */
    public function normalize()
    {
        // See RFC 3886, section 6

        // Schemes are case-insensitive
        if ($this->scheme) {
            $this->scheme = strtolower($this->scheme);
        }

        // Hostnames are case-insensitive
        if ($this->host) {
            $this->host = strtolower($this->host);
        }

        // Remove default port number for known schemes (RFC 3986, section 6.2.3)
        if ($this->port &&
            $this->scheme &&
            $this->port == getservbyname($this->scheme, 'tcp')) {

            $this->port = null;
        }

        // Normalize case of %XX percentage-encodings (RFC 3986, section 6.2.2.1)
        foreach (array('userinfo', 'host', 'path') as $part) {
            if ($this->$part) {
                $this->$part  = preg_replace('/%[0-9a-f]{2}/ie', 'strtoupper("\0")', $this->$part);
            }
        }

        // Path segment normalization (RFC 3986, section 6.2.2.3)
        $this->path = self::removeDotSegments($this->path);

        // Scheme based normalization (RFC 3986, section 6.2.3)
        if ($this->host && !$this->path) {
            $this->path = '/';
        }
    }

    /**
     * Returns whether this instance represents an absolute URL.
     *
     * @return  bool
     */
    public function isAbsolute()
    {
        return (bool) $this->scheme;
    }

    public function isSameHost( self $url )
    {
        if( !$url->isAbsolute() )
        {
            return TRUE;
        }

        return $url->host === $this->host;
    }

    /**
     * Whether the two domains differ only in a subdomain, e.g. 
     * www.ymc.ch is still related to blog.ymc.ch
     * 
     * @param self $url 
     * @param float $depth 
     * @access public
     * @return bool
     */
    public function isSameDomain( self $url, $depth = 2 )
    {
        if( !$url->isAbsolute() )
        {
            return TRUE;
        }

        $host1 = $url->host;
        $host2 = $this->host;
        if( $host1 === $host2 )
        {
            return TRUE;
        }

        $hostParts1 = explode( '.', $host1 );
        $hostParts2 = explode( '.', $host2 );

        for( $i = 0; $i < $depth; ++$i )
        {
            if( array_pop( $hostParts1 ) !== array_pop( $hostParts2 ) )
            {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Returns a new url object representing an absolute URL relative to
     * $this URL.
     *
     * @param self|string $reference relative URL
     *
     * @return self
     */
    public function resolve( $reference )
    {
        if (is_string($reference)) 
        {
            $reference = new self( $reference );
        }

        if( !$this->isAbsolute() ) 
        {
            throw new Exception('Base-URL must be absolute');
        }

        // A non-strict parser may ignore a scheme in the reference if it is
        // identical to the base URI's scheme.
        if (!$this->getOption(self::OPTION_STRICT) && $reference->scheme == $this->scheme) {
            $reference->scheme = null;
        }

        $target = new self;
        if ($reference->scheme ) {
            $target->scheme = $reference->scheme;
            $target->setAuthority($reference->getAuthority());
            $target->path  = self::removeDotSegments($reference->path);
            $target->query = $reference->query;
        } else {
            $authority = $reference->getAuthority();
            if ($authority) {
                $target->setAuthority($authority);
                $target->path  = self::removeDotSegments($reference->path);
                $target->query = $reference->query;
            } else {
                if ($reference->path == '') {
                    $target->path = $this->path;
                    if ($reference->query ) {
                        $target->query = $reference->query;
                    } else {
                        $target->query = $this->query;
                    }
                } else {
                    if (substr($reference->path, 0, 1) == '/') {
                        $target->path = self::removeDotSegments($reference->path);
                    } else {
                        // Merge paths (RFC 3986, section 5.2.3)
                        if ($this->host  && $this->path == '') {
                            $target->path = '/' . $this->path;
                        } else {
                            $i = strrpos($this->path, '/');
                            if ($i !== false) {
                                $target->path = substr($this->path, 0, $i + 1);
                            }
                            $target->path .= $reference->path;
                        }
                        $target->path = self::removeDotSegments($target->path);
                    }
                    $target->query = $reference->query;
                }
                $target->setAuthority($this->getAuthority());
            }
            $target->scheme = $this->scheme;
        }

        $target->fragment = $reference->fragment;

        return $target;
    }

    /**
     * Removes dots as described in RFC 3986, section 5.2.4, e.g.
     * "/foo/../bar/baz" => "/bar/baz"
     *
     * @param string $path a path
     *
     * @return string a path
     */
    private static function removeDotSegments($path)
    {
        $output = '';

        // Make sure not to be trapped in an infinite loop due to a bug in this
        // method
        $j = 0; 
        while ($path && $j++ < 100) {
            // Step A
            if (substr($path, 0, 2) == './') {
                $path = substr($path, 2);
            } elseif (substr($path, 0, 3) == '../') {
                $path = substr($path, 3);

            // Step B
            } elseif (substr($path, 0, 3) == '/./' || $path == '/.') {
                $path = '/' . substr($path, 3);

            // Step C
            } elseif (substr($path, 0, 4) == '/../' || $path == '/..') {
                $path = '/' . substr($path, 4);
                $i = strrpos($output, '/');
                $output = $i === false ? '' : substr($output, 0, $i);

            // Step D
            } elseif ($path == '.' || $path == '..') {
                $path = '';

            // Step E
            } else {
                $i = strpos($path, '/');
                if ($i === 0) {
                    $i = strpos($path, '/', 1);
                }
                if ($i === false) {
                    $i = strlen($path);
                }
                $output .= substr($path, 0, $i);
                $path = substr($path, $i);
            }
        }

        return $output;
    }

    /**
     * Returns a Net_URL2 instance representing the canonical URL of the
     * currently executing PHP script.
     * 
     * @return  string
     */
    public static function getCanonical()
    {
        if (!isset($_SERVER['REQUEST_METHOD'])) {
            // ALERT - no current URL
            throw new Exception('Script was not called through a webserver');
        }

        // Begin with a relative URL
        $url = new self($_SERVER['PHP_SELF']);
        $url->scheme = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        $url->host = $_SERVER['SERVER_NAME'];
        $port = intval($_SERVER['SERVER_PORT']);
        if ($url->scheme == 'http' && $port != 80 ||
            $url->scheme == 'https' && $port != 443) {

            $url->port = $port;
        }
        return $url;
    }

    /**
     * Returns the URL used to retrieve the current request.
     *
     * @return  string
     */
    public static function getRequestedURL()
    {
        return self::getRequested()->getUrl();
    }

    /**
     * Returns a Net_URL2 instance representing the URL used to retrieve the
     * current request.
     *
     * @return  Net_URL2
     */
    public static function getRequested()
    {
        if (!isset($_SERVER['REQUEST_METHOD'])) {
            // ALERT - no current URL
            throw new Exception('Script was not called through a webserver');
        }

        // Begin with a relative URL
        $url = new self($_SERVER['REQUEST_URI']);
        $url->scheme = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        // Set host and possibly port
        $url->setAuthority($_SERVER['HTTP_HOST']);
        return $url;
    }

    /**
     * Sets the specified option.
     *
     * @param string $optionName a self::OPTION_ constant
     * @param mixed  $value      option value  
     *
     * @return void
     * @see  self::OPTION_STRICT
     * @see  self::OPTION_USE_BRACKETS
     * @see  self::OPTION_ENCODE_KEYS
     */
    function setOption($optionName, $value)
    {
        if (!array_key_exists($optionName, $this->options)) {
            return false;
        }
        $this->options[$optionName] = $value;
    }

    /**
     * Returns the value of the specified option.
     *
     * @param string $optionName The name of the option to retrieve
     *
     * @return  mixed
     */
    function getOption($optionName)
    {
        return isset($this->options[$optionName])
            ? $this->options[$optionName] : false;
    }
}
