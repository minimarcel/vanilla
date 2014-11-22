<?php

import('vanilla.util.StringMap');

/**
 * 
 */
class Currency
{
    /* Le code ISO 4217 */
    private $code;
    private $name;
    private $fractionDigits;

//  ---------------------------------------------------------->

    public function __construct($code, $name, $fractionDigits=-1)
    {
	$this->code		= strtoupper($code);
	$this->name		= strtolower($name);
	$this->fractionDigits	= intval($fractionDigits);
    }

//  ---------------------------------------------------------->

    public function getCode()
    {
	return $this->code;
    }

    public function getName()
    {
	return $this->name;
    }

    public function getDefaultFractionDigits()
    {
	return $this->fractionDigits;
    }

    /**
     * Retourne le symbol pour la locale courrante
     */
    public function getSymbol()
    {
    	return $this->getSymbolForLocale(Localisation::getCurrentLocale());
    }

    public function getSymbolForLocale(VLocale $locale)
    {
	$properties = $locale->getProperties('Currencies');		
	if ( !empty($properties) )
	{
	    foreach ( $properties as $property )
	    {
		$s = explode(' ', $property); 
		if ( $s[0] == $this->code )
		{
		    return $s[1];
		}
	    }
	}

	// on retourne le code par défaut
	return $this->code;
    }

//  ---------------------------------------------------------->

    public static function getInstanceForLocale(VLocale $locale)
    {
	$properties = $locale->getProperties('Currencies');		
	if ( !empty($properties) )
	{
	    $s = explode(' ', $properties[0]);
	    return Localisation::getCurrencyForCode($s[0]);
	}

	throw new Exception("No currency found for locale : $locale");
    }

//  ---------------------------------------------------------->

    public function __toString()
    {
	return $this->code;
    }
}
?>
