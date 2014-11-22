<?php

import('vanilla.io.Writer');

class QuotedPrintableWriter implements Writer
{
    private $writer;
    private $lineLength;
    private $count;
    private $gotSpace;
    private $gotCR;

// -------------------------------------------------------------------------->

    public function __construct(Writer $writer, $lineLength=-1)
    {
	$this->writer	    = $writer;
	$this->lineLength   = $lineLength;
	$this->count	    = 0;
	$this->getSpace	    = false;
	$this->getCR	    = false;
    }

// -------------------------------------------------------------------------->

    public function write($s, $length=-1)
    {
	// FIXME doit on utiliser chunk ?
	// petite node, la méthode chunk existe en php, qui permet de couper un texte en ligne
	// de longeur fixe, en insérant en bout de chaque ligne un délimiteur

	$l = ($length >= 0 ? min(strlen($s), $length) : strlen($s));
	for ( $i = 0 ; $i < $l ; $i++ )
	{
	    $c = $s[$i];

	    if ( $this->gotSpace )
	    {
	        if ( ($c === "\n") || ($c === "\r") )
	        {
		    $this->output(" ", true);
	        }
	        else
	        {
	            $this->output(" ", false);
	        }

	        $this->gotSpace = false;
	    }

	    if ( $c === " ")
	    {
	        $this->gotSpace = true;
	        continue;
	    }

	    if ( $c === "\r" )
	    {
	        $this->gotCR = true;
	        $this->count = $this->outputCRLF();
	        continue;
	    }

	    if ( $c == "\n" )
	    {
	        if ( $this->gotCR )
	        {
	            $this->gotCR = false;
	        }
	        else
	        {
	            $this->count = $this->outputCRLF();
	        }
	        continue;
	    }

	    if ( ($c < " ") || ($c > "|") || ($c === '=') )
	    {
	        $this->output($c, true);
	    }
	    else
	    {
	        $this->output($c, false);
	    }
	}
    }

    protected function output($c, $encode=false)
    {
	if ( $encode )
	{
	    $this->count += 3;
	    if ( $this->lineLength > 0 && ($this->count >= $this->lineLength) )
	    {
	        $this->writer->write("=\r\n");
	        $this->count = 3;
	    }

	    $i = ord($c);
	    $this->writer->write("=");
	    $this->writer->write($this->toHex($i));
	}
	else
	{
	    $this->count++;
	    if( $this->lineLength > 0 && ($this->count >= $this->lineLength) )
	    {
	        $this->writer->write("=\r\n");
	        $this->count = 1;
	    }

	    $this->writer->write($c);
	}
    }

    protected function outputCRLF()
    {
	$this->writer->write("\r\n");
	return 0;
    }

    private function toHex($i)
    {
	$h = dechex($i);
	if ( strlen($h) < 2)
	{
	    $h = "0$h";
	}

	return strtoupper($h);
    }

// -------------------------------------------------------------------------->

    public function close()
    {
	$this->writer->close();
    }
}
?>
