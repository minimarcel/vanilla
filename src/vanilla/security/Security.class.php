<?php

import('vanilla.security.LocalisedUser');
import('vanilla.security.Profil');
import('vanilla.security.ProfilSource');
import('vanilla.security.ExtendedProfilSource');
import('vanilla.security.SecurityException');

import('vanilla.security.crypt.Cipher');

/**
 *
 */
class Security
{
	const COOKIE_NAME = 'vsc';
	
//----------------------------------------------->
	
	private static $sources = Array();
	private static $profil;
	private static $profilLoaded = false;
		
//----------------------------------------------->
	
	public static $USE_COOKIE 	= false;
	public static $COOKE_TIME	= 0;

//----------------------------------------------->

	/**
	 * Retourne l'utilisateur actuellement connecté
	 */
	public static function getConnectedUser()
	{
		$profil = self::getConnectedProfil();
		if ( !empty($profil) )
		{
			return $profil->getUser();
		}

		return null;
	}

	public static function isConnectedUser()
	{
		$user = self::getConnectedUser();
		return !empty($user);
	}

	/**
	 * Retourne le profil connecté
	 */
	public static function getConnectedProfil()
	{
		if ( empty(self::$sources) )
		{
			// FIXME comment gère t-on ça ?
			// throw new SecurityException('No profil source defined.');
			return null;
		}

		if ( self::$profilLoaded === false )
		{
			list($serialized, $sourceName) = self::loadFromSession();
			
			if ( !empty($serialized) && !empty($sourceName) && !empty(self::$sources[$sourceName]) )
			{
				self::$profil = self::$sources[$sourceName]->unserializeProfil($serialized);
				if ( empty(self::$profil) )
				{
					// erreur lors du chargement du profil, par précaution on détruit la session
					HttpSession::destroy();
				}
			}

			self::$profilLoaded = true;
		}

		return self::$profil;
	}

	public static function isInRole($role)
	{
		$profil = self::getConnectedProfil();
		if ( empty($profil) )
		{
			return false;
		}

		return $profil->isInRole($role);
	}

	/**
	 * Détermine si l'utilisateur connecté est dans un des rôles donnés
	 *
	 * @param	roles	soit un tableau de roles (string), soit une chaine de roles sépararés par des virgules
	 */
	public static function isInOneOfRoles($roles)
	{
		$profil = self::getConnectedProfil();
		if ( empty($profil) )
		{
			return false;
		}

		if ( !is_array($roles) )
		{
			$roles = explode(",", $roles);
		}

		foreach ( $roles as $role )
		{
			if ( $profil->isInRole(trim($role)) )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * The classic signIn way, by login and password
	 */
	public static function signIn($login, $password, $sourceName=null)
	{
		return self::signInByLogins(Array("login" => $login, "password" => $password), $sourceName);
	}

	/**
	 * The more secure signIn way with connexion identifiants list
	 */
	public static function signInByLogins(Array $logins, $sourceName=null)
	{
		self::signOut();
		if ( !empty($sourceName) )
		{
			return self::signInForSource($logins, $sourceName);
		}

		foreach ( self::$sources as $name => $source )
		{
			if ( self::signInForSource($logins, $name) )
			{
				return true;
			}
		}

		return false;
	}

	private static function signInForSource(Array $ids, $sourceName)
	{
		if ( !isset(self::$sources[$sourceName]) )
		{
			return false;
		}

		$source = self::$sources[$sourceName];
		if ( $source instanceof ExtendedProfilSource )
		{
			$profil = $source->getProfilByLogins($ids);
		}
		else if ( $source instanceof ProfilSource )
		{
			$profil = $source->getProfilByLoginAndPassword($ids["login"], $ids["password"]);
		}
		else
		{
			throw new Exception("Can't find a way to load profil for source [$sourceName]");
		}

		if ( empty($profil) )
		{
			return false;
		}

		self::$profil = $profil;
		self::$profilLoaded = true;

		self::saveInSession($profil, $sourceName);
		return true;
	}

	/**
	 * Sign au user by is identifiants. La source doit être précisée.
	 * Méthode utilisée en interne pour logger un utilisateur qui vient de s'inscrire par exemple
	 */
	public static function signInByIds(Array $ids, $sourceName)
	{
		self::signOut();
		if ( !isset(self::$sources[$sourceName]) )
		{
			return false;
		}

		$source = self::$sources[$sourceName];
		if ( !($source instanceof ExtendedProfilSource) )
		{
			throw new Exception("The profil source [$sourceName] doesn't accept connexion by ids");
		}

		$profil = $source->getProfilByIds($ids);
		if ( empty($profil) )
		{
			return false;
		}

		self::$profil = $profil;
		self::$profilLoaded = true;

		self::saveInSession($profil, $sourceName);
		return true;
	}

	public static function signOut()
	{
		if ( self::isConnectedUser() )
		{
			self::disableProfil();
			HttpSession::destroy();
			
			if ( self::$USE_COOKIE )
			{
				unset($_COOKIE[self::COOKIE_NAME]);
				setcookie(self::COOKIE_NAME, "", -1);
			}
		}
	}

	/**
	 * Force le profil a être rechargé
	 */
	public static function disableProfil()
	{
		self::$profilLoaded = false;
		self::$profil = null;
	}

//----------------------------------------------->

	public static function loadSource($name, $sourceClass)
	{
		$classname  = import($sourceClass);
		$source	    = new $classname();

		self::addSource($name, $source);
		return $source;
	}

	public static function addSource($name, IProfilSource $source)
	{
		self::$sources[$name] = $source;
	}
	
//----------------------------------------------->

	private static function saveInSession($profil, $sourceName)
	{
		if ( self::$USE_COOKIE )
		{
			$o = Array();
			$o['p'] = $profil->serializeProfil();
			$o['s'] = $sourceName;
			
			$s = serialize($o);
			setcookie(self::COOKIE_NAME, Cipher::cipherRandom($s), self::$COOKE_TIME);
		}
		else 
		{
			HttpSession::put('vanilla.security.Profil', $profil->serializeProfil());
			HttpSession::put('vanilla.security.Source', $sourceName);
		}
	}
	
	private static function loadFromSession()
	{
		$serialized = null;
		$sourceName = null;
		
		if ( self::$USE_COOKIE )
		{
			$s = HTTP::COOKIE(self::COOKIE_NAME);
			if ( !empty($s) )
			{
				$o = unserialize(Cipher::decipher($s));
				if ( is_array($o) && isset($o['p']) && isset($o['s']) )
				{
					$serialized = $o['p'];
					$sourceName = $o['s'];
				}
			}
		}
		else
		{
			$serialized = HttpSession::get('vanilla.security.Profil');
			$sourceName	= HttpSession::get('vanilla.security.Source');
		}
		
		return Array($serialized, $sourceName);
	}
}
?>
