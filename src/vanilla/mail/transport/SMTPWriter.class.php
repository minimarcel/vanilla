<?php

import('vanilla.io.Writer');

class SMTPWriter implements Writer
{
    private $writer;
    private $lastWasCRLF = false;

// -------------------------------------------------------------------------->

    public function __construct(Writer $writer)
    {
	$this->writer = $writer;
    }

// -------------------------------------------------------------------------->

    // FIXME moyen plus simple ?, 
    public function write($s, $length=-1)
    {
	if ( strlen($s) == 0 )
	{
	    return;
	}

	$l = ($length >= 0 ? min(strlen($s), $length) : strlen($s));
	if ( $l == 0 )
	{
	    return;
	}

	$s = substr($s, 0, $l);
	if ( $this->lastWasCRLF && $s[0] == '.' )
	{
	    $s = ".$s";
	}

	$s = str_replace("\n.", "\n..", $s);
	$s = str_replace("\r.", "\r..", $s);

	$l = strlen($s) - 1;
	$this->lastWasCRLF = ($s[$l] == "\n" || $s[$l] == "\r");

	$this->writer->write($s);
    }

    public function close()
    {
	$this->writer->close();
    }
}
?>
