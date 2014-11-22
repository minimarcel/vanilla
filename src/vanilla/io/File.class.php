<?php

import('vanilla.io.IOException');

class File
{
    public static $fileMode   = 0644;
    public static $dirMode    = 0755;

// -------------------------------------------------------------------------->

    private $path; 
    private $fileInfo;

// -------------------------------------------------------------------------->
    
    public function __construct($path)
    {
	$this->path = $path;
    }

    public function __destruct()
    {
	if ( isset($this->fileInfo) )
	{
	    unset($this->fileInfo);
	}
    }

// -------------------------------------------------------------------------->

    /**
     * Retourne l'URL relative à la racine du site
     */
    public function toRelativeURL()
    {
	$path = relativePath($this->path);
	if ( DIRECTORY_SEPARATOR !== '/' )
	{
	    $url = str_replace(DIRECTORY_SEPARATOR, '/', $url);
	}

	return $path;
    }

    public function isFile()
    {
	return is_file($this->path);
    }

    public function isDirectory()
    {
	return is_dir($this->path);
    }

    public function exists()
    {
	return file_exists($this->path);
    }

    public function getPath()
    {
	return $this->path;
    }

    public function getCanonicalPath()
    {
	return realpath($this->path);
    }

    public function getName()
    {
	return basename($this->path);
    }

    /**
     * Retourne un tableau avec le nom sans l'extension et l'extension
     */
    public function getNameAndExtension()
    {
	$name = $this->getName();
	$nameWithoutExtension = $this->getName();
	$extension = '';

	$pointIndex = strrpos($name, '.');
	if ( $pointIndex !== false )
	{
	    $nameWithoutExtension = substr($name, 0, $pointIndex);
	    $extension = substr($name, $pointIndex);
	}

	return Array($nameWithoutExtension, $extension);
    }

    public function getParentPath()
    {
	return dirname($this->path);
    }

    public function getParentFile()
    {
	return new File( $this->getParentPath() );
    }

    public function delete()
    {
	if ( $this->isDirectory() )
	{
	    return (rmdir($this->path) == true);
	}

	return (unlink($this->path) == true);
    }

    public function deleteRecursively()
    {
	if ( $this->isDirectory() )
	{
	    foreach ( $this->listDirectoryFiles() as $file )
	    {
		$file->deleteRecursively();
	    }
	}

	$this->delete();
    }

    public function getSize()
    {
	return filesize($this->path);
    }

    public function mkdir($mode=null)
    {
	if ( empty($mode) )
	{
	    $mode = self::$dirMode;
	}

	try
	{
	    mkdir($this->path, $mode, false);
	    chmod($this->path, $mode);
	}
	catch(Exception $e)
	{
	    throw new IOException("Can't create directory : " .$this->path . " ; caused by : \n $e");
	}
    }

    public function mkdirs($mode=null)
    {
	$dirs	= array(); 
	$f	= $this;

	do
	{
	    $dirs[] = $f;
	    $f = $f->getParentFile();
	}
	while ( !$f->exists() );

	for ( $i = sizeof($dirs) - 1 ; $i >= 0 ; $i-- )
	{
	    $dirs[$i]->mkdir($mode);
	}
    }

    public function touch()
    {
	touch($this->path);
    }

    public function getContent()
    {
	return file_get_contents($this->path);
    }

    public function getTextContent($charset="UTF-8")
    {
	$s = $this->getContent();

	// FIXME factoriser avec la méthode convertCharset de StreamReader
	$default = mb_internal_encoding();
	if ( $charset != $default )
	{
	    $s = mb_convert_encoding($s, $default, $charset);
	}

	return $s;
    }

    public function chmod($mode)
    {
	if ( empty($mode) )
	{
	    return;
	}

	chmod($this->path, $mode);
    }

// -------------------------------------------------------------------------->

    /**
     * Retourne un array contenant le nom des fichiers présents dans ce répertoire
     */
    public function listDirectory()
    {
        $handle = null;
        $files = Array();

        try
        {
            $handle = opendir($this->path);
            while ( ($file = readdir($handle)) !== false ) 
            {
		if ( $file === '.' || $file === '..' )
		{
		    continue;
		}

		$files[] = $file;
            }
        }
        catch(Exception $e)
        {
            if ( !empty($handle) )
            {
        	closedir($handle);
            }

            throw $e;
        }

        if ( !empty($handle) )
        {
            closedir($handle);
        }

        return $files;
    }

    public function listDirectoryFiles()
    {
        $paths = $this->listDirectory();
        $files = Array();
        foreach( $paths as $path )
        {
            $files[] = self::fromChildPath($this, $path);
        }

        return $files;
    }

// -------------------------------------------------------------------------->

    public function move(File $dest)
    {
        $this->rename($dest);	
    }

    public function rename(File $dest)
    {
        rename($this->path, $dest->path);	
    }

    public function copy(File $dest)
    {
        copy($this->path, $dest->path);	
    }

// -------------------------------------------------------------------------->

    private function getFileInfo()
    {
	if ( !self::isFileInfoSupported() )
	{
	    throw new Exception("FileInfo not supported");
	}

	if ( empty($this->fileInfo) )
	{
	    $this->fileInfo = new FInfo();
	}

	return $this->fileInfo;
    }

    public function getMime()
    {
	return $this->getFileInfo()->file($this->getCanonicalPath(), FILEINFO_MIME);
    }

    private function parseMime($s)
    {
	if ( empty($s) )
	{
	    return null;
	}

	$matches = null;
	if ( preg_match("/(\S+\/?\S+?); charset=(\S+)/", $s, $matches) && sizeof($matches) > 1 )
	{
	    $o = Array("type" => $matches[1]);
	    if ( sizeof($matches) > 2 )
	    {
		$o["charset"] = $matches[2];
	    }

	    return $o;
	}

	return null;
    }

    public function getMimeEncoding()
    {
	if ( !defined("FILEINFO_MIME_ENCODING") )
	{
	    $mime = $this->parseMime($this->getMime());
	    if ( !empty($mime) && isset($mime["charset"]) )
	    {
		return $mime["charset"];
	    }

	    return null;
	}

	return $this->getFileInfo()->file($this->getCanonicalPath(), FILEINFO_MIME_ENCODING);
    }

    public function getMimeType()
    {
	if ( !defined("FILEINFO_MIME_TYPE") )
	{
	    $mime = $this->parseMime($this->getMime());
	    if ( !empty($mime) && isset($mime["type"]) )
	    {
		return $mime["type"];
	    }

	    return null;
	}

	return $this->getFileInfo()->file($this->getCanonicalPath(), FILEINFO_MIME_TYPE);
    }

// -------------------------------------------------------------------------->

    public function __toString()
    {
	return $this->path;
    }

// -------------------------------------------------------------------------->

    /**
     * Retourne un objet File pour le chemin absolu donné
     */
    public static function fromAbsolutePath($path)
    {
	return new File($path);
    }

    /**
     * Retourne un objet File pour le chemin donné relatif à la racine de l'application web 
     */
    public static function fromRelativePath($path)
    {
	return self::fromAbsolutePath(absolutePath($path));
    }
    
    /**
     * Retourne un objet File pour l'URL donnée relative à la racine de l'application web
     */
    public static function fromRelativeURL($url)
    {
	if ( DIRECTORY_SEPARATOR !== '/' )
	{
	    $url = str_replace('/', DIRECTORY_SEPARATOR, $url); 
	}

	return self::fromRelativePath( self::convertURL($url) );
    }

    private static function convertURL($url)
    {
	if ( DIRECTORY_SEPARATOR !== '/' )
	{
	    $url = str_replace('/', DIRECTORY_SEPARATOR, $url); 
	}
	
	return $url;
    }

    /**
     * Retourne un nouvelle instance de l'objet File depuis le couple père/fils, 
     * le chemin du fils étant relatif à celui du père.
     */
    public static function fromChildPath(File $parent, $path)
    {
	$parent = $parent->getPath();
        if ( substr($parent, strlen($parent) - 1, 1) != DIRECTORY_SEPARATOR )
        {
            $parent .= DIRECTORY_SEPARATOR;
        }

        if ( substr($path, 0, 1) == DIRECTORY_SEPARATOR )
        {
            $path = substr($path, 1);
        }

        return new File($parent . $path);
    }

    /**
     * Retourne un nouvelle instance de l'objet File depuis le couple père/fils, 
     * l'URL du fils étant relative au chemin du père.
     */
    public static function fromChildURL(File $parent, $url)
    {
	return self::fromChildPath($parent, self::convertURL($url) );
    }

    public static function isFileInfoSupported()
    {
	return function_exists('finfo_open');
    }

}
