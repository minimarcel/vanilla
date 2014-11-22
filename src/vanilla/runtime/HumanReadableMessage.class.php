<?php

import('vanilla.io.File');

/**
 * 
 */
class HumanReadableMessage implements SerializableObject
{
    private $message;
    private $exception;
    private $success;

    private $code;
    private $source;

//  ----------------------------------------------->

    public function __construct($message, $exception=null, $code=null)
    {
	$this->message	    = $message;
	$this->exception    = $exception;
	$this->success	    = null;
	$this->code	    = $code;

	// on essaye de savoir qui émet ce message
	$e = new Exception();
	$t = $e->getTrace();

	// par défaut on essaye de connaitre la class
	$this->source = self::findClassSource($t);

	// sinon on récupère le chemin du fichier
	if ( empty($this->source) ) 
	{
	    $file	= $t[0]["file"];
	    $f		= new File($file);
	    $url	= $f->toRelativeURL();
	    $libraryId	= Library::findPackageIdForFile($file);

	    // est-ce dans une library ?
	    if ( !empty($libraryId) )
	    {
		$pf = new File(Library::$packages[$libraryId]->getAbsolutePath());
		$this->source = substr($url, strlen($pf->toRelativeURL())+1);
	    }
	    else
	    {
		$this->source = $url;
	    }
	}
    } 

    private static function findClassSource($t)
    {
	$i = 1;
	while ( isset($t[$i]) && isset($t[$i]["class"]) )
	{
	    $c = $t[$i]["class"];
	    if ( $c != "HumanReadableMessage" && $c != "HumanReadableMessageStack" )
	    {
		return $c;
	    }

	    $i++;
	}

	return null;
    }

//  ----------------------------------------------->

    public function getClassPaths()
    {
	$a = Array('vanilla.runtime.HumanReadableMessage');
	if ( $this->exception instanceof SerializableObject )
	{
	    $a = array_merge($a, $this->exception->getClassPaths());
	}

	return $a;
    }

//----------------------------------------------->

    public function setMessage($message)
    {
	$this->message = $message;
    }

    public function getMessage()
    {
	return $this->message;
    }

    public function setException($exception)
    {
	$this->exception = $exception;
    }

    public function getException()
    {
	return $this->exception;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getSource()
    {
        return $this->source;
    }

    /**
     * Retourne le code du message
     */
    public function getCode()
    {
	return $this->code;
    }

    /**
     * Retourne le code de l'exception si elle existe
     */
    public function getExceptionCode()
    {
        if ( !empty($this->exception) )
        {
	    if ( $this->exception instanceof CodifedException )
	    {
		return $this->exception->getStringCode();
	    }

            return $this->exception->getCode();
        }

        return null;
    }

    /**
     * Force le sucess
     */
    public function setSuccess($success)
    {
	$this->success = $success;
    }

    public function isSuccess()
    {
	if ( isset($this->success)  )
	{
	    return $this->success;
	}
	
	return ( empty($this->exception) );
    }
}
?>
