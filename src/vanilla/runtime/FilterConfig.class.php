<?php

import('vanilla.runtime.FilterManager');

/**
 * 
 */
class FilterConfig
{
    private $matchPatterns;
    private $exceptPatterns;
    private $name;
    private $parameters;

//----------------------------------------------->

    /**
     * Crée une nouvelle config de filtre
     *
     * @param	name		le nom du filtre
     * @param	matchPatterns	un ArrayList de patterns.
     *				Si l'un de ces patterns correspond avec la ressource courrante, alors le filtre sera executé.
     * @param	exceptPatterns	un ArrayList de patterns.
     *				Si l'un de ces patterns correspond avec la ressource courrante, alors le filtre sera PAS executé.
     * @param	parameters	une StringMap de paramètre d'initialisation passés au filtre.
     */
    public function __construct($name, $matchPatterns, $exceptPatterns, $parameters)
    {
	$this->matchPatterns	= $matchPatterns;
	$this->exceptPatterns	= $exceptPatterns;
	$this->parameters	= $parameters;
	$this->name		= $name;
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

    public function matches($resourceName)
    {
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
	    if ( FilterManager::patternMatch($pattern, $resourceName) )
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
	    if ( FilterManager::patternMatch($pattern, $resourceName) )
	    {
		return true;
	    }
	}

	return false;
    }
}
?>
