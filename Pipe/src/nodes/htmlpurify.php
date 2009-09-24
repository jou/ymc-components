<?php

class ymcPipeHtmlPurifyNode extends ymcPipeNode
{
    protected $typename = 'html purify';

    public function processInput( ymcPipeExecution $execution, $input )
    {
        $text = $input;

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache', 'DefinitionImpl', null);
        $config->set('HTML', 'AllowedElements', array());
        $purifier = new HTMLPurifier($config);

        return html_entity_decode( $purifier->purify( $text ), ENT_QUOTES, 'UTF-8' );
    }

    protected function getConfigurationClass()
    {
        return NULL;
    }
}
