<?php

class ymcPipeResponseToDomNode extends ymcPipeNode
{
    protected $typename = 'curl response to DOM';
    protected $minInNodes = 1;
    protected $maxInNodes = 1;

    /**
     * 
     * @param ymcPipeExecution $execution 
     * @param ymcCurlResponse  $input 
     * @return ymcPipeHtmlDomDocument
     */
    public function processInput( ymcPipeExecution $execution, $input )
    {
        //var_dump( $input );
        if( $input instanceof ymcCurlResponse )
        {
          $dom = ymcPipeHtmlDomDocument::createFromHtml( (string)$input, $input->getCharset() );
          return $dom;
        }
        else
        {
          throw new ymcPipeNodeWrongInputException( $this->typename, $input, 'ymcCurlResponse' );
        }
    }

    protected function getConfigurationClass()
    {
        return NULL;
    }
}
