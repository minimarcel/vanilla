<?php

import('vanilla.runtime.Filter');
import('vanilla.security.Security');
import('vanilla.security.simple.SimpleAuthorisation');

/**
 * 
 */
class RestrictedAccessFilter implements Filter
{
    private $redirectPage;
    private $authorisation;
    
//----------------------------------------------->

    public function __construct()
    {
	$this->authorisation = new SimpleAuthorisation();
    }

    /**
     *
     */
    public function init(FilterConfig $config)
    {
	$this->redirectPage = $config->getParameter("redirectPage");

	// FIXME créer une interface FilterAuthorisation 
	// pouvant être loadé et initialisée ?
	// TODO gérer les paramètres pour l'authorisation
    }

    /**
     *
     */
    public function execute($resourceName)
    {
	$profil	    = Security::getConnectedProfil();
	$accepted   = $this->authorisation->isAuthorized($profil);

	if ( !$accepted )
	{
	    if ( !empty($this->redirectPage) )
	    {
		HTTP::redirect(www($this->redirectPage));
	    }
	    else
	    {
		HTTP::writeHttpResponseStatusLine(HTTP::HTTP_Forbidden);
	    }
	    
	    HTTP::write403ResponseMessage();
	    exit();
	}

	return $accepted;
    }

//----------------------------------------------->

    public function setRedirectPage($page)
    {
	$this->redirectPage = $page;
    }

    public function getRedirectPage()
    {
	return $this->redirectPage;
    }

    /**
     * Défini les roles acceptés
     *
     * @param	roles	    une chaîne de caractére contenant les roles séparés par des virgules
     */
    public function setAuthorisation(Authorisation $authorisation)
    {
	$this->authorisation = $authorisation;
    }

    public function getAuthorisation()
    {
	return $this->authorisation;
    }
}
?>
