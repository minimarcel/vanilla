<?php

import('vanilla.sql.Driver');
import('vanilla.sql.ConnectionSource');
import('vanilla.sql.SQLException');

import('vanilla.util.ArrayList');
import('vanilla.util.Date');

/**
 * 
 */
class DB
{
    private static $drivers;
    private static $sources;
    private static $defaultSource;

//----------------------------------------------->

    public static function getConnection()
    {
	if ( isset(self::$defaultSource) )
	{
	    return self::$defaultSource->getConnection();
	}
	
	throw new SQLException('No default connection source defined');
    }
    
    public static function getConnectionForSource($name)
    {
	$source = self::getSourceForName($name);
	if ( !empty($source) )
	{
	    return $source->getConnection();
	}
	
	throw new SQLException('No database source named : ' . $name);
    }
    
//----------------------------------------------->

    public static function getDrivers()
    {
	return self::drivers;
    }
    
    public static function addDriver(Driver $driver)
    {
	if ( empty(self::$drivers) )
	{
	    self::$drivers = new ArrayList();
	    self::$drivers->add($driver);
	}
	else if ( !self::$drivers->contains($driver) )
	{
	    self::$drivers->add($driver);
	}
    }
    
    public static function loadDriver($driverClass)
    {
	if ( empty(self::$drivers) )
	{
	    self::$drivers = new ArrayList();
	}
	
	$classname = import($driverClass);

	foreach ( self::$drivers->elements as $driver )
	{
	    if ( $driver instanceof $classname )
	    {
		return $driver;
	    }
	}
	
	$driver = new $classname();
	self::$drivers->add($driver);
	
	return $driver;
    }
    
//----------------------------------------------->
    
    public static function getSources()
    {
	return self::sources;
    }
    
    public static function getSourceForName($name)
    {
	if ( isset(self::$sources) )
	{
	    foreach ( self::$sources->elements as $src )
	    {
		if ( $src->getName() === $name )
		{
		    return $src;
		}
	    }
	    
	    return null;
	}
	
	throw new SQLException('No connection source');
    }
    
    public static function addResource($name, $driverClass, $host, $database, $user=null, $pwd=null, $permanent=false, $isDefault=true)
    {
	$driver = self::loadDriver($driverClass);
	$src = new ConnectionSource($name, $driver, $host, $database, $user, $pwd, $permanent);

	self::addConnectionSource($src, $isDefault);

	return $src;
    }

    public static function addConnectionSource(IConnectionSource $src, $isDefault=false)
    {
	if ( empty(self::$sources) )
	{
	    self::$sources = new ArrayList();
	}

	self::$sources->add($src);
	
	if ( $isDefault )
	{
	    self::$defaultSource = $src;
	}
    }

//----------------------------------------------->

    public static function quote($s)
    {
	if ( !isset($s) )
	{
	    return 'NULL';
	}

	if ( is_object($s) )
	{
	    $s = "$s";
	}

	if ( !is_string($s) )
	{
	    if ( is_bool($s) )
	    {
		return ( $s ? 1 : 0 );
	    }
	    else if ( is_float($s) || is_double($s) )
	    {
		return str_replace(",", ".", "$s");
	    }

	    return $s;
	}

	if ( $s == '0' )
	{
	    return "'0'";
	}

	$s = self::escape($s);
	if ( empty($s) )
	{
	    return "NULL";
	}

	return "'$s'";
    }

    public static function escape($s)
    {
	if ( empty($s) )
	{
	    return $s;
	}

	// FIXME gestion des quotes
	return addslashes($s);
    }

    public static function dateToString($date)
    {
	if ( !isset($date) || empty($date) )
	{
	    return 'NULL';
	}

	return self::quote($date->format('%Y-%m-%d'));
    }

    public static function timestampToString($date)
    {
	if ( !isset($date) || empty($date) )
	{
	    return 'NULL';
	}

	return self::quote($date->format('%Y-%m-%d %H:%M:%S'));
    }
}
?>
