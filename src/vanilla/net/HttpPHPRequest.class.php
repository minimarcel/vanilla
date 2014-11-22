<?php

import('vanilla.net.WebURL');
import('vanilla.io.File');
import('vanilla.util.Date');

class HttpPHPRequest
{
    // URI interne utilisée par les scripts
    // généralement modifiées avant (ou durant ??) la phase de filtrage par un routeur pour redéfinir l'uri a utiliser en interne
    private $uri;

//  ------------------------------------>

    public function getPHPPath()
    {
	return HTTP::SERVER('PATH');
    }

    public function getServerSignature()
    {
	return HTTP::SERVER('SERVER_SIGNATURE');
    }

    public function getServerSoftware()
    {
	return HTTP::SERVER('SERVER_SOFTWARE');
    }

    public function getServerName()
    {
	return HTTP::SERVER('SERVER_NAME');
    }

    // deprecated
    public function getServerAddresse()
    {
	warning("HttpPHPRequest::getServerAddresse is deprecated, use getServerAddress instead");
	return self::getServerAddress();
    }

    public function getServerAddress()
    {
	return HTTP::SERVER('SERVER_ADDR');
    }

    public function getServerPort()
    {
	return HTTP::SERVER('SERVER_PORT');
    }

    public function getServerProtocol()
    {
	return HTTP::SERVER('SERVER_PROTOCOL');
    }

    public function getServerURI()
    {
	$protocol   = $this->isHTTPS() ? "https" : "http";
	$port	    = $this->getServerPort();
	$host	    = $this->getHost();

	return HTTP::constructServerURI($host, $protocol, $port);
    }

    public function isHTTPS()
    {
	return strpos($this->getServerProtocol(), "HTTPS") === 0;
    }

//-------------------------------------------------------------------------->

    public function getRemoteAddresse()
    {
	return HTTP::SERVER('REMOTE_ADDR');
    }

    public function getRemotePort()
    {
	return HTTP::SERVER('REMOTE_PORT');
    }

    public function getGatewayInterface()
    {
	return HTTP::SERVER('GATEWAY_INTERFACE');
    }

    public function getHost()
    {
	return HTTP::SERVER('HTTP_HOST');
    }

//-------------------------------------------------------------------------->

    public function getScriptFileName()
    {
	return HTTP::SERVER('SCRIPT_FILENAME');
    }

    /**
     * Retourne le fichier script sous la forme d'un objet vanilla.io.File
     */
    public function getScriptFile()
    {
	return File::fromAbsolutePath( $this->getScriptFileName() );
    }

    public function getRequestMethod()
    {
	return HTTP::SERVER('REQUEST_METHOD');
    }

    public function getQueryString()
    {
	return HTTP::SERVER('QUERY_STRING');
    }

    /**
     * Retourne la requête initiale transmise au server web
     * On doit utiliser dans la plus part des cas la méthode "getRequestURI" qui renvoie l'uri interne à utiliser, 
     * uri modifiée par les routeurs via la méthode setRequestURI
     *
     * C'est cette méthode qui doit être surcharger en cas d'utilisation d'une autre classe request
     */
    public function getInitialRequestURI()
    {
	return HTTP::SERVER('REQUEST_URI');
    }

    public function getRequestURI()
    {
	if ( empty($this->uri) )
	{
		$this->uri = $this->getInitialRequestURI();
		
	    // on l'initialise en enlevant le WWW_URL 
	    if ( startsWith($this->uri, WWW_URL) )
	    {
		$this->uri = substr($this->uri, strlen(WWW_URL));
	    }

	    if ( !startsWith($this->uri, '/') )
	    {
		$this->uri = '/' . $this->uri;
	    }
	}

	return $this->uri;
    }

    public function setRequestURI($uri)
    {
	if ( !startsWith($uri, '/') )
	{
	    $uri = '/' . $uri;
	}

	$this->uri = $uri;
    }

    public function getRequestWebURL()
    {
	return WebURL::Create($this->getRequestURI());
    }

    public function getScriptName()
    {
	// FIXME faire de même pour les autre "ORIG_*", et d'ailleurs d'où ça vient ? config apache ?
	$scriptName = HTTP::SERVER("ORIG_SCRIPT_NAME");
	if ( empty($scriptName) )
	{
	    $scriptName = HTTP::SERVER('SCRIPT_NAME');
	}

	return $scriptName;
    }

    /**
     * Retourne le nom du script sous la forme d'un objet vanilla.net.WebURL
     */
    public function getScriptWebURL($appendGetParameters=true)
    {
	$url = WebURL::Create( $this->getScriptName() );
	if ( $appendGetParameters )
	{
	    $this->appendGetParameters($url);
	}

	return $url;
    }

    public function appendGetParameters(WebURL $url)
    {
	foreach ( $_GET as $name => $value )
	{
	    $name = str_replace('_', '.', $name);
	    $url->addParameter($name, $value);
	}
    }

    public function getRequestTime()
    {
	return intval(HTTP::SERVER('REQUEST_TIME'));
    }

    /**
     * Retourne la request time sous la forme d'un objet vanilla.util.Date
     */
    public function getRequestDate()
    {
	return new Date( $this->getRequestTime() );
    }
}
?>
