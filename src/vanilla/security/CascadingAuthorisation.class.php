<?php

import('vanilla.security.Authorisation');

/**
 * 
 */
class CascadingAuthorisation implements Authorisation
{
    private $parent;
    private $child;

//  --------------------------->

    /*
     * If the parent authorisation is null, the authorisation will pass !
     */
    public function __construct(Authorisation $child, /*Authorisation*/ $parent=null)
    {
	$this->parent	= $parent;
	$this->child	= $child;
    }
    
//  --------------------------->

    public function getParentAuhtorisation()
    {
	return $this->parent;
    }

    public function getChildAuhtorisation()
    {
	return $this->child;
    }

//  --------------------------->

    public function isAuthorized(/*Profil*/ $profil)
    {
	if ( !empty($this->parent) && !$this->parent->isAuthorized() )
	{
	    return false;
	}

	return $this->child->isAuthorized($profil);
    }
}

