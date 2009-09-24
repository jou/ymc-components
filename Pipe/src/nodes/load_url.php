<?php

class ymcPipeLoadUrlNode extends ymcPipeNode
{
    CONST MAX_FILESIZE = 1048576; // 1024*1024

    protected $typename = 'load url';

    public function execute( ymcPipeExecution $execution )
    {
        if( !isset( $this->variables['requested'] ) )
        {
            $url = $this->fetchInput();
            $url = array_pop( $url );

            $request = new ymcCurlRequest( $url, array( 'maxSize' => self::MAX_FILESIZE ) );
            try
            {
                $request->run();
            }
            catch( Exception $e )
            {
                throw new Exception( 'Could not load '.$url.'. Curl Exception: '.$e->getMessage());
            }

            if( !$request->wasSuccessful() )
            {
                throw new Exception( 'Could not load '.$url.' HTTP Code '.$response->httpCode );
            }

            $this->setOutput( $request->response );
            $this->activateOutNodes( $execution );
            return TRUE; // DONE.
        }

        // only for legacy
        $response = $execution->api->receiveHttp( $this->variables['url'] );
        if( $response )
        {
            $this->setOutput( $response );
            $this->addResponseIdToExecution( $execution, $response->_id );
            $this->activateOutNodes( $execution );
            return true;
        }
        throw new Exception( 'I should have a response!' );
    }

    protected function addResponseIdToExecution( ymcPipeExecution $execution, $id )
    {
        if( !isset( $execution->variables['curl_responses'] ) )
        {
             $execution->variables['curl_responses'] = array();
        }
        $execution->variables['curl_responses'][] = $id;
    }

    public static function getResponseIdsFromExecution( ymcPipeExecution $execution )
    {
        if( !isset( $execution->variables['curl_responses'] ) )
        {
             return array();
        }
        return $execution->variables['curl_responses'];
    }

    protected function getConfigurationClass()
    {
        return NULL;
    }
}
