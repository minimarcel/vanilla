<?php

import('vanilla.net.HttpWebRequest');
import('vanilla.net.curl.CURLHttpWebResponse');
import('vanilla.io.StringWriter');

class CURLHttpWebRequest extends HttpWebRequest
{
	private $resource	= null;
	private $writer	= null;

//  ------------------------------------------------------------->

	public function __construct($url)
	{
		parent::__construct($url);
	}

	public function __destruct()
	{
		if ( !empty($this->resource) )
		{
			curl_close($this->resource);
			$this->resource = null;
		}
	}

//  ------------------------------------------------------------->

	public function isConnected()
	{
		return !empty($this->resource);
	}

	/**
	 * Retourne le writer pour écrire les données postées
	 */
	public function getRequestWriter()
	{
		$this->initResource();
		if ( empty($this->writer) )
		{
			$this->writer = new StringWriter();
		}

		return $this->writer;
	}

	/**
	 *
	 */
	public function getResponse()
	{
		$this->initResource();

		/*
		 * On post les data si nécessaire
	 	 */

		if ( !empty($this->writer) )
		{
			$data = $this->writer->getBuffer();

			$this->writer->close();
			$this->writer = null;

			curl_setopt($this->resource, CURLOPT_POSTFIELDS, $data);
		}

		/*
		 On extrait les headers
		*/

		$headers = Array();
		for ( $it = $this->headers->getLinesIterator() ; $it->hasNext() ; )
		{
			$headers[] = $it->next();
		}

		curl_setopt($this->resource, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($this->resource, CURLOPT_HEADER, 0); // inclut le header dans la réponse ???
		curl_setopt($this->resource, CURLOPT_CUSTOMREQUEST, $this->method);

		// définition du timeout
		if ( $this->connectTimeout > 0 )
		{
			curl_setopt($this->resource, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
		}

		// définition du timeout
		if ( $this->timeout > 0 )
		{
			curl_setopt($this->resource, CURLOPT_TIMEOUT, $this->timeout);
		}

		if ( $this->followLocation )
		{
			if ( ini_get('open_basedir') == '' && !ini_get('safe_mode') )
			{
				curl_setopt($this->resource, CURLOPT_FOLLOWLOCATION, 1);
			}
			else
			{
				// TODO gérer les locations à la mano
				curl_setopt($this->resource, CURLOPT_FOLLOWLOCATION, 0);
			}
		}

		if ( !$this->sslVerification )
		{
			curl_setopt($this->resource, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($this->resource, CURLOPT_SSL_VERIFYHOST, FALSE);
		}

		if ( !empty($this->authLoginPassword) )
		{
			curl_setopt($this->resource, CURLOPT_USERPWD, $this->authLoginPassword);
			curl_setopt($this->resource, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		}

		$reponse = new CURLHttpWebResponse($this, $this->resource);

		curl_close($this->resource);
		$this->resource = null;

		return $reponse;
	}

//  ------------------------------------------------------------->

	private function initResource()
	{
		if ( empty($this->resource) )
		{
			$this->resource = curl_init($this->url);
		}
	}
}
?>
