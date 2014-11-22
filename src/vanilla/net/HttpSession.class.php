<?php
import('vanilla.util.StringMap');
import('vanilla.net.HTTP');

/**
 *
 */
class HttpSession
{
	const SESSION_KEY = 'vanilla_net_http_session';

	private static $instance;

	private $unserialized;
	private $serialized;
	private $invalidated;

//------------------------------------------------------->

	private function __construct($id=null)
	{
		$this->unserialized = new StringMap();
		$this->serialized   = new StringMap();
		$this->invalidated  = false;

		/*
		 * On récupère les clés serialisées dans la session
	 	 */

		if ( !empty($id) )
		{
			session_id($id);
		}

		session_start();
		$s = HTTP::SESSION(self::SESSION_KEY);
		if ( isset($s) )
		{
			$this->serialized->elements = $s;
		}
	}

	function __destruct()
	{
		if ( $this->invalidated )
		{
			return;
		}

		/*
		 * On serialize les objets dans une nouvelle map
	 	 */

		$s = new StringMap();

		/*
		 * On recopie les objets non deserialisés
	 	 */

		foreach ( $this->serialized->elements as $key => $value )
		{
			$s->put($key, $value);
		}

		/*
		 * On serialize les nouveaux objets
		 */

		foreach ( $this->unserialized->elements as $key => $value )
		{
			$o = new Object();
			$o->value = serialize($value);
			if ( $value instanceof SerializableObject )
			{
				$o->classPaths = array_unique($value->getClassPaths());
			}

			$s->put($key, $o);
		}

		/*
		 * On enregistre le tableau de clés dans la session
		 */

		$_SESSION[self::SESSION_KEY] = $s->elements;
	}

	//------------------------------------------------------->

	public static function start($id=null)
	{
		self::$instance = new HttpSession($id);
	}

	public static function destroy()
	{
		session_destroy();
		self::$instance->invalidate = true;
		self::$instance = null;
		self::start();
	}

	public static function getId()
	{
		return session_id();
	}

	public static function acceptCookies()
	{
		// FIXME pourquoi PHPSESSID
		$c = HTTP::COOKIE("PHPSESSID");
		return isset($c);
	}

//------------------------------------------------------->

	public static function put($name, $v)
	{
		self::$instance->unserialized->put($name, $v);
		self::$instance->serialized->remove($name);
	}

	public static function contains($name)
	{
		return self::$instance->unserialized->contains($name) || self::$instance->serialized->contains($name);
	}

	public static function get($name)
	{
		$serialized = self::$instance->serialized->get($name);
		if ( isset($serialized) )
		{
			if ( !empty($serialized->classPaths) )
			{
				foreach ( $serialized->classPaths as $classPath )
				{
					// on importe si possible cette classe
					import($classPath);
				}
			}

			// on désérialize le tout
			$value = unserialize($serialized->value);

			self::$instance->serialized->remove($name);
			self::$instance->unserialized->put($name, $value);
		}
		else
		{
			$value = self::$instance->unserialized->get($name);
		}

		return $value;
	}

	public static function remove($name)
	{
		self::$instance->unserialized->remove($name);
		self::$instance->serialized->remove($name);
	}

	public static function keys()
	{
		return array_merge(self::$instance->unserialized->keys(), self::$instance->serialized->keys());
	}

	public static function isEmpty()
	{
		return self::$instance->unserialized->isEmpty() && self::$instance->serialized->isEmpty();
	}
}
?>
