<?php

import('vanilla.io.TextWriter');

class CSVWriter 
{
    private $writer;
    private $decodeUTF8=false;
    private $beginOfLine=true;

// -------------------------------------------------------------------------->

    public function __construct(TextWriter $writer)
    {
	$this->writer	= $writer;
    }

    public function setDecodingUTF8($decode)
    {
	warning("This methode is deprecated, use a text writer with a specified charset instead");	
	$this->decodeUTF8 = ($decode == true);
    }

// -------------------------------------------------------------------------->

    public function writeln()
    {
	$this->writer->writeText("\r\n");
	$this->beginOfLine = true;
    }

    public function writeCell($v)
    {
	if ( $this->beginOfLine )
	{
	    $this->beginOfLine = false;
	}
	else
	{
	    $this->writer->writeText(";");
	}

	if ( $this->decodeUTF8 )
	{
	    // FIXME deprecated !!! on doit utiliser un text writer qui décode l'UTF-8 dans le charset de destination à la place
	    $v = utf8_decode($v);
	}

	$this->writer->writeText( $this->encode($v) );
	return $this;
    }

    private function encode($v)
    {
	if ( mb_strstr($v, "\r") !== false || mb_strstr($v, "\n") !== false || mb_strstr($v, '"') !== false || mb_strstr($v, ';')  !== false )
	{
	    // FIXME est-ce que seul les \r\n sont acceptés ? 

	    $v = str_replace('"', '""', $v);
	    $v = "\"$v\"";
	}

	return $v;
    }
}
?>
