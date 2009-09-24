<?php

/**
 * Node that runs PHP's preg_match function on an input string and outputs one matching string.
 * 
 * Since preg_match gives us an array with the full match and all subpattern, we must identify,
 * which element of this array should be outputed.
 *
 * In General the last element of $matches is outputed, but you can name a subpattern 'x' to
 * return the part matching this pattern instead. Naming Pattern is done by inserting 'P<name>' at
 * the start of the pattern, e.g.:
 *
 * (foo(?P<x>bar)foo)
 *
 * The subpattern matching 'bar' is named 'x' and will be outputed.
 */
class ymcPipePregMatchNode extends ymcPipeNode
{
    protected $typename = 'PregMatch';

    public function processInput( ymcPipeExecution $execution, $input )
    {
        $result = preg_match( $this->config->regexp, $input, $matches );

        //@todo all kind of error checkings and for emtpy result
        if( array_key_exists( 'x', $matches ) )
        {
            return $matches['x'];
        }
        return array_pop( $matches );
    }

    protected function getConfigurationClass()
    {
        return 'ymcPipePregMatchNodeConfiguration';
    }
}
