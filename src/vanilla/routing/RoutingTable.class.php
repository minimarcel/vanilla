<?php

import('vanilla.util.ArrayList');
import('vanilla.routing.RoutingRule');

class RoutingTable
{
    const DIRECTION_REWRITING   = RoutingRule::DIRECTION_REWRITING;
    const DIRECTION_REQUEST	= RoutingRule::DIRECTION_REQUEST;
    const DIRECTION_BOTH	= RoutingRule::DIRECTION_BOTH;

//  -------------------------------------------------->

    /**
     * Singleton, instance par défaut
     */
    private static $instance;

//  -------------------------------------------------->

    /**
     * Le tableau de règles dans l'ordre
     */
    private $rules;

//  -------------------------------------------------->

    /**
     * Récupération du singleton, de l'instance unique
     */
    public static function getInstance()
    {
	if ( empty(self::$instance) )
	{
	    self::$instance = new RoutingTable();
	}

	return self::$instance;
    }

//  -------------------------------------------------->

    public function __construct()
    {
	$this->rules = new ArrayList();
    }

//  -------------------------------------------------->

    public function addRulesFromPath($path)
    {
	include($path);
    }

//  -------------------------------------------------->

    public function add(RoutingRule $rule)
    {
	$this->rules->add($rule);
    }

    public function addRule($rule, $controller, $staticVars=null, $direction=RoutingRule::DIRECTION_BOTH)
    {
	$this->add(new RoutingRule($rule, $controller, $staticVars, $direction));
    }

//  -------------------------------------------------->

    public function matchRequestURI($uri)
    {
	// FIXME doit on passer plusieurs fois pour évaluer les réécritures dus à des renomages ?
	// ou alors on laisse ce cas très particulier à apache
	foreach ( $this->rules->elements as $rule )
	{
	    if ( $rule->getDirection() & RoutingRule::DIRECTION_REQUEST )
	    {
		$url = $rule->matchRequestURI($uri);
		if ( !empty($url) )
		{
		    return $url;
		}
	    }
	}

	return null;
    }

    public function rewriteURL(URLRW $url)
    {
	// fIXME doit on aller dans l'autre sens ? (je ne penses pas)
	foreach ( $this->rules->elements as $rule )
	{
	    if ( $rule->getDirection() & RoutingRule::DIRECTION_REWRITING && $rule->rewriteURL($url) )
	    {
		return true;
	    }
	}

	return false;
    }

//  -------------------------------------------------->

    /**
     * Retourne les règles de routage dans l'ordre
     */
    public function getRules()
    {
	return $this->rules;
    }
}
?>
