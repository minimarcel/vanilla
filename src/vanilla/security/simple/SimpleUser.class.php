<?php

import('vanilla.security.LocalisedUser');
import('vanilla.util.ArrayList');

/**
 * 
 */
class SimpleUser implements LocalisedUser
{
    private $name;
    private $login;
    private $roles;
    private $localeCode;

    private $password;
    private $hashAlgo = null;

//  ---------------------------------------->

    function __construct($name, $login, $password, $hashAlgo=null)
    {
	$this->name		= $name;
	$this->login		= $login;
	$this->password		= $password;
	$this->hashAlgo		= $hashAlgo;
	$this->roles		= new ArrayList();
    }

//  ---------------------------------------->

    public function getName()
    {
	return $this->name;
    }

    public function getLogin()
    {
	return $this->login;
    }

    public function getPassword()
    {
	return $this->password;
    }

    public function getHashAlgo()
    {
	return $this->hashAlgo;
    }

    public function getRoles()
    {
	return $this->roles;
    }

    public function setRoles($roles)
    {
	if ( empty($roles) )
	{
	    return;
	}

	$this->roles->setArray($roles);
    }

    public function getPreferedLocaleCode()
    {
	return $this->localeCode;
    }

    public function setPreferedLocaleCode($code)
    {
	$this->localeCode = $code;
    }
}
?>
