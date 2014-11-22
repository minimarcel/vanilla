<?php

import('vanilla.localisation.parser.LocaleParserHandler');
import('vanilla.localisation.VLocale');

class LocaleConstructor implements LocaleParserHandler
{
    private $redirect;
    private $locale;

    private $currentCollection;
    private $currentCollectionName;
    
//  ---------------------------------------->

    public function isRedirect()
    {
	return isset($this->redirect);
    }

    public function getRedirect()
    {
	return $this->redirect;
    }

    public function getLocale()
    {
	return $this->locale;
    }

//  ---------------------------------------->

    public function redirectTo($locale)
    {
	$this->redirect = $locale;
    }

    public function comment($line)
    {}

    public function startLocale($code)
    {
	$locale = explode('_', $code);
	$this->locale = new VLocale($locale[0], $locale[1]);
    }

    public function finish()
    {
	$this->flushCollection(); 
    }

    public function startPropertyCollection($name)
    {
	$this->flushCollection();

	$this->currentCollectionName	= $name;
	$this->currentCollection	= Array();
    }

    public function addProperty($value)
    {
	$this->currentCollection[] = $value;
    }

    private function flushCollection()
    {
	if ( empty($this->currentCollection) )
	{
	    return;
	}

	$this->locale->setProperties($this->currentCollectionName, $this->currentCollection);
    }
}
?>
