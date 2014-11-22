<?php

import('vanilla.net.WebHeaderCollection');

abstract class HttpWebRequest
{
	private static $methods = Array("GET", "POST", "HEAD", "OPTIONS", "PUT", "DELETE", "TRACE");

//  ------------------------------------------------------------->

	protected $url = null;
	protected $method = "GET";
	protected $headers = null;
	protected $followLocation = true;
	protected $sslVerification = true;
	protected $connectTimeout = 0; // valeur en seconde (0 veut dire infini)
	protected $timeout = 0; // valeur en seconde (0 veut dire infini)

	protected $authLoginPassword=null;

//  ------------------------------------------------------------->

	protected function __construct($url)
	{
		$this->headers = new WebHeaderCollection();
		$this->url = $url;
	}

//  ------------------------------------------------------------->

	public function getMethod()
	{
		return $this->method;
	}

	public function setMethod($method)
	{
		if ( $this->isConnected() )
		{
			throw new Exception("Can't set method request if the connection is opened");
		}

		$method = strtoupper($method);

		$list = new ArrayList();
		$list->setArray(self::$methods);
		if ( !$list->contains($method) )
		{
			throw new Exception("Invalid method : $method");
		}

		$this->method = $method;
	}

	/**
	 * Follow location header
	 */
	public function setFollowLocation($followLocation)
	{
		$this->followLocation = ($followLocation == true);
	}

	public function isFollowLocation()
	{
		return $this->followLocation;
	}

	/**
	 * Vérifie le certificat ssl, et le host
	 * Est activé par défaut
	 */
	public function setSSLVerification($sslVerification)
	{
		$this->sslVerification = ($sslVerification == true);
	}

	public function isSSLVerification()
	{
		return $this->sslVerification;
	}

	public function setAuthentificationLogin($login, $password)
	{
		$this->authLoginPassword = "$login:$password";
	}

	public function getURL()
	{
		return $this->url;
	}

	public function setConnectTimeout($timeout)
	{
		$this->connectTimeout = abs(intval($timeout));
	}

	public function getConnectTimeout()
	{
		return $this->connectTimeout;
	}

	public function setTimeout($timeout)
	{
		$this->timeout = abs(intval($timeout));
	}

	public function getTimeout()
	{
		return $this->timeout;
	}

//  ------------------------------------------------------------->
//  Header methods

	public function getHeaders()
	{
		return $this->headers;
	}

//  ------------------------------------------------------------->

	/**
	 * Définit la méthode à POST, récpère la RequestWriter et écrit les données à poster
	 */
	public function postData($data)
	{
		$this->setMethod("POST");
		$writer = $this->getRequestWriter();
		$writer->write($data);
		$writer->close();
	}

//  ------------------------------------------------------------->

	/**
	 *
	 */
	public abstract function isConnected();

	/**
	 * Retourne le writer pour écrire les données postées
	 */
	public abstract function getRequestWriter();

	/**
	 *
	 */
	public abstract function getResponse();

//  ------------------------------------------------------------->

	/**
	 * TODO utiliser une autre manière de procéder
	 */
	public static function createFromURLString($url)
	{
		if ( function_exists("curl_init") )
		{
			import('vanilla.net.curl.CURLHttpWebRequest');
			$request = new CURLHttpWebRequest($url);
			return $request;
		}

		throw new Exception("No handler found for this url : $url");
	}
}
?>
