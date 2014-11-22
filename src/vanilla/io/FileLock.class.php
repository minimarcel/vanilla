<?php

import('vanilla.runtime.Lock');

/**
 * 
 */
class FileLock implements Lock
{
    private $file;
    private $handle;

//----------------------------------------------->

    public function __construct(File $file)
    {
	$this->file	= $file;
	$this->handle = fopen($file->getPath(), "w");

	if ( empty($this->handle) )
	{
	    throw new IOException("Impossible d\'ouvrir le fichier : $file");
	}
    } 

    /**
     * Tente d'obtenir le lock ce fichier.
     * @return true si le lock est obtenue, false sinon
     */
    public function obtain()
    {
	// TODO je n'arrive pas à faire fonctionner le LOCK_NB (non block script execution)
	return flock($this->handle, LOCK_EX);
    }

    public function __destruct()
    {
	try
	{
	    $this->release();
	}
	catch(Exception $e)
	{}
    }

//----------------------------------------------->

    public function release()
    {
	if ( !empty($this->handle) )
	{
	    try
	    {
		// FIXME l'effacer, ça marche sur toutes les plateformes ?
		$this->file->delete();
		fclose($this->handle);
		unset($this->handle);
	    }
	    catch(Exception $e)
	    {
		throw $e;
	    }
	}
    }

    public function getFile()
    {
	return $this->file;
    }
}
?>
