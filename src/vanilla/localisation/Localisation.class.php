<?php
import('vanilla.net.HTTP');
import('vanilla.localisation.CurrencySource');
import('vanilla.localisation.LocaleSource');
import('vanilla.localisation.Language');
import('vanilla.localisation.LanguageSource');
import('vanilla.localisation.TranslationSource');
import('vanilla.localisation.simple.SimpleLanguageSource');

/**
 * TODO gérer le timezone 
 */
class Localisation
{
    /**
     * La source de locales.
     */
    private static $localeSource  = null;

    /**
     * La source de monaies.
     */
    private static $currencySource  = null;

    /**
     * La source de langues.
     */
    private static $languageSource  = null;

    /**
     * La source de traductions.
     */
    private static $translationSource = null;

    /**
     * La Locale courrante
     */
    private static $currentLocale = null;
    
    /**
     * La langue courrante
     */
    private static $currentLanguage = null;

    /**
     * Les langues et locales préférées.
     */
    private static $userPreferences = null;

//----------------------------------------------->
// Locale

    /**
     * Return the navigator user preferences
     */
    public static function getUserPreferences()
    {
	if ( empty(self::$userPreferences) )
	{
	    $prefs = explode(',', HTTP::SERVER('HTTP_ACCEPT_LANGUAGE'));
	    if ( empty($prefs) )
	    {
		return null;
	    }
	
	    self::$userPreferences = Array();
	    foreach ( $prefs as $p )
	    {
		self::$userPreferences[] = self::parseUserPreference($p);
	    }
	}

	return self::$userPreferences;
    }

    private static function parseUserPreference($p)
    {
	$pref = new Object();

	$pe = explode(';', $p);
	$l  = explode('-', $pe[0]);

	$pref->language	= $l[0];
	if ( sizeof($l) > 1 )
	{
	    $pref->country  = strtoupper($l[1]);
	    $pref->locale   = $pref->language. "_" . $pref->country;
	}
	else
	{
	    $pref->country  = null;
	    $pref->locale   = $pref->language;
	}

	if ( sizeof($pe) > 1 && substr($pe[1], 0, 2) == 'q=' )
	{
	    $pref->q = floatval(substr($pe[1], 2));
	}
	else
	{
	    $pref->q = 1;
	}

	return $pref;
    }

//----------------------------------------------->
// Locale && Languages

    public static function getLocaleSource()
    {
	if ( empty(self::$localeSource) )
	{
	    self::$localeSource = new LocaleSource();
	}

	return self::$localeSource;
    }

    public static function getLanguageSource()
    {
	if ( empty(self::$languageSource) )
	{
	    self::$languageSource = new SimpleLanguageSource();
	}

	return self::$languageSource;
    }

    public static function getDefaultLocale()
    {
	return self::getLocaleForLanguage( self::getDefaultLanguage() );
    }

    public static function getUserLocale()
    {
	$locale = null;

	foreach ( self::getUserPreferences() as $pref )
	{
	    $language = self::getLanguageForCode($pref->language);
	    if ( empty($language) )
	    {
		continue;
	    }

	    $locale = self::getLocaleSource()->getLocaleForCode($pref->locale);
	    if ( !empty($locale) )
	    {
		break;
	    }

	    $locale = self::getLocaleSource()->getLocaleForCode($pref->language);
	    if ( !empty($locale) )
	    {
		break;
	    }

	    $locale = null;
	}

	return $locale;
    }


    public static function getCurrentLocale()
    {
	if ( empty(self::$currentLocale) )
	{
	    self::setDefaultLocale();
	}

	return self::$currentLocale;
    }

    public static function getDefaultLanguage()
    {
	return self::getLanguageSource()->getDefaultLanguage();
    }

    public static function getLanguages()
    {
	return self::getLanguageSource()->getLanguages();
    }

    public static function getCurrentLanguage()
    {
	if ( empty(self::$currentLanguage) )
	{
	    self::setDefaultLocale();
	}

	return self::$currentLanguage;
    }

    /**
     * Spécifie une locale
     * Retroune true si la locale a été mise correctement, false sinon aucune locale n'a été trouvée.
     */
    public static function setLocaleForCode($code)
    {
	$code = trim($code);
	$locale = null;

	$e = explode('_', $code);
	if ( sizeof($e) == 2 )
	{
	    /*
	       Locale complète
	    */

	    $locale = self::getLocaleForFullCode($code);
	}
	else if ( strtoupper($e[0]) == $code )
	{
	    /*
	       Pays seul ?
	    */

	    $locale = self::getLocaleForCountryCode($e[0]);
	}
	else
	{
	    /*
	       Langue seule ?
	    */

	    $locale = self::getLocaleForLanguageCode($e[0]);
	}

	/*
	   As-t-on trouvé une locale ?
	*/

	if ( empty($locale) )
	{
	    return false;
	}

	self::setLocale($locale);
	return true;
    }

    /**
     * 
     */
    public static function getLocaleForCode($code)
    {
	return self::getLocaleSource()->getLocaleForCode(trim($code));
    }

    public static function getLanguageForCode($code)
    {
	return self::getLanguageSource()->getLanguageForCode($code);
    }

//----------------------------------------------->

    private static function setDefaultLocale()
    {
	$locale = self::getUserLocale();
	if ( empty($locale) )
	{
	    $locale = self::getDefaultLocale();
	    if ( empty($locale) )
	    {
		throw new Exception("Can't find a default locale for the default language");
	    }
	}

	self::setLocale( $locale );	
    }

    // retourne une locale dont le language est supporté pour le language donné
    private static function getLocaleForLanguageCode($language)
    {
	return self::getLocaleForLanguage( self::getLanguageForCode($language) );
    }

    // retourne une locale dont le language est supporté pour le language donné
    private static function getLocaleForLanguage(/*Language*/ $language)
    {
	if ( empty($language) )
	{
	    return null;
	}

	$code	= $language->getCode();
	$locale = null;

	/*
	   On essaye de trouver une locale qui est dans les prefs du user
	*/
	foreach ( self::getUserPreferences() as $pref )
	{
	    if ( $pref->language === $code )
	    {
		$locale = self::getLocaleSource()->getLocaleForCode($pref->locale);
		if ( !empty($locale) )
		{
		    break;
		}
	    }
	}

	if ( empty($locale) )
	{
	    /*
	       Pas de locale pour ce user
	       on prends le pays par défaut
	    */
	    $locale = self::getLocaleSource()->getLocaleForCode($code); 
	}

	return $locale;
    }

    // retourne une locale dont le language est supporté pour le pays donné
    private static function getLocaleForCountryCode($country)
    {
	$country = trim($country);
	if ( empty($country) )
	{
	    return null;
	}
	
	$locale = null;

	/*
	   On essaye de trouver une langue qui est dans les prefs du user
	*/
	foreach ( self::getUserPreferences() as $pref )
	{
	    if ( $pref->country === $country )
	    {
		$language = self::getLanguageForCode($pref->language);
		if ( empty($language) )
		{
		    continue;
		}

		$locale	= self::getLocaleSource()->getLocaleForCode($pref->locale);
		if ( !empty($locale) )
		{
		    break;
		}
	    }
	}

	if ( empty($locale) )
	{
	    /*
	       Pas de langue préférencielle pour le user
	       on cherche donc la locale pour ce pays
	    */

	    $locale	= self::getLocaleSource()->getLocaleForCode($country);
	    $language	= $locale->getLanguage();

	    if ( empty($language) )
	    {
		$locale = null;
	    }
	}

	return $locale;
    }

    /**
     * Codes entiers (ex : fr_FR)
     */
    private static function getLocaleForFullCode($code)
    {
	$code = trim($code);

	$locale = self::getLocaleSource()->getLocaleForCode($code);
	if ( empty($locale) )
	{
	    return null;    
	}

	$language = $locale->getLanguage();
	if ( empty($language) )
	{
	    return null;
	}

	return $locale;
    }

    /**
     * Définie la locale, on doit être sûr qu'une langue existe pour cette locale
     */
    private static function setLocale(VLocale $locale)
    {
	self::$currentLocale	= $locale;
	self::$currentLanguage	= $locale->getLanguage();
    }

//----------------------------------------------->
// Translations

    /**
     * Retourne la translation source surchargée, si aucune n'est spécifée, retourne la translation source par défaut.
     */
    public static function getTranslationSource()
    {
	if ( empty(self::$translationSource) )
	{
	    self::$translationSource = new TranslationSource();
	}

	return self::$translationSource;
    }

    /**
     * Retourne la traduction pour la chaîne donnée
     */
    public static function getTranslation($string, $domain='default')
    {
	return self::getTranslationSource()->getTranslation($string, self::getCurrentLocale(), $domain);
    }

    /**
     * Retourne la traduction plurielle pour la chaîne donnée
     */
    public static function getPluralTranslation($single, $plural, $nb, $domain='default')
    {
	return self::getTranslationSource()->getPluralTranslation($single, $plural, $nb, self::getCurrentLocale(), $domain);
    }

    /**
     * Traduit la chaîne donnée dans le domaine donné et remplace le pattern %i% (i étant la position du pattern commençant à 1 dans les paramètres) par les paramètres donnés
     */
    public static function translate($string, $params, $domain='default')
    {
	if ( empty($domain) )
	{
	    $domain = 'default';
	}

	$translation = self::getTranslation($string, $domain);
	$translation = self::replacePatterns($translation, $params);

	return $translation;
    }

    /**
     * Traduit les chaînes simple et plurielle données (suivant le nombre donné) dans le domaine donné et remplace le pattern %i% (i étant la position du pattern commençant à 1 dans les paramètres) par les paramètres donnés
     */
    public static function translatePlural($single, $plural, $nb, $params, $domain='default')
    {
	if ( empty($domain) )
	{
	    $domain = 'default';
	}

	$translation = self::getPluralTranslation($single, $plural, $nb, $domain);
	$translation = self::replacePatterns($translation, $params);

	return $translation;
    }

    private static function replacePatterns($translation, $params)
    {
	if ( !empty($params) )
	{
	    for( $i = 0 ; $i < sizeof($params) ; )
	    {
		$param = $params[$i++];
		$translation = str_replace("%$i%", $param, $translation);
	    }
	}

	return $translation;
    }

//----------------------------------------------->
// Sources

    public static function loadLanguageSource($sourceClass)
    {
	$classname		= import($sourceClass);
	self::$languageSource	= new $classname();

	return self::$languageSource;
    }

    public static function loadTranslationSource($sourceClass)
    {
	$classname		    = import($sourceClass);
	self::$translationSource    = new $classname();

	return self::$translationSource;
    }

    public static function loadLocaleSource($sourceClass)
    {
	$classname		    = import($sourceClass);
	self::$localeSource	    = new $classname();

	return self::$localeSource;
    }

//----------------------------------------------->
// Currencies

    public static function getCurrencySource()
    {
	if ( empty(self::$currencySource) )
	{
	    self::$currencySource = new CurrencySource();
	}

	return self::$currencySource;
    }

    /**
     * Retourne la currency suivant le code
     */
    public static function getCurrencyForCode($code)
    {
	return self::getCurrencySource()->getCurrencyForCode($code);
    }

    /**
     * Retourne la currency suivant le code
     */
    public static function getCurrencyForCurrentLocale()
    {
	return Currency::getInstanceForLocale( self::getCurrentLocale() );
    }

    /**
     * Retourne la currency suivant le code
     */
    public static function getCurrencyForLocale(VLocale $locale)
    {
	return Currency::getInstanceForLocale( $locale );
    }
}

// FIXME définition des locales et timezones
// Alors attention à toujours avoir le même timezone pour récupérer des dates qui servent en base de donnée par ex
// le mieux c'est de pouvoir convertir une date, dans le timezone en fonction de la locale d'affichage.
// si on se met à utiliser des dates en fonction des timezone pour faire des calculs de dates, 
// alors on risque d'avoir de nombreuses erreurs de calcul. Il vaut mieux donc fixer une timezone pour l'application, 
// et convertir pour l'affichage.
date_default_timezone_set("Europe/Paris");
setlocale(LC_ALL, 'fr_FR.UTF8');
?>
