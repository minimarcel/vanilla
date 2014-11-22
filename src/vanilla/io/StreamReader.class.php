<?php

import('vanilla.io.TextReader');
import('vanilla.io.IOException');

class StreamReader implements TextReader
{
    protected $handle;
    protected $closeStream;
    protected $charset;

// -------------------------------------------------------------------------->

    /**
     * Crée un nouveau StreamReader.
     *
     * @param	handle		la ressource
     * @param	closeStream	ferme le stream à la fermeture du reader
     * @param	charset		définit le charset du flux d'entrée. 
     *				Est utilisé essentiellement pour la lecture de fichiers textes (TextReader)
     */
    public function __construct($handle, $closeStream=false, $charset="UTF-8")
    {
	if ( empty($handle) )
	{
	    throw new IOException("Flux null");
	}

	$this->handle	    = $handle;
	$this->closeStream  = $closeStream;
	$this->charset	    = $charset;
    }

    public function __destruct()
    {
	try
	{
	    $this->close();
	}
	catch(Exception $e)
	{}
    }

// -------------------------------------------------------------------------->

    public function read($length)
    {
	if ( empty($this->handle) )
	{
	    throw new IOException('Stream closed');
	}

	if ( feof($this->handle) )
	{
	    return false;
	}

	if ( empty($length) )
	{
	    return '';
	}

	return fread($this->handle, $length);
    }

    /**
     * Lit une ligne complète ou un morceau de la ligne.
     * Renvoie false si la fin du fichier est atteinte.
     */
    public function readln($length=-1)
    {
	if ( empty($this->handle) )
	{
	    throw new IOException('Stream closed');
	}

	$s = ($length < 0 ? fgets($this->handle) : fgets($this->handle, $length));
	if ( $s === false )
	{
	    return false;
	}

	$s = $this->convertCharset($s);

	$l = mb_strlen($s);
	for  ( $i = 0 ; $i < $l ; $i++ )
	{
	    $c = mb_substr($s, $l - $i - 1, 1);
	    if ( $c !== "\r" && $c !== "\n" )
	    {
		break;
	    }
	}

	return mb_substr($s, 0, $l - $i);
    }

    /**
     * Lit jusqu'à la fin de la stream
     */
    public function readToEnd()
    {
	if ( empty($this->handle) )
	{
	    throw new IOException('Stream closed');
	}

	$s = "";
	for ( $b = ""; $b !== false ; $b = $this->read(2048) )
	{
	    $s .= $b;
	}

	return $s;
    }

    public function readTextToEnd()
    {
	return $this->convertCharset( $this->readToEnd() );
    }

    public function close()
    {
	if ( !empty($this->handle) && $this->closeStream )
	{
	    fclose($this->handle);
	}

	unset($this->handle);
    }

    public function getStream()
    {
	return $this->handle;
    }

    /**
     * Convertit la chaîne vers le charset définit par défaut
     * En général le charset par défaut est l'UTF-8
     */
    protected function convertCharset($s)
    {
	$default = mb_internal_encoding();
	if ( $this->charset != $default )
	{
	    $s = mb_convert_encoding($s, $default, $this->charset);
	}

	// FIXME pouvoir préciser un charset UKNOWN pour tenter de le découvrir ?

	return $s;
    }
}
?>
