<?php

import('vanilla.io.TextWriter');
import('vanilla.io.IOException');

class StreamWriter implements TextWriter
{
    // TODO gérer les fins de lines suivant les OS (\n pour unix, \r\n pour windows, \r pour macos)
    // comment le déterminer du system ?
    // utiliser un terminator spécifique par writer ?
    public static $lineSeparator = "\n";

// -------------------------------------------------------------------------->

    protected $handle;
    private $separator;
    protected $charset;

// -------------------------------------------------------------------------->

    public function __construct($handle, $charset="UTF-8")
    {
	if ( empty($handle) )
	{
	    throw new IOException("Flux null");
	}

	$this->handle	    = $handle;
	$this->separator    = self::$lineSeparator;
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

    public function setLineSeparator($separator)
    {
	$this->separator = (empty($separator) ?  self::$lineSeparator : $separator);
    }

    public function getLineSeparator()
    {
        return $this->separator;
    }

    /**
     * Ecrit des bytes
     */
    public function write($s, $length=-1)
    {
	if ( empty($this->handle) )
	{
	    throw new IOException('Stream writer closed');
	}

	if ( strlen($s) == 0 )
	{
	    return;
	}

	$l = ($length >= 0 ? $length : strlen($s));
	if ( fwrite($this->handle, $s, $l) === false )
	{
	    throw new IOException("Impossible d'écrire dans le flux");
	}
    }

    public function writeText($s, $length=-1)
    {
	if ( mb_strlen($s) == 0 )
	{
	    return;
	}

	$l = ($length >= 0 ? min(mb_strlen($s), $length) : mb_strlen($s));
	$s = mb_substr($s, 0, $l);
	$s = $this->convertCharset($s);

	$this->write($s);
    }

    public function writeln($s='')
    {
	$this->writeText($s . $this->separator);
    }

    public function close()
    {
	unset($this->handle);
    }

    public function getStream()
    {
	return $this->handle;
    }

    /**
     * Convertit la chaîne depuis le charset par défaut vers le charset de sortie
     * En général le charset par défaut est l'UTF-8
     */
    protected function convertCharset($s)
    {
	$default = mb_internal_encoding();
	if ( $this->charset != $default )
	{
	    $s = mb_convert_encoding($s, $this->charset, $default);
	}

	// FIXME pouvoir préciser un charset UKNOWN pour tenter de le découvrir ?

	return $s;
    }
}
?>
