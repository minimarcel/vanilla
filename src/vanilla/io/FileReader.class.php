<?php

import('vanilla.io.StreamReader');
import('vanilla.io.SeekReader');
import('vanilla.io.IOException');

class FileReader extends StreamReader implements SeekReader
{
    private $file;

// -------------------------------------------------------------------------->

    public function __construct(File $file, $charset="UTF-8")
    {
	try
	{
	    parent::__construct(fopen($file->getPath(), "r"), true, $charset);
	}
	catch(Exception $e)
	{
	    // TODO gérer les causes
	    throw new IOException("Impossible d'ouvrir le fichier : ".$file->getPath()." :\n$e");
	}

	$this->file = $file;
    }

// -------------------------------------------------------------------------->

    public function seekTo($position)
    {
	if ( fseek($this->handle, $position) == -1 )
	{
	    throw new IOException("Can't seek at the given position : $position");
	}
    }

    public function getCurrentPosition()
    {
	return ftell($this->handle);
    }

    public function getSize()
    {
	// FIXME est-ce que la taille d'un flux peut évoluer après ouverture ???
	return $this->file->getSize();
    }

// -------------------------------------------------------------------------->

    public function getFile()
    {
	return $this->file;
    }
}
?>
