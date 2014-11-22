<?php

import('vanilla.runtime.URLRewritingRule');

/**
 * 
 */
class LanguageRewritingRule implements URLRewritingRule
{
    const PREFIX_NONE_TYPE	= 0;
    const PREFIX_COUNTRY_TYPE	= 1;
    const PREFIX_LANGUAGE_TYPE	= 2;

//----------------------------------------------->

    private $translate	    = false;
    private $prefixType	    = self::PREFIX_NONE_TYPE;

//----------------------------------------------->

    /**
     *
     */
    public function init(URLRewritingConfig $config)
    {
	// TODO
    }

    /**
     *
     */
    public function execute(URLRW $url)
    {
	$file = $url->getFileWithoutContext();
    
	/**
	 * On définit la locale de l'url
	 */

	$langParam  = $url->getParameter('lang');
	$locale	    = null;

	if ( !empty($langParam) )
	{
	    $locale = Localisation::getLocaleSource()->getLocaleForCode($langParam); 
	    $url->removeParameter('lang');
	}

	if ( empty($locale) )
	{
	    $locale = Localisation::getCurrentLocale();
	}

	/**
	 * Traduction de l'url
	 */
	
	if ( $this->translate )
	{
	    $file = Localisation::getTranslationSource()->getTranslation($file, $locale);
	}

	/**
	 * On préfixe la page
	 */
	switch($this->prefixType)
	{
	    case self::PREFIX_COUNTRY_TYPE : 
	    {
		$p = $locale->getCountryCode();
		$file = "/$p$file";
		break;
	    }

	    case self::PREFIX_LANGUAGE_TYPE : 
	    {
		$p = $locale->getLanguageCode();
		$file = "/$p$file";
		break;
	    }
	}

	$url->navigate(www($file), false);

	return $url;
    }

//----------------------------------------------->

    public function setTranslate($translate)
    {
	$this->translate = ($translate=== true);
    }

    public function isTranslate()
    {
	return $this->translate;
    }

    public function setPrefixType($prefixType)
    {
	$this->prefixType = $prefixType;
    }

    public function getPrefixType()
    {
	return $this->prefixType;
    }
}
?>
