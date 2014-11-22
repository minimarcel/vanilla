<?php

import('vanilla.runtime.Filter');

/**
 * 
 */
class LanguageFilter implements Filter
{
    private $saveInSession;
    private $sessionKey;
    
//----------------------------------------------->

    /**
     *
     */
    public function init(FilterConfig $config)
    {
	$this->saveInSession	= $config->getParameter("saveInSession") === "true";
	$this->sessionKey	= $config->getParameter("sessionKey");

	if ( empty($this->sessionKey) )
	{
	    $this->sessionKey = "vanilla.localisation.currentLocale";
	}
    }

    /**
     *
     */
    public function execute($resourceName)
    {
	/*
	   On récupère la locale dans les paramètres
	*/

	$code = HTTP::REQUEST('lang');
	if ( !empty($code) )
	{
	    Localisation::setLocaleForCode($code);
	}
	else if ( $this->saveInSession )
	{
	    $code = HttpSession::get($this->sessionKey);
	    if ( !empty($code) )
	    {
		Localisation::setLocaleForCode($code);
	    }
	}

	if ( $this->saveInSession )
	{
	    $locale = Localisation::getCurrentLocale();
	    HttpSession::put($this->sessionKey, $locale->__toString());
	}

	return true;
    }

//----------------------------------------------->

    public function setSaveInSession($saveInSession)
    {
	$this->saveInSession = ($saveInSession === true);
    }

    public function isSaveInSession()
    {
	return $this->saveInSession;
    }

    public function setSessionKey($sessionKey)
    {
	if ( !empty($sessionKey) )
	{
	    $this->sessionKey = $sessionKey;
	}
    }

    public function getSessionKey()
    {
	return $this->sessionKey;
    }
}
?>
