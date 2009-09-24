<?php

if( !class_exists( 'ezcBase' ) )
{
    require_once 'ezc/Base/base.php';
    spl_autoload_register( array( 'ezcBase', 'autoload' ) );
}
spl_autoload_register( 'testYmcHtmlFormAutoload' );

function testYmcHtmlFormAutoload( $class )
{
    static $autoloadArray, $basedir;

    if( !$autoloadArray )
    {
        $basedir = realpath( dirname( __FILE__ ).'/../src' );
        $autoloadArray = include $basedir.'/autoload/html_form_autoload.php';
    }

    if( array_key_exists( $class, $autoloadArray ) )
    {
        require $basedir.'/'.$autoloadArray[$class];
        return TRUE;
    }
    return FALSE;
}
