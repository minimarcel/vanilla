<?php

import('vanilla.net.HttpWebRequest');

abstract class HttpWebResponse
{
    protected $request = null;
    protected $headers = null;

    protected $status = -1;
    protected $message;

//  ------------------------------------------------------------->

    protected function __construct(HttpWebRequest $request)
    {
	$this->headers	= new WebHeaderCollection();
	$this->request	= $request;
    }

//  ------------------------------------------------------------->

    public function getStatus()
    {
	$this->getResponseReader();
	return $this->status;
    }

    public function getMessage()
    {
	$this->getResponseReader();
	return $this->message;
    }

    /**
     * Retourne la request à l'origine
     */
    public function getRequest()
    {
	return $this->request;
    }

    public function getContentString()
    {
	$reader = $this->getResponseReader();
	$s = $reader->readTextToEnd();
	$reader->close();

	return $s;
    }

//  ------------------------------------------------------------->
//  Header methods

    public function getResponseHeader($name)
    {
	return $this->headers->get($name);
    }

    public function getHeaders()
    {
	return $this->headers;
    }

    // TODO get contenttype, renvoie un objet avec le contenttype et le charset

//  ------------------------------------------------------------->

    /**
     * Retourne la réponse reader qui a été positionnée au début du contenu, 
     * sauf si la méthode get content string a été appellé
     */
    public function getResponseReader()
    {
	$reader = $this->getTextReader();

	if ( $this->status == -1 )
	{
	    // FIXME bah avant il me semblait que c'était le header du message http, maintenant il est où ?
	    //for ( $line = null ; $line !== false && $line !== '' ; $line = $reader->readln() )
	    //{
	    //    // on parse cette ligne	 pour contruire le header
	    //}

	    // on parse le header
	    $this->status = 0;
	}

	return $reader;
    }

//  ------------------------------------------------------------->

    /**
     * Retourne le reader positionné au début du header
     */
    protected abstract function getTextReader();

    /**
     * Get the response url
     */
    public abstract function getURL();
}
?>
