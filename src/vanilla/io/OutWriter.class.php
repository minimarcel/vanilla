<?php

import('vanilla.io.TextWriter');

class OutWriter implements TextWriter
{
    protected $charset;

// -------------------------------------------------------------------------->

    public function __construct($charset="UTF-8")
    {
	$this->charset = $charset;
    }

// -------------------------------------------------------------------------->

    /**
     * Ecrit des bytes
     */
    public function write($s, $length=-1)
    {
	if ( strlen($s) == 0 )
	{
	    return;
	}

	$l = ($length >= 0 ? min(strlen($s), $length) : strlen($s));
	echo substr($s, 0, $l);
    }

    /**
     * Ecrit un texte en le convertissant dans le charset final.
     * 
     */
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

    public function close()
    {}

//  ----------------------------------------6>

    /**
     * Convertit la chaîne depuis le charset par défaut vers le charset de sortie
     * En général le charset par défaut est l'UTF-8
     * TODO mettre cette méthode dans un charset helper
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
