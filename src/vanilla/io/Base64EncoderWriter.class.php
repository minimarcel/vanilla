<?php

import('vanilla.io.Writer');

class Base64EncoderWriter implements Writer
{
    private $writer;
    private $lineLength;
    private $count;

// -------------------------------------------------------------------------->

    public function __construct(Writer $writer, $lineLength=-1)
    {
	$this->writer	    = $writer;
	$this->lineLength   = $lineLength;
	$this->count	    = 0;
    }

// -------------------------------------------------------------------------->

    public function write($s, $length=-1)
    {
	$l = ($length >= 0 ? min(strlen($s), $length) : strlen($s));
	$s = substr($s, 0, $l);

	// on encode les data
	$b = base64_encode($s);
	$l = strlen($b);

	if ( $l == 0 )
	{
	    // rien à écrire
	    return;
	}

	$r = $this->lineLength - $this->count;
	if ( $r == 0 )
	{
	    // on été déjà à la fin de la ligne
	    $this->writer->write("\r\n");
	    $this->count = 0;
	    $r = $this->lineLength;
	}

	$r = min($r, $l);
	while ( $r > 0 )
	{
	    $this->writer->write($b, $r);
	    $this->count = $r;

	    $b = substr($b, $r);
	    $l = strlen($b);
	    if ( $l > 0 )
	    {
		$this->writer->write("\r\n");
		$this->count = 0;
	    }

	    $r = min($this->lineLength, $l);
	}
    }

// -------------------------------------------------------------------------->

    public function close()
    {
	$this->writer->close();
    }
}
?>
