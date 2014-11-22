<?php 
// -----------------------------------> 
//
// Add the vanilla basics by declaring : 
//  - he import() function (to import classes)
//  - functions to write paths and rewrite urls
//  - logging functions
//  - localisation functions
//  - and other usefull functions
//
// And handle php errors.
//
// -----------------------------------> 

/**
 * An empty class Object
 */
class Object
{
    public static $null = null;
    public function __constructor()
    {}
}

/**
 * An imported class
 */
class Classes
{
    /**
     * List of classes mapped by  class name
     * Each class is decribed with the properties : name, fullName, path, loaded
     */
    public static $list = Array();

    /**
     * Defines whether the classes should be load on import 
     */
    public static $loadOnImport = true;

//  ------------------------------------------->

    public static function import($fullClassName)
    {
        $e = explode('.', $fullClassName);
        $n = end($e);
        
        if ( isset(self::$list[$n]) )
        {
            return $n;
        }

        $o = new Object();
        $o->name        = $n;
        $o->path        = str_replace('.', '/', $fullClassName) . '.class.php'; 
        $o->fullName    = $fullClassName;
        $o->loaded      = false;

        self::$list[$o->name] = $o;
        if ( self::$loadOnImport )
        {
            self::load($o->name);
        }

        return $o->name;
    }

    public static function load($className)
    {
        if ( isset(self::$list[$className]) )
        {
            $o = self::$list[$className];
            $r = require_once($o->path);

            if ( !$r ) 
            {
                throw new Exception("Unkwnown class file : " . $o->fullName);
            }

            $o->loaded = true;
        }
        else
        {
            throw new Exception("Unknown class $className; it was not imported.");
        }
    }
}

// -----------------------------------> 

/**
 * Import a class
 *  @param  fullClassName   the full class name to import : my.package.MyClass
 *                          the class will be found at the path (relative to the package root) 
 *                          my/package/MyClass.class.php
 */
function import($fullClassName)
{
    return Classes::import($fullClassName);
}

// -----------------------------------> 

// FIXME php prévoie une méthode appellée sur un objet lors de la sérialisation
// du coup on pourrait simplement appeller les méthode d'import lors de la désérialisation
import('vanilla.io.SerializableObject');

// -----------------------------------> 


/**
 * Creates the relative url for the given package file
 *
 *  @param  id      the package id from where the file belong 
 *  @param  file    the file we want to find the relative path
 *
 *  @see www($path)
 */
function wwwLib($id, $file)
{
    return www('LIB/' . Library::$packages[$id]->path . '/' . $file);
}

/**
 * Convert the given path (relative to the website root path) into a relative url (relative to the website root url).
 * The website relative URL is given by the WWW_URL constant, usualy defined into the SERVER/config/server.php file.
 * The returned url will start with a '/' but will not contains the protocol and domain informations, unlike the method awww().
 */
function www($path)
{
    if ( substr($path, 0, 1) == '/' )
    {
        $path = substr($path, 1);
    }

    return WWW_URL . $path;
}

/**
 * Convert the given path (relative to the website root path) into an absolute url.
 * The absolute url is found from the query http informations.
 *
 * @see vanilla.net.HTTP
 * @see vanilla.net.WebURL
 */
function awww($path)
{
    return WebURL::Create(www($path))->toAbsoluteString();
}

/**
 * Convert the given path (relative to the website root path) into an absolute path.
 * The website absolute path is given by the WWW_PATH constant, usualy defined into the SERVER/config/server.php file.
 */
function absolutePath($path)
{
    return WWW_PATH . $path;
}

/**
 * Conver the given absolute path into a relative path (relative to the website root path)
 * The website absolute path is given by the WWW_PATH constant, usualy defined into the SERVER/config/server.php file.
 */
function relativePath($path)
{
    // TODO handle absolute path that reside before the website root path (with ../../ ?)
    if ( substr($path, 0, strlen(WWW_PATH)) === WWW_PATH )
    {
        return substr($path, strlen(WWW_PATH));
    }

    return $path;
}

import('vanilla.runtime.URLRewriting');

/**
 * Rewrite the given relative path (relative to the website root path)
 * using the defined RewriteRules
 *
 * @see vanilla.net.WebURL
 */
function rwww($path)
{
    return WebURL::Create(www($path))->__toString();
}

// -----------------------------------> 

/**
 * Escape special chars for js
 */
function strjs($s, $doNotEscapeSimpleQuote=false)
{
    $s = str_replace('\\', '\\\\', $s);
    $s = str_replace('"', '\\"', $s);
    $s = str_replace("\r\n", "\\r\\n", $s);
    $s = str_replace("\n", "\\n", $s);
    $s = str_replace("\t", "\\t", $s);

    if ( !$doNotEscapeSimpleQuote )
    {
        $s = str_replace('\'', '\\\'', $s);
    }

    return $s;
}

/**
 * Escape special chars for xml (and xhtml)
 */
function strxml($s)
{
    return htmlspecialchars($s, ENT_COMPAT, 'UTF-8');
}

/**
 * Convert any given element into its string representation.<br>
 * Avoid exceptions into __toString methods (that are not allowed in php) by always returning a result (#UNKNOWN if unknonw)
 */
function toString($o)
{
    if ( is_string($o) )
    {
        return $o;
    }

    if ( is_bool($o) )
    {
        return $o ? "true" : "false";
    }

    if ( is_array($o) )
    {
        $s = "";
        foreach ( $o as $k => $e )
        {
            $s .= "{" . "$k:" . toString($e) . "}";
        }

        return "#Array[$s]";
    }

    try
    {
        return "$o";
    }
    catch(Exception $e)
    {}

    if ( is_object($o) )
    {
        return "#" . get_class($o);
    }

    return "#UNKNONW";
}

/**
 * Returns the boolean representation of the given value.
 */
function boolval($v)
{
    if ( is_string($v) )
    {
        return $v == "1" || $v == "true" || $v == "yes" || $v == "oui";
    }

    return ( $v ? true : false );
}

/**
 * Tests if the given string (haystack) starts with the specified prefix (needle).
 */
function startsWith($haystack, $needle)
{
    if ( empty($haystack) || empty($needle) )
    {
        return false;
    }

    if ( strlen($needle) > strlen($haystack) )
    {
        return false;
    }

    return (substr($haystack, 0, strlen($needle)) === $needle);
}

/**
 * Tests if the given string (haystack) ends with the specified suffix (needle).
 */
function endsWith($haystack, $needle)
{
    if ( empty($haystack) || empty($needle) )
    {
        return false;
    }

    if ( strlen($needle) > strlen($haystack) )
    {
        return false;
    }

    return (substr($haystack, -strlen($needle)) === $needle);
}

// -----------------------------------> 
// Expose logging methods

import('vanilla.net.HttpSession'); // FIXME why here?

import('vanilla.runtime.Logger');
import('vanilla.runtime.HumanReadableMessageStack');
import('vanilla.runtime.FilterManager');

Logger::$DoNotLogHumanReadableExceptions = true;

/**
 * Log a "config" level message
 */
function config($msg, $e=null)
{
    Logger::getDefault()->config($msg, $e);
}

/**
 * Log a "debug" level message
 */
function debug($msg, $e=null)
{
    config($msg, $e);
}

/**
 * Log an "info" level message
 */
function info($msg, $e=null)
{
    Logger::getDefault()->info($msg, $e);
}

/**
 * Log a "warning" level message
 */
function warning($msg, $e=null)
{
    Logger::getDefault()->warning($msg, $e);
}

/**
 * Log a "severe" level message
 */
function severe($msg, $e=null)
{
    Logger::getDefault()->severe($msg, $e);
}

// -----------------------------------> 
// Error management

if ( !defined('E_DEPRECATED') )
{
    define('E_DEPRECATED', 8192);
}

if ( !defined('E_USER_DEPRECATED') )
{
    define('E_USER_DEPRECATED', 16384);
}

/**
 * Catch errors and warnings
 */
function vanillaErrorHandler($level, $string, $file, $line, $context)
{
    // TODO use the level the right way : something like ($level & E_ERROR)
    $levelString = '';
    switch($level)
    {
        // FIXME should we exit(1) on a fatal error
        case E_USER_ERROR : 
        {
            $logLevel	    = Logger::LOGGER_SEVERE_LEVEL;	    
            $levelString    = 'E_USER_ERROR';
            break;
        }

        case E_ERROR : 
        {
            $logLevel	    = Logger::LOGGER_SEVERE_LEVEL;	    
            $levelString    = 'E_ERROR';
            break;
        }

        case E_USER_WARNING : 
        {
            $logLevel	    = Logger::LOGGER_INFO_LEVEL;	    
            $levelString    = 'E_USER_WARNING';
            break;
        }

        case E_WARNING : 
        {
            $logLevel	    = Logger::LOGGER_INFO_LEVEL;	    
            $levelString    = 'E_WARNING';
            break;
        }

        case E_USER_NOTICE : 
        {
            $logLevel	    = Logger::LOGGER_INFO_LEVEL;	    
            $levelString    = 'E_USER_NOTICE';
            break;
        }

        case E_NOTICE : 
        {
            $logLevel	    = Logger::LOGGER_INFO_LEVEL;	    
            $levelString    = 'E_NOTICE';
            break;
        }

        case E_USER_DEPRECATED : 
        {
            $logLevel	    = Logger::LOGGER_INFO_LEVEL;	    
            $levelString    = 'E_USER_DEPRECATED';
            break;
        }

        case E_DEPRECATED : 
        {
            $logLevel	    = Logger::LOGGER_INFO_LEVEL;	    
            $levelString    = 'E_DEPRECATED';
            break;
        }

        default : 
        {
            $logLevel	    = Logger::LOGGER_CONFIG_LEVEL;
            $levelString    = "UKNOWN";
        }
    }

    // we don't log warnings generated by the methods loadHTML loadXML and DOMDocument: too verbose and useless
    // FIXME set a config
    if ( $level == E_WARNING && strstr($string, "DOMDocument::loadHTML") != FALSE)
    {
        return true;
    }

    if ( $level == E_NOTICE || $level == E_USER_NOTICE || $level == E_USER_DEPRECATED || $level == E_DEPRECATED )
    {
        Logger::getDefault()->log($logLevel, "The handler catchs an error of level $levelString($level) with message[$string] on[$file:$line] with context[$context]");
        return true;
    }

    // transforme into an exception, 
    // that can be catch by the code
    $e = new RuntimeException($string, $level);
    throw $e;
}

/**
 * Catch uncaught exceptions
 */
function vanillaExceptionHandler($exception)
{
    Logger::getDefault()->severe('Uncaugth exception', $exception);
}

// FIXME
set_error_handler('vanillaErrorHandler', defined('ERROR_REPORTING_LEVEL') ? ERROR_REPORTING_LEVEL : E_ALL); 
set_exception_handler('vanillaExceptionHandler');

// -----------------------------------> 
// localisations and translations

import('vanilla.localisation.Localisation');

/**
 * Translate the given string, using the domain "default"
 * @see vanilla.localisation.Localisation#translate
 */
function __t($string /*, ...$parameters*/)
{
    $parameters = func_get_args();
    array_shift($parameters);

    return Localisation::translate($string, $parameters);
}

/**
 * Translate the given plural string, using the domain "default"
 * @see vanilla.localisation.Localisation#translatePlural
 */
function __tp($single, $plural, $nb /*, ...$parameters*/)
{
    $parameters = func_get_args();
    array_shift($parameters);
    array_shift($parameters);

    return Localisation::translatePlural($single, $plural, $nb, $parameters);
}

// configure shorten translation methods
// Some lib already use those methods
if ( !function_exists('__') )
{
    // synonym of __t
    function __($string /*, ...$parameters*/)
    {
        $parameters = func_get_args();
        array_shift($parameters);

        return Localisation::translate($string, $parameters);
    }

    // synonym of __tp
    function __p($single, $plural, $nb /*, ...$parameters*/)
    {
        $parameters = func_get_args();
        array_shift($parameters);
        array_shift($parameters);

        return Localisation::translatePlural($single, $plural, $nb, $parameters);
    }
}

/**
 * Translate the given string using the domain deduced by the package where the filename belong
 * @see vanilla.localisation.Localisation#translate
 */
function __c($filename, $string /*, ...$parameters*/)
{
    $parameters = func_get_args();
    array_shift($parameters);
    array_shift($parameters);

    return Localisation::translate($string, $parameters, Library::findPackageIdForFile($filename));
}

/**
 * Translate the given plural string using the domain deduced by the package where the filename belong
 * @see vanilla.localisation.Localisation#translate
 */
function __cp($filename, $single, $plural, $nb /*, ...$parameters*/)
{
    $parameters = func_get_args();
    array_shift($parameters);
    array_shift($parameters);
    array_shift($parameters);

    return Localisation::translatePlural($single, $plural, $nb, $parameters, Library::findPackageIdForFile($filename));
}

/**
 * Translate the given string using the given specified domain
 * @see vanilla.localisation.Localisation#translate
 */
function __d($domain, $string /*, ...$parameters*/)
{
    $parameters = func_get_args();
    array_shift($parameters);
    array_shift($parameters);

    return Localisation::translate($string, $parameters, $domain);
}

/**
 * Translate the given plural string using the given specified domain
 * @see vanilla.localisation.Localisation#translate
 */
function __dp($domain, $single, $plural, $nb /*, ...$parameters*/)
{
    $parameters = func_get_args();
    array_shift($parameters);
    array_shift($parameters);
    array_shift($parameters);

    return Localisation::translatePlural($single, $plural, $nb, $parameters, $domain);
}

// you may have to use an apache2 directive 
if ( !defined('PHP_MAJOR_VERSION') || (PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION < 3) )
{
    set_magic_quotes_runtime(0);
}

// always use UTF-8
mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");
?>
