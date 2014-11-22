<?php

import('vanilla.io.FileLock');
import('vanilla.io.File');
import('vanilla.io.FileWriter');
import('vanilla.io.FileReader');


/**
 * Le worker est la classe qui travaille avec le dossier SERVER/_work_
 * Elle permet grâce à des clés d'obtenir des locks ou de gérer des numéros de version.
 */
class Worker
{
    const WORK_DIRECTORY_URL = "SERVER/_work_";

//----------------------------------------------->

    public static function ensureThatWorkDirectoryExists()
    {
	$dir = File::fromRelativeURL(self::WORK_DIRECTORY_URL);
	if ( !$dir->exists() )
	{
	    info("The work directory [$dir] has been created, to increase security denie the access to this directory by http protocol");
	    $dir->mkdir();
	}
	else if ( !$dir->isDirectory() )
	{
	    throw new Exception("Unable to find the word direcory : $dir");
	}

	return $dir;
    }

    public static function getWorkDirectory($url)
    {
	$dir = self::ensureThatWorkDirectoryExists();
	$dir = File::fromChildURL($dir, $url);
	if ( !$dir->exists() )
	{
	    $dir->mkdirs();
	}
	else if ( !$dir->isDirectory() )
	{
	    throw new Exception("The work directory [$path] is not a directory.");
	}


	return $dir;
    }

//----------------------------------------------->

    private static function keyToFile($key, $ext)
    {
	return str_replace(".", "_", $key) . ".$ext";
    }

    /**
     * Retourne une instance de l'object Lock si un lock est obtenu, null sinon.
     */
    public static function obtainLock($key)
    {
	$dir	= self::getWorkDirectory("locks");
	$lock	= new FileLock(File::fromChildURL($dir, self::keyToFile($key, "lock")));

	if ( !$lock->obtain() )
	{
	    return null;
	}

	return $lock;
    }
}
?>
