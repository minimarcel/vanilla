<?php

import('vanilla.localisation.Currency');

/**
 * Permet de récupérer une monnaie
 */
class CurrencySource
{
    private $cache;
    private $webCurrenciesDirectory;
    private $vanillaCurrenciesDirectory;

//  ---------------------------------------->

    function __construct()
    {
	$this->cache = new StringMap();

	/*
	   Récupération des répertoires contenant les monnaies
	*/

	$dir = File::fromRelativePath('WEB/currencies');
	if ( $dir->exists() && $dir->isDirectory() )
	{
	    $this->webCurrenciesDirectory = $dir;
	}

	$package = LibraryPackage::$packages['fr.vanilla'];
	if ( empty($package) )
	{
	    throw new Exception("Unable to find the fr.vanilla package");
	}

	$dir = new File($package->getAbsolutePath() . "/currencies");
	if ( !$dir->exists() || !$dir->isDirectory() )
	{
	    throw new Exception("Unable to find the currencies directory :" . $dir);
	}

	$this->vanillaCurrenciesDirectory = $dir;
    }

//  ---------------------------------------->

    /**
     * Renvoie toute les monnaies diponibles.
     */
    public function getAvailableCurrencies()
    {
	/*
	   On liste les locales du répertoire WEB/currencies et vanilla currencies
	*/

	// TODO
    }

    /**
     *
     */
    public function getCurrencyForCode($code)
    {
	/*
	   A-t-on déjà chargé cette monnaie
	*/
	
	if ( $this->cache->contains($code) )
	{
	    return $this->cache->get($code);
	}

	/*
	   On cherche son fichier
	*/

	$f = $this->findCurrencyFileForCode($code);
	if ( empty($f) )
	{
	    return null;
	}

	/*
	   On parse le fichier
	*/

	$currency = $this->parseCurrency($f);
	$this->cache->put($code, $currency);

	return $currency;
    }


    private function parseCurrency(File $file)
    {
	$reader	    = new FileReader($file);
	$currency   = null;

	try
	{
	    $code = trim($reader->readln());
	    $name = trim($reader->readln());
	    $defaultFractionDigits = trim($reader->readln());
	    // FIXME utiliser un symbol par défaut ?

	    $currency = new Currency($code, $name, $defaultFractionDigits);
	}
	catch(Exception $e)
	{
	    if ( isset($reader) )
	    {
		$reader->close();
	    }

	    throw $e;
	}

	if ( isset($reader) )
	{
	    $reader->close();
	}

	return $currency;
    }

    
    private function findCurrencyFileForCode($code)
    {
	/*
	   On teste avec le répertoire locales du site web
	*/

	$f = $this->findCurrencyFileInDirectory($this->webCurrenciesDirectory, $code);
	if ( !empty($f) )
	{
	    return $f;
	}

	return $this->findCurrencyFileInDirectory($this->vanillaCurrenciesDirectory, $code);
    }

    protected function findCurrencyFileInDirectory($dir, $code)
    {
	if ( empty($dir) )
	{
	    return null;
	}

	$f = File::fromChildPath($dir, strtoupper($code));
	if ( !$f->exists() )
	{
	    return null;
	}

	return $f;
    }
}
?>
