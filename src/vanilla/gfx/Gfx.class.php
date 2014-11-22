<?php

import('vanilla.gfx.GfxDriver');
import('vanilla.gfx.GfxException');
import('vanilla.gfx.Color');
import('vanilla.gfx.FontSource');

/**
 * 
 */
class Gfx
{
    private static $driver;
    private static $fontSource=null;

//  ----------------------------------------------->

    public static function setDriver(GfxDriver $driver)
    {
	self::$driver = $driver;
    }

    public static function loadDriver($driverClass)
    {
	$classname  = import($driverClass);
	$driver	    = new $classname();

	if ( $driver instanceof GfxDriver )
	{
	    self::setDriver($driver);
	}
    }

    public static function getDriver()
    {
	return self::$driver;
    }

    private static function checkDriver()
    {
	if ( empty(self::$driver) )
	{
	    throw new GfxException("No Gfx driver");
	}
    }

//  ----------------------------------------------->

    public static function loadImageFromFile(File $file)
    {
	self::checkDriver();
    	return self::$driver->loadImageFromFile($file);
    }

    public static function getImageInfoFromFile(File $file)
    {
	self::checkDriver();
    	return self::$driver->getImageInfoFromFile($file);
    }

    public static function createImage($width, $height, /*Color */$background=null)
    {
	self::checkDriver();
    	return self::$driver->createImage($width, $height, $background);
    }

    public static function writeImageToFile(Image $image, File $file, $format)
    {
	self::checkDriver();
	// TODO pourquoi ne pas utiliser le outputstream : gd ne le supporte pas ?
    	return self::$driver->writeImageToFile($image, $file, $format);
    }

    public static function writeImageToStandardOutput(Image $image, $format)
    {
	self::checkDriver();
	// TODO pourquoi ne pas utiliser le outputstream : gd ne le supporte pas ?
    	return self::$driver->writeImageToStandardOutput($image, $format);
    }

    /*
     * Renvoie le rectangle entourant une chaîne pour une font donnée
     */
    public static function getStringBoundingBox(Font $font, $str, $angle=0)
    {
	self::checkDriver();
	return self::$driver->getStringBoundingBox($font, $str, $angle);
    }

//  ----------------------------------------------->

    //public static function getAvailableFonts()
    //{
    //    return self::getFontSource()->getAvailableFonts();
    //}

    public static function loadFont($name, $size=10, $style=0)
    {
	$file = self::getFontSource()->findFontFileBy($name, $style);
	if ( empty($file) )
	{
	    return null;
	}

	return new Font(trim(strtolower($name)), $file, $size, $style);
    }

    public static function getFontSource()
    {
	if ( empty(self::$fontSource) )
	{
	    self::$fontSource = new FontSource();
	}

	return self::$fontSource;
    }
}

// register the default driver
if ( function_exists('gd_info') )
{
    Gfx::loadDriver('vanilla.gfx.gd.GdDriver');
}
?>
