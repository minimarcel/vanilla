<?php

import('vanilla.io.QuotedPrintableWriter');

class MimeQEncoder extends QuotedPrintableWriter
{
    /**
     * WORD special chars constant.
     */
    const WORD_SPECIALS = "=_?\"#$%&'(),.:;<>@[\\]^`{|}~";

    /**
     * TEXT special chars constant
     */
    const TEXT_SPECIALS = "=_?";

// -------------------------------------------------------------------------->

    private $specials;
    
// -------------------------------------------------------------------------->

    public function __construct(Writer $writer, $word)
    {
	parent::__construct($writer, -1);
	$this->specials = $word ? self::WORD_SPECIALS : self::TEXT_SPECIALS;
    }

// -------------------------------------------------------------------------->

    public function write($s, $length=-1)
    {
	$l = ($length >= 0 ? min(strlen($s), $length) : strlen($s));
	for ( $i = 0 ; $i < $l ; $i++ )
	{
	    $c = $s[$i];

	    if ( $c == " " )
	    {
		$this->output("_", false);
		continue;
	    }

	    if ( ($c < " ") || ($c >= "|") || strpos($this->specials, $c) !== false )
	    {
		$this->output($c, true);
	    }
	    else
	    {
		$this->output($c, false);
	    }
	}
    }

// -------------------------------------------------------------------------->

    public static function encodedLength($s, $word)
    {
	$length = 0;
	$theSpecials = $word ? self::WORD_SPECIALS : self::TEXT_SPECIALS;

	for ( $i = 0; $i < strlen($s); $i++ )
	{
	    $c = $s[$i];
	    if( ($c < " ") || ($c >= "|") || strpos($theSpecials, $c) !== false )
	    {
		$length += 3;
	    }
	    else
	    {
		$length++;
	    }
	}

	return $length;
    }
}
