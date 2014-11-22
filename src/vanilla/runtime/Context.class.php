<?php

import('vanilla.util.ObjectCache');
import('vanilla.runtime.ConfigMap');
import('vanilla.util.StringMap');

/**
 * 
 */
class Context
{
//  ---------------------------------------->

    private static $instance;
    private $cache;
    private $config;

//  ---------------------------------------->

    protected function __construct()
    {
	$this->cache	= new ObjectCache();
	$this->config	= new ConfigMap();
    }

//  ---------------------------------------->

    public static function getInstance()
    {
	if ( empty(self::$instance) )
	{
	    self::$instance = new Context();
	}
	
	return self::$instance;
    }
    
//  ---------------------------------------->

    public function getCache()
    {
	return $this->cache;
    }

    public static function addInCache($object/*, $keys*/)
    {
	$cache	    = self::getInstance()->getCache();
	$callback   = new ReflectionMethod(get_class($cache), 'add');
	$args	    = func_get_args();

	$callback->invokeArgs($cache, $args);
    }

    public static function addInCacheByClassName($classname, $object/*, $keys*/)
    {
        $cache	    = self::getInstance()->getCache();
        $callback   = new ReflectionMethod(get_class($cache), 'addByClassName');
        $args	    = func_get_args();

        $callback->invokeArgs($cache, $args);
    }

    public static function getFromCache($classname/*, $keys*/)
    {
	$cache	    = self::getInstance()->getCache();
	$callback   = new ReflectionMethod(get_class($cache), 'get');
	$args	    = func_get_args();

	return $callback->invokeArgs($cache, $args);
    }

    public static function removeFromCache($object/*, $keys*/)
    {
	$cache	    = self::getInstance()->getCache();
	$callback   = new ReflectionMethod(get_class($cache), 'remove');
	$args	    = func_get_args();

	$callback->invokeArgs($cache, $args);
    }

//  ---------------------------------------->

    /**
     * Renvoie une valeur de la config key
     * @return ConfigMap
     */
    public function getConfigMap()
    {
		return $this->config;	
    }
    
    /**
     * Définit si cette configuration existe
     * @see ConfigMap#exists
     */
    public static function existsConfig($key)
    {
    	return self::getInstance()->config->exists($key);
    }

    /**
     * Renvoie la première valeur de la config key
     * @see ConfigMap#get
     */
    public static function getConfig($key)
    {
		return self::getInstance()->config->get($key);
    }

    /**
     * Renvoie la liste de valeur de la config key
     * @see ConfigMap#getList
     */
    public static function getConfigList($key)
    {
		return self::getInstance()->config->getList($key);
    }
}
?>
