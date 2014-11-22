<?php

import('vanilla.io.File');
import('vanilla.routing.RoutingTable');

class Router
{
    const CONTROLLERS_PATH	    = "WEB/controllers/";

    const NOTFOUND_CONTROLLER	    = "404";
    const ERROR_CONTROLLER	    = "500";
    const FORBIDDEN_CONTROLLER	    = "403";
    const HOME_CONTROLLER	    = "home";

//  -------------------------------------------------->

    /**
     * La request uri
     */
    protected $requestURI;

    /**
     * Le controller trouvé
     */
    protected $controller;

    /**
     * La request uri retraduite
     */
    protected $uri;

    /**
     * Le noeud trouvé dans l'arborescence
     */
    protected $node;

//  -------------------------------------------------->

    public function __construct()
    {
	$this->requestURI = HTTP::getRequest()->getRequestWebURL()->getFile();
    }

    public function execute()
    {
	try
	{
	    $this->executeRules();
	    $this->uri = $this->controller;

	    /*
	       Si aucun controller on le déduit de la request URI
	    */

	    if ( empty($this->controller) )
	    {
		if ( $this->requestURI == "/" )
		{
		    $this->controller = self::HOME_CONTROLLER;
		    $this->uri = $this->controller;
		}
		else
		{
		    $this->controller = $this->requestURI;
		    $this->uri = $this->requestURI;
		}
	    }

	    $this->executeController();
	}
	catch(Exception $e)
	{
	    severe("Une errreur est survenue lors de l'execution du router", $e);

	    // on passer sur le controller 500 si aucune information n'a encore été envoyée
	    if ( !headers_sent() )
	    {
		$this->controller = self::ERROR_CONTROLLER;
		$this->executeController();
	    }
	}
    }

//  -------------------------------------------------->

    public function getController()
    {
	return $this->controller;
    }

    public function getRequestURI()
    {
	return $this->requestURI;
    }

    public function getURI()
    {
	return $this->uri;
    }

//  -------------------------------------------------->

    /**
     * Execute les règles de routage
     * Si une règle match, on positionne les paramètres dans l'url 
     * et positionne la variable d'instance controller
     */
    protected function executeRules()
    {
	$url = RoutingTable::getInstance()->matchRequestURI($this->requestURI);
	if ( !empty($url) )
	{
	    $this->controller = $url->getFile();
	    foreach ( $url->getParameterNames() as $name )
	    {
		// FIXME arrays
		$value = $url->getParameter($name);
		
		// on indique au système que des variables sont passées en GET ... propre ou pas propre ?
		$_GET[$name] = $value;
		$_REQUEST[$name] = $value;
	    }
	}
    }

    /**
     * 
     */
    protected function executeController()
    {
	$controllerPath = $this->getControllerPath();

	if ( $this->controller == self::ERROR_CONTROLLER )
	{
	    // FIXME pour la 500 faire comme la 400 (show500Page) et l'appeller dans le catch plus haut !
	    HTTP::writeHttpResponseStatusLine(HTTP::HTTP_Server_Error);
	    if ( !$this->controllerExists($controllerPath, $this->controller) )
	    {
		HTTP::writeResponseMessage(HTTP::HTTP_Server_Error, "");
	    }
	    else
	    {
		$this->includeController($controllerPath, $this->controller);
	    }
	}
	else if ( $this->controller == self::FORBIDDEN_CONTROLLER )
	{
	    // FIXME pour la 403, trouver comment retourner un forbidden exception dans l'execution des règles ????
	    HTTP::writeHttpResponseStatusLine(HTTP::HTTP_Forbidden);
	    if ( !$this->controllerExists($controllerPath, $this->controller) )
	    {
		HTTP::writeResponseMessage(HTTP::HTTP_Forbidden, "");
	    }
	    else
	    {
		$this->includeController($controllerPath, $this->controller);
	    }
	}
	else
	{
	    if ( empty($this->controller) || !$this->controllerExists($controllerPath, $this->controller) )
	    {
		$this->controller = self::NOTFOUND_CONTROLLER;
	    }

	    if ( $this->controller == self::NOTFOUND_CONTROLLER )
	    {
		$this->show404Page();
	    }
	    else
	    {
		$this->includeController($controllerPath, $this->controller);
	    }
	}
    }

    protected function controllerExists($controllerPath, $controller)
    {
	return File::fromRelativeURL($controllerPath . $controller . ".php")->exists();
    }

    protected function getControllerPath()
    {
	return defined('CONTROLLERS_PATH') ? CONTROLLERS_PATH : self::CONTROLLERS_PATH;
    }

    public function show404Page()
    {
	$controller	= self::NOTFOUND_CONTROLLER;
	$controllerPath	= $this->getControllerPath();

	HTTP::writeHttpResponseStatusLine(HTTP::HTTP_Not_Found);

	if ( !$this->controllerExists($controllerPath, $controller) )
	{
	    HTTP::writeResponseMessage(HTTP::HTTP_Not_Found, "");
	}
	else
	{
	    $this->includeController($controllerPath, $controller);
	}
    }

    /*
       Include controller in a diffrent method to create a scope to variables
    */
    protected function includeController($controllerPath, $controller)
    {
	include($controllerPath . $controller . ".php");
    }
}
?>
