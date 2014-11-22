<?php

import('vanilla.security.Profil');
import('vanilla.security.simple.SimpleUser');

/**
 * 
 */
class SimpleProfil implements Profil
{
    private $user;

//  ---------------------------------------->

    function __construct(SimpleUser $user)
    {
	$this->user = $user;
    }

//  ---------------------------------------->

    public function getUser()
    {
	return $this->user;
    }

    public function isInRole($role)
    {
	return $this->user->getRoles()->contains($role);
    }

    public function serializeProfil()
    {
	return $this->user->getLogin();
    }
}
?>
