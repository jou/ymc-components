#!/usr/bin/env php
<?php

require_once 'ezc/Base/ezc_bootstrap.php';
ezcBase::addClassRepository( dirname( __FILE__ ).'/..', dirname( __FILE__ ).'/..' );

$input = new ezcConsoleInput();

$helpOption = $input->registerOption( new ezcConsoleOption( 'h', 'help' ) );
$helpOption->isHelpOption = true;

$input->argumentDefinition = new ezcConsoleArguments();
$input->argumentDefinition[0] = new ezcConsoleArgument( "infile" );
$input->argumentDefinition[0]->mandatory = false;
$input->argumentDefinition[0]->default = '-';
$input->argumentDefinition[0]->type = ezcConsoleInput::TYPE_STRING;
$input->argumentDefinition[0]->shorthelp = "pipe definition file";
$input->argumentDefinition[0]->longhelp = "Pipe XML definition file to convert or - to read from STDIN ( not implemented yet )";

try
{
     $input->process();
     main( $input );
}
catch ( /*ezcConsoleOptionException*/ Exception $e )
{
     echo $e->getMessage();
     exit( 1 );
}

function main( ezcConsoleInput $input )
{
    $pipe = getPipe( $input->argumentDefinition["infile"]->value );
    $dotVisitor = new ymcPipeDotVisitor;
    $pipe->accept( $dotVisitor );
    echo $dotVisitor->getDot();
}

function getPipe( $infile )
{
    // get XML String
    if( !is_readable( $infile ) )
    {
        throw new Exception( 'Can not read file '.$infile.'.' );
    }

    $document = new DOMDocument;
    $document->load( $infile );

    return ymcPipeDefinitionStorageXml::loadFromDocument( $document );
}
