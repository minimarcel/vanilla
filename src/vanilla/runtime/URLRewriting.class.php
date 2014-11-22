<?php

import('vanilla.util.ArrayList');
import('vanilla.runtime.URLRewritingRule');
import('vanilla.runtime.URLRewritingConfig');
import('vanilla.runtime.URLRW');

/**
 * 
 */
class URLRewriting
{
    private static $instance;
    private $rules;

//----------------------------------------------->

    private function __construct()
    {
	$this->rules = new ArrayList();
    }

//----------------------------------------------->

    public static function getInstance()
    {
	if ( empty(self::$instance) )
	{
	    self::$instance = new URLRewriting();
	}

	return self::$instance;
    }

//----------------------------------------------->

    public function add(URLRewritingConfig $config, URLRewritingRule $rule)
    {
	$o = new Object();
	$o->config = $config;
	$o->rule = $rule;

	$this->rules->add($o);
	$rule->init($config);
    }

    public function execute(WebURL $url)
    {
	if ( $this->rules->isEmpty() )
	{
	    return $url;
	}

	/*
	   On execute les règles sur cette url
	*/

	$url = new URLRW($url);
	foreach ( $this->rules->elements as $o )
	{
	    if ( $o->config->matches($url) )
	    {
		if ( $o->rule->execute($url) === false )
		{
		    return;
		}
	    }
	}

	return $url;
    }

//----------------------------------------------->

    /**
     * Ajoute une nouvelle règle de réécriture d'URL à la fin de la liste des règles.
     *
     * @param	name		le nom de la règle
     * @param	className	la classe du de la règle à loader
     * @param	matchPatterns	un ArrayList de patterns.
     *				Si l'un de ces patterns correspond avec l'URL donnée, alors la règle sera executée.
     * @param	exceptPatterns	un ArrayList de patterns.
     *				Si l'un de ces patterns correspond avec l'URL donnée, alors la règle ne sera PAS executée.
     * @param	parameters	une StringMap de paramètre d'initialisation passés à la règle.
     */
    public static function addRule($name, $class, $matchPatterns=null, $exceptPatterns=null, $parameters=null)
    {
	$config = new URLRewritingConfig($name, $matchPatterns, $exceptPatterns, $parameters);

	$classname = import($class);
	$rule	= new $classname();

	self::getInstance()->add($config, $rule);
	return $rule;
    }

//----------------------------------------------->

    public static function executeRulesOn(WebURL $url)
    {
	return self::getInstance()->execute($url);
    }
}
?>
