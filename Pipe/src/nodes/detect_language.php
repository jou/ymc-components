<?php

class ymcPipeDetectLanguageNode extends ymcPipeNode
{
    protected $typename = 'detect language';
    
    protected $maxInNodes = 2;
    protected $minInNodes = 2;

    public function processInput( ymcPipeExecution $execution, $input )
    {
        $domDocument = $response = $text = false;

        foreach( $input as $nodeName => $inputData )
        {
            if( $inputData instanceof ymcPipeHtmlDomDocument )
            {
                $domDocument = $inputData;
            }

            if( $inputData instanceof ymcCurlResponse )
            {
                $response = $inputData;
            }
            
            if( gettype( $inputData ) == 'string' )
            {
                $text = $inputData;
            }
        }

        if( !$domDocument )
        {
            throw new ymcPipeNodeWrongInputException( $this->typename, $domDocument, 'ymcPipeHtmlDomDocument' );
        }

        if( !$response )
        {
            throw new ymcPipeNodeWrongInputException( $this->typename, $response, 'ymcCurlResponse' );
        }

        if( $execution->variables['language'] )
        {
            $language = $execution->variables['language'];
        }
        else
        {
            $language = $domDocument->getLanguage( $response );
        }
        
        if( !$language )
        {
            $language = '';
            // detect language out of the givent string $text
            // (!) previouly remove all html, js and css stuff
            // that the don't affect the language
        }
        
        
        return $language;
    }

    protected function getConfigurationClass()
    {
        return NULL;
    }
}
