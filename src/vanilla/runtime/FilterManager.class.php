<?php

import('vanilla.util.ArrayList');
import('vanilla.runtime.Filter');
import('vanilla.runtime.FilterConfig');

/**
 * 
 */
class FilterManager
{
    private static $instance;
    private $filters;
    private $resourceName;

//----------------------------------------------->

    private function __construct()
    {
	$this->filters = new ArrayList();
	$this->resourceName = HTTP::getRequest()->getScriptName();
    }

//----------------------------------------------->

    public static function getInstance()
    {
	if ( empty(self::$instance) )
	{
	    self::$instance = new FilterManager();
	}

	return self::$instance;
    }

//----------------------------------------------->

    public function add(FilterConfig $config, Filter $filter)
    {
	$o = new Object();
	$o->config = $config;
	$o->filter = $filter;

	$this->filters->add($o);
	$filter->init($config);
    }

    public function execute()
    {
	foreach ( $this->filters->elements as $o )
	{
	    if ( $o->config->matches($this->resourceName) )
	    {
		if ( $o->filter->execute($this->resourceName) === false )
		{
		    return;
		}
	    }
	}
    }

    public function setResourceName($resourceName)
    {
	$this->resourceName = $resourceName;	
    }

//----------------------------------------------->

    /**
     * Ajoute un nouveau filtre à la fin de la liste des filtres.
     *
     * @param	name		le nom du filtre
     * @param	className	la classe du filtre a loader.
     * @param	matchPatterns	un ArrayList de patterns.
     *				Si l'un de ces patterns correspond avec la ressource courrante, alors le filtre sera executé.
     * @param	exceptPatterns	un ArrayList de patterns.
     *				Si l'un de ces patterns correspond avec la ressource courrante, alors le filtre sera PAS executé.
     * @param	parameters	une StringMap de paramètre d'initialisation passés au filtre.
     */
    public static function addFilter($name, $class, $matchPatterns=null, $exceptPatterns=null, $parameters=null)
    {
	$config = new FilterConfig($name, $matchPatterns, $exceptPatterns, $parameters);

	$classname = import($class);
	$filter	= new $classname();

	self::getInstance()->add($config, $filter);
	return $filter;
    }

    public static function executeFilters()
    {
	self::getInstance()->execute();
    }

    public static function useRequestURIAsResourceName() 
    {
	self::getInstance()->setResourceName(HTTP::getRequest()->getRequestURI());
    }

    public static function useScriptNameAsResourceName() 
    {
	self::getInstance()->setResourceName(HTTP::getRequest()->getScriptName());
    }

//----------------------------------------------->

    public static function patternMatch($pattern, $s)
    {
	$pattern = self::toPattern($pattern);
	return (preg_match($pattern, $s) >  0);
    }

    private static function toPattern($s)
    {
        // si la chaîne commence par ^ on rajoute le WWW_URL
	if ( $s[0] == '^' )
	{
	    $s = $s[1] == '/' ? substr($s, 2) : substr($s, 1);
	    $s = '^' . WWW_URL . $s;
	}

        $s = str_replace('/', '\/', $s);
        return "/$s/";
    }
}
