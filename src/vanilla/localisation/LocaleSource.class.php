<?php

import('vanilla.localisation.VLocale');
import('vanilla.localisation.parser.LocaleParser');
import('vanilla.localisation.parser.LocaleConstructor');

/**
 * Permet de récupérer une locale
 */
class LocaleSource
{
    private $cache;
    private $parser;
    private $webLocalesDirectory;
    private $vanillaLocalesDirectory;

//  ---------------------------------------->

    function __construct()
    {
	$this->cache = new StringMap();

	/*
	   Récupération des répertoires contenant les locales
	*/

	$dir = File::fromRelativePath('WEB/locales');
	if ( $dir->exists() && $dir->isDirectory() )
	{
	    $this->webLocalesDirectory = $dir;
	}

	$package = LibraryPackage::$packages['fr.vanilla'];
	if ( empty($package) )
	{
	    throw new Exception("Unable to find the fr.vanilla package");
	}

	$dir = new File($package->getAbsolutePath() . "/locales");
	if ( !$dir->exists() || !$dir->isDirectory() )
	{
	    throw new Exception("Unable to find the locales directory :" . $dir);
	}

	$this->vanillaLocalesDirectory = $dir;
	$this->parser = new LocaleParser();
    }

//  ---------------------------------------->

    /**
     * Renvoie toute les locales diponibles.
     * FIXME rajotuer un paramètre non obligatoire précisant les locales ayant la liste de propriétés données
     */
    public function getAvailableLocales()
    {
	/*
	   On liste les locales du répertoire WEB/locales
	*/

	// TODO
    }

    /**
     *
     */
    public function getLocaleForCode($code)
    {
	$code = trim($code);

	/*
	   A-t-on déjà chargé cette locale
	*/
	
	if ( $this->cache->contains($code) )
	{
	    return $this->cache->get($code);
	}

	/*
	   Pour éviter les boucles infinies
	   on met la valeur à null dans le cache
	*/

	$this->cache->put($code, null);

	/*
	   On cherche son fichier
	*/

	$f = $this->findLocaleFileForCode($code);
	if ( empty($f) )
	{
	    return null;
	}

	/*
	   On parse la locale
	*/

	$constructor = new LocaleConstructor();
	$this->parser->parse($f, $constructor);

	$locale = null;
	if ( !$constructor->isRedirect() )
	{
	    /*
		On récupère la locale
	    */

	    $locale = $constructor->getLocale();
	}
	else
	{
	    /*
		On gère la redirection
	    */

	    $locale = $this->getLocaleForCode( $constructor->getRedirect() );
	}
	
	$this->cache->put($code, $locale);
	return $locale;
    }

    
    private function findLocaleFileForCode($code)
    {
	/*
	   On teste avec le répertoire locales du site web
	*/

	$f = $this->findLocaleFileInDirectory($this->webLocalesDirectory, $code);
	if ( !empty($f) )
	{
	    return $f;
	}

	return $this->findLocaleFileInDirectory($this->vanillaLocalesDirectory, $code);
    }

    protected function findLocaleFileInDirectory($dir, $code)
    {
	if ( empty($dir) )
	{
	    return null;
	}

	if ( strtoupper($code) === $code )
	{
	    // pour les systèmes étant case insensitive, on rajoute un _ devant les code de country 
	    $code = "_$code";
	}

	$f = File::fromChildPath($dir, $code);
	if ( !$f->exists() )
	{
	    return null;
	}

	return $f;
    }
}
?>
