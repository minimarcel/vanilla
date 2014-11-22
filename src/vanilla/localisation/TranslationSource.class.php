<?php
import('vanilla.localisation.gettext.GetTextCache');
import('vanilla.localisation.VLocale');
import('vanilla.util.StringMap');
import('vanilla.io.File');

/**
 * Permet de récupérer une traduction
 */
class TranslationSource
{
    private $cache;

//  ---------------------------------------->

    function __construct()
    {
	$this->cache = new StringMap();
    }

//  ---------------------------------------->

    public function getPluralTranslation($single, $plural, $nb, VLocale $locale, $domain='default')
    {
	$cacheFile = $this->getCacheFile($locale, $domain);

	if ( !empty($cacheFile) )
	{
	    $s = $cacheFile->getPluralTranslation($single, $plural, $nb);
	    if ( !empty($s) )
	    {
		return $s;
	    }
	}

	return ($nb > 1 ? $plural : $single);
    }

    /**
     * Renvoie une traduction pour la chaîne donnée et la langue donnée.
     * Attention : si cette méthode est suchargée, ne pas oublier de l'appeller
     * afin que les fonctionnalité déjà existantes puissent continuer à bénéficier de ce système de traduction.
     */
    public function getTranslation($string, VLocale $locale, $domain='default')
    {
	$cacheFile = $this->getCacheFile($locale, $domain);

	if ( empty($cacheFile) )
	{
	    return $string;
	}

	$s = $cacheFile->getTranslation($string);
	return (empty($s) ? $string : $s);
    }

    private function getCacheFile(VLocale $locale, $domain)
    {
	$key = "$domain.$locale";

	/*
	   On essaye dans le cache
	*/

	if ( $this->cache->contains($key) )
	{
	    return $this->cache->get($key);
	}

	/*
	   On cherche le fichier de traduction avec la locale donnée
	*/

	$f = $this->findCacheFileForDomainAndLocale($domain, $locale->__toString());
	if ( !empty($f) )
	{
	    $this->cache->put($key, $f);
	    return $f;
	}

	/*
	   On essaye avec la locale par défaut pour cette même langue
	*/

	$defaultLocale = $locale->getLanguageCode();
	if ( $defaultLocale !== $locale )
	{
	    $defaultKey = "$domain.$defaultLocale";

	    /*
	       On retente donc dans le cache
	    */

	    if ( $this->cache->contains($defaultKey) )
	    {
		$f = $this->cache->get($defaultKey);
		$this->cache->put($key, $f);
		return $f;
	    }

	    /*
	       On cherche le fichier de traduction pour cette novelle locale
	    */
	    $f = $this->findCacheFileForDomainAndLocale($domain, $defaultLocale);
	    if ( !empty($f) )
	    {
		$this->cache->put($key, $f);
		$this->cache->put($defaultKey, $f);
		return $f;
	    }
	}

	$this->cache->put($key, null);

	/*
	   On a rien trouvé :(
	   Par défaut on retourne cette même chaîne 
	*/

	return null;
    }

    protected function findCacheFileForDomainAndLocale($domain, $locale)
    {
	$defaultLangDir = File::fromRelativePath('WEB/languages');

	/*
	   Domaine par défaut
	*/

	if ( $domain === 'default' )
	{
	    $f = File::fromChildPath($defaultLangDir, "$locale.mo");
	    if ( !$f->exists() )
	    {
		return null;
	    }

	    return new GetTextCache($f);
	}

	/*
	   Autre domain
	   On essaye dans le répertoire web du site
	*/
	
	$f = File::fromChildPath($defaultLangDir, "$domain.$locale.mo");
	if ( $f->exists() )
	{
	    return new GetTextCache($f);
	}

	/*
	   On essaye dans le répertoire lang du libray
	*/

	if ( !isset(LibraryPackage::$packages[$domain]) )
	{
	    return null;
	}

	$package = LibraryPackage::$packages[$domain];
	if ( empty($package) )
	{
	   return null;
	}

	$f = new File($package->getAbsolutePath() . "/languages/$locale.mo");
	if ( !$f->exists() )
	{
	    return null;
	}

	return new GetTextCache($f);
    }
}
?>
