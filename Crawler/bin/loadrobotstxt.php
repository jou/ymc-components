#!/usr/bin/env php
<?

require_once 'ezc/Base/ezc_bootstrap.php';

$urls = explode( "\n", stream_get_contents( STDIN ) );
shuffle( $urls );

$destinationDir = dirname( __FILE__ ).'/../tests/data_robots_txt/';

foreach( $urls as $url )
{
    $filename = $destinationDir.urlToFilename( $url );
    if( file_exists( $filename ) )
    {
        continue;
    }

    echo $url,"\n";
    $txt = loadRobotsTxt( $url );
    if( $txt )
    {
        file_put_contents( $filename, $txt );
    }
}

function loadRobotsTxt( $url )
{
    $txt = @file_get_contents( 'http://'.$url.'/robots.txt' );
    if( FALSE !== stripos( $txt, '<html' ) )
    {
        return NULL;
    }
    return $txt;
}

function urlToFilename( $url )
{
    $from = './:?#';
    $to   = '_____';
    return strtr( $url, $from, $to );
}
