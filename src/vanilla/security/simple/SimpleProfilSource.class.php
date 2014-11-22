<?php

import('vanilla.util.StringMap');

import('vanilla.security.ProfilSource');
import('vanilla.security.simple.SimpleProfil');
import('vanilla.security.simple.SimpleUser');

/**
 * 
 */
class SimpleProfilSource implements ProfilSource
{
    private $users;

//------------------------------------------>

    function __construct()
    {
	$this->users = new StringMap();
    }

//------------------------------------------>

    public function addUser(SimpleUser $user)
    {
	$this->users->put($user->getLogin(), $user);
    }

    public function appendUser($name, $login, $password, $roles, $preferedLocaleCode=null, $hashAlgo=null) // par comptatibilité, on en difinit pas
    {
	$user = new SimpleUser($name, $login, $password, $hashAlgo);
	$user->setRoles($roles);
	$user->setPreferedLocaleCode($preferedLocaleCode);
	$this->addUser($user);
	return $this;
    }

    public function appendSecuredUser($name, $login, $password, $roles, $preferedLocaleCode=null, $hashAlgo='sha1')
    {
	return $this->appendUser($name, $login, $password, $roles, $preferedLocaleCode, $hashAlgo);
    }

//------------------------------------------>

    public function getProfilByLoginAndPassword($login, $password)
    {
	$user = $this->users->get($login);
	if ( empty($user) )
	{
	    return null;
	}

	$algo = $user->getHashAlgo();
	if ( !empty($algo) )
	{
	    $password = hash($algo, $password);
	}

	if ( $user->getPassword() === $password )
	{
	    $profil = new SimpleProfil($user);
	    return $profil;
	}

	return null;
    }

    public function unserializeProfil($serialized)
    {
	// serialized contient le login
	$user = $this->users->get($serialized);
	if ( empty($user) )
	{
	    return null;
	}

	$profil = new SimpleProfil($user);
	return $profil;
    }
}
?>
