<?php

class ymcCurlMultiHandle
{
    protected $requests = array();

    protected $multiHandle;

    protected $curlHandles = array();

    public function addRequest( ymcCurlRequest $request )
    {
        $this->requests[] = $request;
    }

    public function addRequests( Array $requests )
    {
        foreach( $requests as $request )
        {
            $this->addRequest( $request );
        }
    }

    public function createRequests( Array $urls )
    {
        $requests = array();
        foreach( $urls as $url )
        {
            $requests[] = new ymcCurlRequest( $url );
        }
        return $requests;
    }

    protected function initMultiHandle()
    {
        $requests = $this->requests;

        if( empty( $requests ) )
        {
            return false;
        }

        $mh = curl_multi_init();

        foreach( $requests as $key => $request )
        {
            $ch = $request->getHandle();

            $this->curlHandles[$key] = $ch;
            curl_multi_add_handle( $mh, $ch );
        }
        return $mh;
    }

    public function run()
    {
        // if sth. goes wrong with init (no requests), return false.
        if( !($mh = $this->initMultiHandle() ) )
        {
            return false;
        }

        $active = 0;
        do{
            do{
                $mrc = curl_multi_exec($mh, $active);
            } while ( CURLM_CALL_MULTI_PERFORM === $mrc );

            switch( $mrc )
            {
                case CURLM_OK:
                break;

                case CURLM_OUT_OF_MEMORY:
                    die( 'CURL out of memory.' );
                break;

                case CURLM_INTERNAL_ERROR:
                    ezcLog::getInstance()->log( 'CURL_INTERNAL ERROR', ezcLog::FATAL );
                break;
            }

            // Did sth. happen? Did a handle finish?
            $moreMessages = 0;
            do{
                $this->handleMultiMessage( curl_multi_info_read( $mh, $moreMessages ) );
            } while( $moreMessages );
            

            // wait for sth. to do
            if( -1 === curl_multi_select($mh) )
            {
                ezcLog::getInstance()->log( 'curl_multi_select returned -1', ezcLog::FATAL );
                $active = false; // break the loop
            }
            
        } while ( $active );

        return TRUE;
    }

    public function __destruct()
    {
        $mh = $this->multiHandle;
        
        if( is_resource( $mh ) )
        {
            foreach( $this->curlHandles as $ch )
            {
                curl_multi_remove_handle( $mh, $ch );
            }
            curl_multi_close( $mh );
        }
    }

    /**
     * Handles the output of curl_multi_info_read.
     *
     * Currently the only defined message is CURLMSG_DONE.
     *
     * The message is either FALSE for no message or an array:
     * array(3) {
     *   ["msg"]    => int(1) // CURLMSG_DONE
     *   ["result"] => int(0)
     *   ["handle"] => resource(4) of type (curl)
     * }
     *
     * @param mixed $message 
     * @access protected
     * @return void
     */
    protected function handleMultiMessage( $message )
    {
        if( !is_array( $message ) ) return;

        $handle    = $message['handle'];
        $handleKey = array_search( $handle, $this->curlHandles, TRUE );
        $request   = $this->requests[$handleKey];

        $request->onCompletion();
    }

    public function __get( $name )
    {
        switch( $name )
        {
            case 'requests':
                return $this->$name;
        }
        throw new Exception( 'Unknown property '.$name );
    }

}
