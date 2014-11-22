<?php

import('vanilla.text.css.CSSToken');

class CSSTokenizer
{
    const SEPARATOR		= "{.,#:>+~:;}";
    const WHITE_SPACE		= " \n\r\t";
    const KEEP_PRECEDING_SPACE	= ".#";

//  ------------------------------------------------->

    private $index = 0;
    private $string;
    private $length = 0;

    private $prevTok;
    private $currTok;

//  ------------------------------------------------->

    public function __construct($s)
    {
	// suppression des commentaires
	$s = preg_replace('/\/\*.*?\*\//s', "", $s);

	$this->string = trim($s);
	$this->length = strlen($this->string);
	$this->skipWhiteSpaces();
    }

//  ------------------------------------------------->

    public function getIndex()
    {
	return $this->index;
    }

    public function getString()
    {
	return $this->string;
    }

//  ------------------------------------------------->

    public function getToken()
    {
	return $this->currTok;
    }

    public function hasNextToken()
    {
	if ( !empty($this->currTok) )
	{
	    $this->prevTok = $this->currTok;
	}

	$this->currTok = $this->parseNext();
	return !empty($this->currTok);
    }

    private function parseNext()
    {
	if ( $this->index >= $this->length )
	{
	    return null;
	}

	$val = "";
	for ( ; $this->index < $this->length ; )
	{
	    $c = $this->string[$this->index++];

	    if ( $this->isWhiteSpace($c) )
	    {
		// on tombe sur un espace au tout début
		if ( strlen($val) == 0 )
		{
		    // on vérifie que c'est bien l'espace qu'on veut renvoyer
		    if ( $this->skipWhiteSpaces() )
		    {
			$c = $this->string[$this->index];
			if ( $this->isSpecial($c) && !$this->iskeepingPrecedingSpace($c) )
			{
			    continue;
			}
		    }

		    return new CSSToken(" ", true);
		}

		$this->index--;
		break;
	    }

	    if ( $this->isSpecial($c) )
	    {
		if ( strlen($val) == 0 )
		{
		    $this->skipWhiteSpaces();
		    return new CSSToken($c, true);
		}

		$this->index--;
		break;
	    }

	    $val .= $c;
	}

	return new CSSToken(trim($val));
    }

    private function isSpecial($c)
    {
	return (strpos(self::SEPARATOR, $c) !== false);
    }

    private function isKeepingPrecedingSpace($c)
    {
	return (strpos(self::KEEP_PRECEDING_SPACE, $c) !== false);
    }

    private function isWhiteSpace($c)
    {
	return (strpos(self::WHITE_SPACE, $c) !== false);
    }

    private function skipWhiteSpaces()
    {
	for ( ; $this->index < $this->length ; $this->index++ )
	{
	    $c = $this->string[$this->index];
	    if ( !$this->isWhiteSpace($c) )
	    {
		return true;
	    }
	}

	return false;
    }
}
?>
