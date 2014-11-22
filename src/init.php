<?php
// -----------------------------------> 
//
// Initialize vanilla : 
//  - creates the vanilla library
//  - include all packages
//  - call the lang.php 
//  - initialize the configurations (/SERVER/config and /WEB/config)
//  - start the php sessions
//  - execute all the filters attached to this http query (if any)
//
// -----------------------------------> 


/**
 * The vanilla package library
 */
class Library
{
    public static $packages;
    public static $path;
    public static $dirCache;

    /**
     * Init the library 
     *  @param   path    the path where to find the libraries to include (usualy the /LIB directory)
     */
    public static function init($path)
    {
        self::$path     = $path;
        self::$packages = Array();
        self::$dirCache = Array();
    }

    /**
     * Add a new package to this library
     *  @param  path    the package path, relative to the LIB directory
     */
    public static function addPackage($path)
    {
        $p = new LibraryPackage($path);
        self::$packages[$p->id] = $p;

        // add this package in the class path
        set_include_path( get_include_path() . PATH_SEPARATOR . self::$path . '/' . $p->path); 

        return $p;
    }

    /**
     * Determines the package for the given filename
     */
    public static function findPackageIdForFile($filename)
    {
        $dir = dirname($filename);
        if ( array_key_exists($dir, self::$dirCache) )
        {
            return self::$dirCache[$dir];
        }

        foreach ( self::$packages as $p )
        {
            if ( $p->containsDirectory($dir) )
            {
                $id = $p->id;
                self::$dirCache[$dir] = $id;
                return $id;
            }
        }

        return null;
    }
}

// init the vanilla library
Library::init(VANILLA_LIB_PATH);

/**
 * A package into the library
 */
class LibraryPackage
{
    public static $packages = Array();

    public $id;
    public $version;
    public $path;
    public $realPath;

    public function __construct($path)
    {
        $this->path	= $path;

        if ( !file_exists($this->getAbsolutePath() . '/_package.php') )
        {
            throw new Exception("Can't find the _package.php file at path " . $path);
        }

        require( $this->getAbsolutePath() . '/_package.php');

        self::$packages[$this->id] = $this;

        // FIXME
        $this->version = str_replace("@REVISION@", "dev", $this->version);
    }

    public function getAbsolutePath()
    {
        return Library::$path . '/' . $this->path;
    }

    public function containsDirectory($dir)
    {
        $l = strlen($this->realPath) + 1;
        if ( strlen($dir) < $l )
        {
            return false;
        }

        return (substr($dir, 0, $l) === $this->realPath . '/');
    }
}

// --> use import after this call
require_once Library::$path . '/packages.php';

// after this last require_once call
// we should use import() to include classes 
require_once 'lang.php';

import('vanilla.io.File');
function include_if_exists($s)
{
    $f = new File($s);
    if ( $f->exists() )
    {
        include_once $f->__toString();
    }
}

// include server resources
include_if_exists(Library::$path . '/../SERVER/config/server.php');

// FIXME should we set the website root directory into the include path ?
set_include_path( get_include_path() . PATH_SEPARATOR . WWW_PATH);

// include web resources
include_if_exists(Library::$path . '/../WEB/config/web.php');

// initialize configs
include_if_exists(Library::$path . '/../SERVER/config/init.php');
include_if_exists(Library::$path . '/../WEB/config/init.php');

// start the php session
HttpSession::start(HTTP::REQUEST('phpsessid'));

// execute all filters
FilterManager::executeFilters();
?>
