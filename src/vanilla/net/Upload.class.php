<?php

import('vanilla.util.TextUtil');
import('vanilla.util.Date');
import('vanilla.io.File');
import('vanilla.io.FileReader');
import('vanilla.io.FileWriter');

import('vanilla.net.FileUploadException');
import('vanilla.net.InvalidFileExtensionException');
import('vanilla.net.OutOfFileSizeException');

/**
 * 
 */
class Upload
{
    const UPLOAD_DIRECTORY_URL	= 'SERVER/upload';
    const TMP_URL		= 'tmp';

//----------------------------------------------->

    /**
     * Crée un dossier avec l'URL donnée relative au dossier d'upload et renvoie un objet File
     */
    public static function createUploadDirectory($url)
    {
	$dir = self::ensureThatUploadDirectoryExists();
	$dir = File::fromChildURL($dir, $url);
	if ( !$dir->exists() )
	{
	    $dir->mkdirs();
	}
	else if ( !$dir->isDirectory() )
	{
	    throw new Exception("The upload directory [$path] is not a directory");
	}

	return $dir;
    }

    public static function ensureThatUploadDirectoryExists()
    {
	$dir = File::fromRelativeURL(self::UPLOAD_DIRECTORY_URL);
	if ( !$dir->exists() )
	{
	    $dir->mkdir();
	}
	else if ( !$dir->isDirectory() )
	{
	    throw new Exception("Unable to find the upload directory : $dir");
	}

	return $dir;
    }

    public static function resolveUploadDirectory($url)
    {
	$dir = self::ensureThatUploadDirectoryExists();
	return File::fromChildURL($dir, $url);
    }

//----------------------------------------------->

    /**
     * Sauvegarde le fichier donné et renvoie un file relatif à la racine du site
     *
     * @param	uploadDirectoryURL  l'url du répertoire
     * @param	name		    le nom du paramètre
     * @param	fileName	    le nom du fichier, si ce nom ne comporte pas d'extension, celle du fichier sera utilisée
     */
    public static function saveFile($uploadDirectoryURL, $name, $fileName=null, $options=null, $index=0)
    {
	if ( self::checkUploadErrors($name, $options, $index) )
	{
	    if ( empty($fileName) )
	    {
		$fileName = self::getFileAttributeValue($name, 'name', $index);
	    }
	    else
	    {
		// on rajoute l'extension si elle n'existe pas
		$f1 = new File($fileName);
		$e1 = $f1->getNameAndExtension();
		
		if ( empty($e1[1] ) )
		{
		    $f2 = new File(self::getFileAttributeValue($name, 'name', $index));
		    $e2 = $f2->getNameAndExtension();
		    $fileName = $e1[0] . $e2[1];
		}
	    }

	    $directory	= self::createUploadDirectory($uploadDirectoryURL);
	    $temp	= self::getFileAttributeValue($name, 'tmp_name', $index);
	    $file	= self::getUniqueFile(File::fromChildPath($directory, $fileName));

	    move_uploaded_file($temp, $file->getPath());
	    chmod($file->getPath(), File::$fileMode);

	    return $file;
	}
	else if ( isset($_FILES[$name]) )
	{
	    switch(self::getFileAttributeValue($name, 'error', $index))
	    {
		// FIXME est-ce qu'on peut avoir plusieurs sections de catch en php ? oui :)
		// UPLOAD_ERR_INI_SIZE
		// UPLOAD_ERR_FORM_SIZE

		// UPLOAD_ERR_PARTIAL
		// UPLOAD_ERR_NO_FILE
		// UPLOAD_ERR_CAN_WRITE
		// UPLOAD_ERR_EXTENSION
	    }
	}

	return null;
    }

    /**
     * Sauvegarde le fichier donné dans le répertoire d'upload temporaire et renvoie un file relatif à la racine du site 
     *
     * @param	name	    le nom de la resource uplodée
     * @param	timeToLive  le temps de vie du fichier en minute, par défaut cette valeur est égale à 24h
     */
    public static function saveTmpFile($name, $timeToLive=1440, $fileName=null, $options=null, $index=0)
    {
	self::deleteExpiredTmpFiles();

	if ( !self::checkUploadErrors($name, $options, $index) )
	{
	    return null; 
	}

	if ( empty($fileName) )
	{
	    $fileName = self::getFileAttributeValue($name, 'name', $index);
	}
	else
	{
	    // on rajoute l'extension si elle n'existe pas
	    $f1 = new File($fileName);
	    $e1 = $f1->getNameAndExtension();
	    
	    if ( empty($e1[1] ) )
	    {
		$f2 = new File(self::getFileAttributeValue($name, 'name', $index));
		$e2 = $f2->getNameAndExtension();
		$fileName = $e1[0] . $e2[1];
	    }
	}
		
	$temp	    = self::getFileAttributeValue($name, 'tmp_name', $index);
	$file	    = self::getTmpFileForName($fileName, $timeToLive);

	move_uploaded_file($temp, $file->getPath());
	chmod($file->getPath(), File::$fileMode);

	return $file;
    }

    public static function getTmpFileForName($fileName, $timeToLive)
    {
	$directory  = self::createUploadDirectory(self::TMP_URL);
	$name	    = time() . '_' . $timeToLive . '_' . $fileName;

	return self::getUniqueFile(File::fromChildPath($directory, $name));
    }

    public static function deleteExpiredTmpFiles()
    {
	$date = new Date();
	$directory = self::createUploadDirectory(self::TMP_URL);
	foreach ( $directory->listDirectoryFiles() as $file )
	{
	    $info = self::getTmpFileInfos($file); 
	    if ( empty($info) )
	    {
		continue;
	    }

	    if ( $date->isAfter($info->deathTime) )
	    {
		$file->delete();
	    }
	}
    }

    public static function saveFileFromTmp($uploadDirectoryURL, File $tmpFile, $delete=true, $suffix=null)
    {
	$file = self::getFileFromTmp($uploadDirectoryURL, $tmpFile, $suffix);
	if ( $delete )
	{
	    $tmpFile->move($file);
	}
	else
	{
	    $tmpFile->copy($file);	    
	}

	chmod($file->getPath(), File::$fileMode);
	return $file;
    }

    public static function getFileFromTmp($uploadDirectoryURL, File $tmpFile, $suffix=null)
    {
	if ( empty($tmpFile) || !$tmpFile->exists() || !$tmpFile->isFile() )
	{
	    // FIXME doit on vérifier qu'il est dans le répertoire temporaire
	    return null;
	}

	$directory  = self::createUploadDirectory($uploadDirectoryURL);
	$info	    = self::getTmpFileInfos($tmpFile);
	$file	    = File::fromChildPath($directory, $info->fileName);

	if ( !empty($suffix) )
	{
	    list($fileName, $fileExtension) = $file->getNameAndExtension();
	    $file = File::fromChildPath($directory, $fileName . $suffix . strtolower($fileExtension));
	}

	$file = self::getUniqueFile($file);
	return $file;
    }

    private static function getTmpFileInfos(File $file)
    {
	$name		= $file->getName();	
	$birthTimePos	= strpos($name, '_');
	$timeToLivePos	= strpos($name, '_', $birthTimePos + 1);

	if ( $birthTimePos === false || $timeToLivePos === false )
	{
	    return null;
	}

	$birthTime  = substr($name, 0, $birthTimePos);
	$timeToLive = substr($name, $birthTimePos + 1, $timeToLivePos - $birthTimePos - 1);
	$name	    = substr($name, $timeToLivePos + 1);

	$info = new Object();
	$info->birthTime    = new Date(intval($birthTime));
	$info->timeToLive   = intval($timeToLive);
	$info->deathTime    = new Date(intval($birthTime) + intval($timeToLive)*60);
	$info->fileName	    = $name;

	return $info;
    }

    /**
     * Copie le fichier donné avec un nom unique
     */
    public static function copyFile(File $file)
    {
	if ( !$file->exists() )
	{
	    return null;
	}

	// FIXME doit on vérifier si on est dans un répertoire d'upload ?

	$copy = self::getUniqueFile($file);
	$file->copy($copy);

	return $copy;
    }

    public static function getUniqueFile(File $file)
    {
	// on récupère le nom et l'extension
	$nameAndExtension = $file->getNameAndExtension();

	// on remplace l'extension
	$nameAndExtension[0] = TextUtil::stripNonAscci($nameAndExtension[0]);

	// on remplace le nom du fichier par le nouveau
	$file = File::fromChildPath($file->getParentFile(), $nameAndExtension[0] . $nameAndExtension[1]);

	$i = 0;
	while ( $file->exists() )
	{
	    $name = $nameAndExtension[0] . $i++ . $nameAndExtension[1];
	    $file = File::fromChildPath($file->getParentFile(), $name);
	}

	return $file;
    }

    public static function isUploadedFile($name, $index=0)
    {
	$v = self::getFileAttributeValue($name, 'tmp_name', $index);
	if ( empty($v) )
	{
	    return false;
	}

	return is_uploaded_file($v);
    }

    public static function getFileAttributeValue($name, $att, $index=0)
    {
	if ( !isset($_FILES[$name]) )
	{
	    return null;
	}

	if ( !isset($_FILES[$name][$att]) )
	{
	    return null;
	}

	$v = $_FILES[$name][$att];
	if ( is_array($v) )
	{
	    if ( !isset($v[$index]) )
	    {
		return null;
	    }

	    $v = $v[$index];
	}
	else if ( $index != 0 )
	{
	    return null;
	}

	return $v;
    }

    /*
     * Vérifie les erreurs liées à l'upload.
     * Ces erreurs peuvent provenir de php, ou des options de restriction 
     * si elles sont précisées
     * @return true si un fichier a été uploadé sans erreur, false si aucun fichier n'a été uploadé
     * @throw UploadFileException si une erreur survient
     */
    private static function checkUploadErrors($name, $options=null, $index=0)
    {
	if ( !self::isUploadedFile($name, $index) )
	{
	    /*
	       Erreurs générées pas php
	    */

	    if ( !isset($_FILES[$name]) )
	    {
		return false;
	    }

	    $n = self::getFileAttributeValue($name, 'name', $index);
	    if ( empty($n) )
	    {
		return false;
	    }

	    $phpCode = self::getFileAttributeValue($name, 'error', $index);
	    if ( $phpCode == 0 || $phpCode == UPLOAD_ERR_NO_FILE )
	    {
		return false;
	    }

	    switch($phpCode)
	    {
		case UPLOAD_ERR_INI_SIZE	: 
		case UPLOAD_ERR_FORM_SIZE	: throw new OutOfFileSizeException($phpCode);
		case UPLOAD_ERR_EXTENSION	: throw new InvalidFileExtensionException($phpCode);
	    }

	    throw new FileUploadException("Php internal error while uploading file", $phpCode);
	}
	else if ( !empty($options) )
	{
	    /*
	       Erreur générées par les options de restriction
	    */
	    
	    // on vérifie l'extension
	    if ( isset($options['extensions']) && !empty($options['extensions']) )
	    {
		$extensions = new ArrayList();

		// on passe tout en lowercase
		foreach ( $options['extensions'] as $e )
		{
		    $extensions->add(strtolower($e));
		}

		$f = new File(self::getFileAttributeValue($name, 'name', $index));
		$e = $f->getNameAndExtension();

		if ( empty($e[1]) || !$extensions->contains(strtolower($e[1])) )
		{
		    throw new InvalidFileExtensionException(-1, $extensions->elements);
		}
	    }

	    // on vérifie la taille (en byte)
	    if ( isset($options['size']) && !empty($options['size']) )
	    {
		$size = $options['size'];
		if ( self::getFileAttributeValue($name, 'size', $index) > $size )
		{
		    throw new OutOfFileSizeException(-1, $size);
		}
	    }
	}

	return true;
    }
}
