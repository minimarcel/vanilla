<?php

import('vanilla.runtime.FilterManager');

/**
 * 
 */
class URLRewritingConfig
{
    private $matchPatterns;
    private $exceptPatterns;
    private $name;
    private $parameters;

//----------------------------------------------->

    /**
     * Crée une nouvelle config de règle de réécriture d'URL
     *
     * @param	name		le nom de la règle
     * @param	matchPatterns	un ArrayList de patterns.
     *				Si l'un de ces patterns correspond avec l'url donnée, alors la règle sera executée.
     * @param	exceptPatterns	un ArrayList de patterns.
     *				Si l'un de ces patterns correspond avec l'url donnée, alors la règle sera PAS executée.
     * @param	parameters	une StringMap de paramètre d'initialisation passés à la règle.
     */
    public function __construct($name, $matchPatterns, $exceptPatterns, $parameters)
    {
	$this->matchPatterns	= $matchPatterns;
	$this->exceptPatterns	= $exceptPatterns;
	$this->parameters	= $parameters;
	$this->name		= $name;

	// TODO precompute patterns !
    }

//----------------------------------------------->

    public function getName()
    {
	return $this->name;
    }

    public function getParameter($name)
    {
	if ( isset($this->parameters) )
	{
	    return $this->parameters->get($name);
	}

	return null;
    }

    /**
     * Retourne un array de noms
     */
    public function getParameterNames()
    {
	if ( isset($this->parameters) )
	{
	    return $this->parameters->keys();
	}

	return Array();
    }

    public function matches(URLRW $url)
    {
	$resourceName = $url->getFileWithoutContext();	

	if ( empty($resourceName) )
	{
	    return false;
	}

	if ( !$this->isMatchingResource($resourceName) )
	{
	    return false;
	}

	if ( $this->isExceptedResource($resourceName) )
	{
	    return false;
	}

	return true;
    }

    private function isMatchingResource($resourceName)
    {
	if ( empty($this->matchPatterns) || $this->matchPatterns->isEmpty() )
	{
	    return true;
	}

	foreach ( $this->matchPatterns->elements as $pattern )
	{
	    if ( self::patternMatch($pattern, $resourceName) )
	    {
		return true;
	    }
	}

	return false;
    }

    private function isExceptedResource($resourceName)
    {
	if ( empty($this->exceptPatterns) || $this->exceptPatterns->isEmpty() )
	{
	    return false;
	}

	foreach ( $this->exceptPatterns->elements as $pattern )
	{
	    if ( self::patternMatch($pattern, $resourceName) )
	    {
		return true;
	    }
	}

	return false;
    }

    public static function patternMatch($pattern, $s)
    {
	$pattern = self::toPattern($pattern);
	return (preg_match($pattern, $s) >  0);
    }

    private static function toPattern($s)
    {
        $s = str_replace('/', '\/', $s);
        return "/$s/";
    }
}
?>
