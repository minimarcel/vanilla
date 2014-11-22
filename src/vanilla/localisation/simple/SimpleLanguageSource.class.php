<?php

import('vanilla.util.StringMap');
import('vanilla.localisation.LanguageSource');
import('vanilla.localisation.simple.SimpleLanguage');

/**
 * 
 */
class SimpleLanguageSource implements LanguageSource
{
    private $defaultLanguage;
    private $languages;

//  ---------------------------------------->

    function __construct()
    {
	$this->languages = new StringMap();

	// par défaut on met le français, mais il peut être surchargé
	$this->putSimpleLanguage('fr', 'Français', true);
    }

//  ---------------------------------------->

    public function getLanguages()
    {
	return $this->languages->elements;
    }

    public function getLanguageForCode($code)
    {
	return $this->languages->get($code);
    }

    public function getDefaultLanguage()
    {
	return $this->defaultLanguage;
    }

    public function putSimpleLanguage($code, $label, $default=false)
    {
	$language = new SimpleLanguage($code, $label);
	$this->putLanguage($language);	

	if ( $default )
	{
	    $this->defaultLanguage = $language;
	}
    }

    public function putLanguage(Language $language)
    {
	$this->languages->put($language->getCode(), $language);
    }
}
?>
