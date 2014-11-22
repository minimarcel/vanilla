<?php

import('vanilla.net.WebURL');
import('vanilla.runtime.Filter');
import('vanilla.security.Security');

/**
 * 
 */
class SecuritySignFilter implements Filter
{
    private $sourceName;
    private $redirect = true;
    
//----------------------------------------------->

    /**
     *
     */
    public function init(FilterConfig $config)
    {
	$this->sourceName   = $config->getParameter("sourceName");
	$this->redirect	    = $config->getParameter("redirect") === "true";
    }

    /**
     *
     */
    public function execute($resourceName)
    {
	// FIXME si le filtre s'applique à un ensemble d'url, le sign out ne fonctionne pas de partout
	// filtrer juste pour le signIn ?
	$signOut = HTTP::REQUEST('security_signOut');
	if ( $signOut )
	{
	    Security::signOut();
	}
	else
	{
	    $login	= HTTP::REQUEST('security_login');
	    $password   = HTTP::REQUEST('security_password');

	    if ( !empty($login)  )
	    {
		if ( !Security::signIn($login, $password, $this->sourceName) )
		{
		    HumanReadableMessageStack::pushMessage(__c(__FILE__, 'Identifiant ou mot de passe incorrect.'))->setSuccess(false);
		}
		else
		{
		    $url = HTTP::getRequest()->getRequestWebURL();
		    $url->removeParameter('security.login');
		    $url->removeParameter('security.password');
		    HTTP::redirect($url->toString(false));

		    exit();
		}
	    }
	}

	return true;
    }

//----------------------------------------------->

    public function setSourceName($name)
    {
	$this->sourceName = $name;
    }

    public function getSourceName()
    {
	return $this->sourceName;
    }

    public function isRedirect()
    {
	return $this->redirect;
    }

    public function setRedirect($redirect)
    {
	$this->redirect = ($redirect === true);
    }
}
?>
