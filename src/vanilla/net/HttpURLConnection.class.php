<?php

import('java.util.ArrayList');

abstract class HttpURLConnection
{
	private static final $methods = Array("GET", "POST", "HEAD", "OPTIONS", "PUT", "DELETE", "TRACE");

//  ------------------------------------------------------------->

	protected $method = "GET";
	protected $responseCode = -1;
	protected $responseMessage = null;
	protected $responseContent = null;
	protected $header = null;
	protected $executed = false;

//  ------------------------------------------------------------->

	/**
	 * Retourne l'url appellée
	 */
	public abstract function getURL();

	/**
	 * Retourne la taille du header
	 */
	public abstract function getHeaderSize();

	/**
	 * Executes the connection,
	 */
	public abstract function execute(/*array || map || string*/$parameters);

//  ------------------------------------------------------------->

	public function setMethodRequest($method)
	{
		if ( $executed )
		{
			throw new Exception("Can't set method request after the request has been executed");
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

	public function getResponseCode()
	{
		if ( !$executed )
		{
			throw new Exception("The request has not been executed");
		}

		throw new Exception("Not implented yet");
	}

	public function getResponseMessage()
	{
		if ( !$executed )
		{
			throw new Exception("The request has not been executed");
		}

		throw new Exception("Not implented yet");
	}

	public function getResponseContent()
	{
		if ( !$executed )
		{
			throw new Exception("The request has not been executed");
		}

		throw new Exception("Not implented yet");
	}

	public function getHeaderFields()
	{
		if ( $this->header == null )
		{
			throw new Exception("No header");
		}

		return $this->header;
	}

	public function getContentType()
	{
		return $this->getHeader()->get("Content-Type");
	}

//  ------------------------------------------------------------->

	/**
	 * TODO
	 * Méthode appellée par les classes filles pour parser la réponse http
	 * et donc en extraire les header, le code, le message et le content
	 */
	protected function parseResponse($s)
	{}

//  ------------------------------------------------------------->

	public static function openConnection($url)
	{
		// TODO ouvrir une connexion sur l'URL donnée
		// avec par défaut sur CURL
	}
}
?>
