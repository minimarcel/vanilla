<?php

import('vanilla.util.StringMap');

/**
 * A VLocale, this one replace the old locale.
 * The Locale class has been deprecated cause it can be already declared with the intl extension.
 */
class VLocale
{
    private $language;
    private $country;
    private $properties;

//  ---------------------------------------------------------->

    public function __construct($language, $country)
    {
	$this->language = strtolower($language);
	$this->country	= strtoupper($country);

	$this->properties = new StringMap();
    }

//  ---------------------------------------------------------->

    public function setProperties($code, $array)
    {
	$this->properties->put($code, $array);
    }

    public function getProperties($code)
    {
	return $this->properties->get($code);
    }

    public function getPropertiesKeys()
    {
	return $this->properties->keys();
    }

//  ---------------------------------------------------------->

    public function getLanguageCode()
    {
	return $this->language;
    }

    public function getCountryCode()
    {
	return $this->country;
    }

    /**
     * Retourne l'objet language si il existe dans le languageSource
     * définit au niveau de l'objet Localisation
     */
    public function getLanguage()
    {
	return Localisation::getLanguageSource()->getLanguageForCode($this->language);
    }

//  ---------------------------------------------------------->

    public function __toString()
    {
	return $this->language . '_' . $this->country;
    }
}
?>
