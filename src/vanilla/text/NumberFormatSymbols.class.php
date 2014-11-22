<?php

import('vanilla.localisation.Localisation');

/**
 *
 */
class NumberFormatSymbols
{
    private $locale = null;

    // symbols
    private $decimalSeparator;
    private $groupingSeparator;
    private $percent;
    private $minusSign;
    private $exponential;
    private $permill;
    private $infinity;

    // currency 
    private $currency;
    private $currencySymbol;

//  ------------------------------------->

    public function __construct($locale=null)
    {
	if ( empty($locale) )
	{
	    $locale = Localisation::getCurrentLocale();
	}

	$this->init($locale);
    }

    private function init(VLocale $locale)
    {
	$this->locale = $locale;

	$symbols = $locale->getProperties('NumberElements');
	if ( empty($symbols) )
	{
	    throw new Exception('Can\'t find locale currency symbols');
	}

	// Attention, ne pas prendre que le premier caractères, effectivement, 
	// en UTF8 les caractères spéciaux sont sur deux caractères
	$this->decimalSeparator	    = $symbols[0];
	$this->groupingSeparator    = $symbols[1];
	$this->percent		    = $symbols[2];
	$this->minusSign	    = $symbols[3];
	$this->exponential	    = $symbols[4];
	$this->permill		    = $symbols[5];
	$this->infinity	    	    = $symbols[6];

	$this->currency		    = Localisation::getCurrencyForLocale($locale);
	$this->currencySymbol	    = $this->currency->getSymbolForLocale($locale);
    }
    
//  ------------------------------------->

    public function getDecimalSeparator()
    {
	return $this->decimalSeparator;
    }

    public function getGroupingSeparator()
    {
	return $this->groupingSeparator;
    }

    public function getPercent()
    {
	return $this->percent;
    }

    public function getMinusSign()
    {
	return $this->minusSign;
    }

    public function getExponential()
    {
	return $this->exponential;
    }

    public function getPermill()
    {
	return $this->permill;
    }

    public function getInfinity()
    {
	return $this->infinity;
    }

    public function getCurrencySymbol()
    {
	return $this->currencySymbol;
    }

    public function getCurrency()
    {
	return $this->currency;
    }

//  ------------------------------------->

    public function setDecimalSeparator($decimalSeparator)
    {
	$this->decimalSeparator = $decimalSeparator[0];
    }

    public function setGroupingSeparator($groupingSeparator)
    {
	$this->groupingSeparator = $groupingSeparator[0];
    }

    public function setPercent($percent)
    {
	$this->percent = $percent[0];
    }

    public function setMinusSign($minusSign)
    {
	$this->minusSign = $minusSign[0];
    }

    public function setExponential($exponential)
    {
	$this->exponential = $exponential[0];
    }

    public function setPermill($permill)
    {
	$this->permill = $permill[0];
    }

    public function setInfinity($infinity)
    {
	$this->infinity = $infinity[0];
    }

    public function setCurrencySymbol($currencySymbol)
    {
	$this->currencySymbol = $currencySymbol;
    }

    public function setCurrency(Currency $currency)
    {
	if ( $currency != $this->currency )
	{
	    $this->currency		= $currency;
	    $this->currencySymbol	= $currency->getSymbolForLocale($this->locale);
	}
    }

//  ------------------------------------->

    public function duplicate()
    {
	$symbols = new NumberFormatSymbols($this->locale);

	$symbols->decimalSeparator = $this->decimalSeparator;
	$symbols->groupingSeparator = $this->groupingSeparator;
	$symbols->percent = $this->percent;
	$symbols->minusSign = $this->minusSign;
	$symbols->exponential = $this->exponential;
	$symbols->permill = $this->permill;
	$symbols->infinity = $this->infinity;
	$symbols->currency = $this->currency;
	$symbols->currencySymbol = $this->currencySymbol;

	return $symbols;
    }
}
