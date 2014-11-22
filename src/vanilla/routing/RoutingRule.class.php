<?php

import('vanilla.util.StringMap');
import('vanilla.util.ArrayList');
import('vanilla.net.WebURL');
import('vanilla.routing.RoutingException');

class RoutingRule
{
    const DIRECTION_REWRITING   = 0x1;
    const DIRECTION_REQUEST	= 0x2;
    const DIRECTION_BOTH	= 0x3;

//  -------------------------------------------------->

    /**
     * la règle comme définie dans le ficher de routing
     */
    private $rule;

    /**
     * le path du controller repondant à cette règle 
     */
    private $controller;

    /**
     * un tableau des variables dynamiques
     * trouvées dans l'ordre dans la rule avec pour chacune
     * l'expression régulière devant répondre
     */
    private $dynamicVars;

    /**
     * une StringMap de variables statiques avec leur valeurs
     */
    private $staticVars;

    /*
     * L'expression régulière permettant de tester la request
     */
    private $requestRegexp;

    /*
     * Liste des champs dans l'ordre pour réécrire une url, 
     * peut-être une chaine, ou une variable
     */
    private $rewritingFields;

    /*
     * Détermine le sens d'application de la rêge : uniquement en réécriture, en interprétation de la request ou dans les deux
     */
    private $direction;

//  -------------------------------------------------->

    /**
     * Crée une nouvelle règle
     */
    public function __construct($rule, $controller, $staticVars=null, $direction=self::DIRECTION_BOTH)
    {	
	if ( empty($direction) )
	{
	    $direction = self::DIRECTION_BOTH;
	}

	$this->rule = $rule;
	$this->controller = $controller;
	$this->direction = min(self::DIRECTION_BOTH, max(self::DIRECTION_REWRITING, $direction));
	$this->staticVars = new StringMap();
	$this->dynamicVars = new ArrayList();
	$this->rewritingFields = new ArrayList();

	if ( !empty($staticVars) )
	{
	    foreach ( $staticVars as $name => $value )
	    {
		$this->staticVars->put($name, $value);
	    }
	}

	/*
	   On parse la rule
	*/

	$result = preg_match_all("/\/([^\/]+)/", $rule, $matches);
	if ( $result === false || $result <= 0 )
	{
	    throw new RoutingException("Invalid routing rule $rule");
	}
	
	$reg = "";

	foreach ( $matches[1] as $part )
	{
	    // du type {/mon/path/{<expression>partie-variable}/{partie-variable$wisy-signal-index}/{partie-variable-facultative?}
	    $ex = preg_match("/\{(<[^>]+>)?([^\\$:\?]+)(:[0-9]+)?(\\$[0-9]+)?(\?)?\}/", $part, $match);
	    if ( $ex < 1 )
	    {
		// ce n'est pas une partie variable
		// on construit l'expression
		$reg .= "\/$part";

		$this->rewritingFields->add(Array("type" => "text", "value" => $part));
	    }
	    else
	    {
		// c'est une partie variable
		$varReg = $match[1];
		if ( empty($varReg) )
		{
		    $varReg = "[^\/]+";
		}
		else
		{
		    $varReg = substr($varReg, 1, -1);
		}

		$name = $match[2];

		$groupIndex = isset($match[3]) ? $match[3] : "";
		$groupIndex = $groupIndex == "" ? -1 : intval(substr($groupIndex, 1));

		$index = isset($match[4]) ? $match[4] : "";
		$index = $index == "" ? -1 : intval(substr($index, 1));

		$optional = isset($match[5]) ? true : false;

		// on cherche le nombre de group dans la régular expression
		// FIXME éviter les parenthèses dans les régular expression
		$nbGroups = preg_match_all("/\([^\(\)]+\)/", $varReg, $groups);

		if ( $groupIndex > -1 && $groupIndex >= $nbGroups )
		{
		    throw new Exception("Invalid group index [$groupIndex] for rule $rule");
		}

		// on ajoute la variable
		$this->dynamicVars->add
		(
		    Array
		    (
			"name"		=> $name,
			"exp"		=> "/^$varReg$/", 
			"index"		=> $index,
			"optional"	=> $optional,
			"groupIndex"	=> $groupIndex,
			"nbGroups"	=> $nbGroups
		    )
		);

		// on construit l'expression
		$reg .= "(\/$varReg)" . ($optional ? '?' : '');

		$this->rewritingFields->add(Array("type" => "var", "value" => $name . ($index < 0 ? "" : "[$index]")));
	    }
	}

	$this->requestRegexp = "/^$reg\/?$/";
    }

//  -------------------------------------------------->

    public function getRule()
    {
	return $this->rule;
    }

    public function getDirection()
    {
	return $this->direction;
    }

//  -------------------------------------------------->

    /**
     * Renvoie une WebURL si cette rule match, sinon renvoie null
     */
    public function matchRequestURI($uri)
    {
	if ( preg_match($this->requestRegexp, $uri, $matches) < 1 )
	{
	    return null;
	}

	$url = WebURL::Create($this->controller);
	foreach ( $this->staticVars->elements as $name => $value )
	{
	    $url->putParameter($name, $value);
	}

	$params = new StringMap();

	$groupIndex = 1;
	for ( $i = 0 ; $i < $this->dynamicVars->size() ; $i++ )
	{
	    $var = $this->dynamicVars->get($i);
	    if ( $var["optional"] && (!isset($matches[$groupIndex]) || $matches[$groupIndex] === "") )
	    {
		$groupIndex += 1 + $var["nbGroups"];
		continue;
	    }

	    $name   = $var["name"];
	    $index  = $var["index"];

	    if ( $var["groupIndex"] < 0 )
	    {
		$value  = substr($matches[$groupIndex], 1);
	    }
	    else
	    {
		$gi	= $groupIndex + $var["groupIndex"] + 1;
		$value  = $matches[$gi];
	    }

	    $p = $params->get($name);
	    if ( empty($p) )
	    {
		$p = new ArrayList();
		$params->put($name, $p);
	    }

	    $index = max(0, $index);
	    while ( $p->size() < $index )
	    {
		$p->add("");
	    }

	    $p->add($value);

	    $groupIndex += 1 + $var["nbGroups"];
	}

	foreach ( $params->elements as $name => $value )
	{
	    $url->putParameter($name, implode("|", $value->elements));
	}

	return $url;
    }

    public function rewriteURL(URLRW $url)
    {
	// liste des valeurs des variables dynamiques
	$dynamicValues = new StringMap();

	// liste des paramètres à supprimer
	$parametersToRemove = new ArrayList();

	/**
	 * On vérifie si la règle correspond
	 */

	// test du controller
	if ( $url->getFileWithoutContext() != $this->controller )
	{
	    return false;
	}

	// vérification des paramètres statiques
	foreach ( $this->staticVars->elements as $name => $value )
	{
	    if ( !$url->hasParameter($name) || $url->getParameter($name) != $value )
	    {
		return false;	
	    }

	    $parametersToRemove->add($name);
	}

	// vérification des paramètres dynamiques
	foreach ( $this->dynamicVars->elements as $var )
	{
	    $name   = $var["name"];
	    $index  = $var["index"];

	    if ( !$url->hasParameter($name) )
	    {
		if ( $var["optional"] )
		{
		    continue;
		}

		return false;	
	    }

	    $value = $url->getParameter($name);
	    if ( $index > -1 )
	    {
		// TODO parser la valeur qu'une seule fois
		$value = WisyURL::parseSignalParameters($value);
		$value = isset($value[$index]) ? $value[$index] : null;
	    }

	    if ( preg_match($var["exp"], $value) < 1 )
	    {
		continue;
	    }

	    $dynamicValues->put($name . ($index < 0 ? "" : "[$index]"), $value);

	    if ( !$parametersToRemove->contains($name) )
	    {
		$parametersToRemove->add($name);
	    }
	}

	/*
	   On reconstruit l'url file
	*/

	$file = "";
	foreach ( $this->rewritingFields->elements as $field )
	{
	    if ( $field["type"] == "var" )
	    {
		$var = $field["value"];
		if ( $dynamicValues->contains($var) )
		{
		    $file .= "/" . $dynamicValues->get($var);
		}
	    }
	    else
	    {
		$file .= "/" . $field["value"];
	    }
	}

	/*
	   On supprime les paramètres non nécessaires
	*/

	foreach ( $parametersToRemove->elements as $name )
	{
	    $url->removeParameter($name);
	}

	/*
	   On navigue vers le nouveau file
	*/

	$url->navigate(www($file), false);

	/*
	    Match !
	*/

	return true;
    }
}
?>
