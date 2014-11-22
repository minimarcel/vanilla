<?php

import('vanilla.security.Authorisation');

/**
 * 
 */
class SimpleAuthorisation implements Authorisation
{
    private $acceptedRoles;
    private $exceptedRoles;
    
//----------------------------------------------->

    public function isAuthorized(/*Profil*/ $profil)
    {
	if ( $this->isInExceptedRoles($profil) )
	{
	    return false;
	}

	return $this->isInAcceptedRoles($profil);
    }

    private function isInAcceptedRoles($profil)
    {
	if ( empty($profil) )
	{
	    return false;
	}

	if ( empty($this->acceptedRoles) )
	{
	    return true;
	}

	foreach ( $this->acceptedRoles as $role )
	{
	    if ( $profil->isInRole($role) )
	    {
		return true;
	    }
	}

	return false;
    }

    private function isInExceptedRoles($profil)
    {
	if ( empty($this->exceptedRoles) )
	{
	    return false;
	}

	if ( !empty($profil) )
	{
	    foreach ( $this->exceptedRoles as $role )
	    {
		if ( $profil->isInRole($role) )
		{
		    return true;
		}
	    }
	}

	return false;
    }

//----------------------------------------------->

    /**
     * Défini les roles acceptés
     * 
     * @param	roles	    un tableau de roles
     */
    public function setAcceptedRoles($roles)
    {
	$this->acceptedRoles = $roles;
    }

    /**
     * Défini les roles acceptés
     *
     * @param	roles	    une chaîne de caractére contenant les roles séparés par des virgules
     */
    public function setAcceptedRolesString($roles)
    {
	if ( empty($roles) )
	{
	    $this->acceptedRoles = null;
	}
	else
	{
	    $this->acceptedRoles = explode(',', $roles);
	}
    }

    /**
     * Défini les roles refusés
     * 
     * @param	roles	    un tableau de roles
     */
    public function setExceptedRoles($roles)
    {
	$this->exceptedRoles = $roles;
    }

    /**
     * Défini les roles refusés
     *
     * @param	roles	    une chaîne de caractére contenant les roles séparés par des virgules
     */
    public function setExceptedRolesString($roles)
    {
	if ( empty($roles) )
	{
	    $this->exceptedRoles = null;
	}
	else
	{
	    $this->exceptedRoles = explode(',', $roles);
	}
    }

//  ----------------------------------------->

    public static function Create(/*string or Array*/ $acceptedRoles=null, /*string or Array*/ $exceptedRoles=null)
    {
	$a = new SimpleAuthorisation();
	
	if ( !empty($acceptedRoles) )
	{
	    if ( is_array($acceptedRoles) )
	    {
		$a->setAcceptedRoles($acceptedRoles);
	    }
	    else
	    {
		$a->setAcceptedRolesString($acceptedRoles);
	    }
	}

	if ( !empty($exceptedRoles) )
	{
	    if ( is_array($exceptedRoles) )
	    {
		$a->setExceptedRoles($exceptedRoles);
	    }
	    else
	    {
		$a->setExceptedRolesString($exceptedRoles);
	    }
	}

	return $a;
    }
}

