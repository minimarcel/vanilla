<?php

import('vanilla.io.StreamWriter');
import('vanilla.io.File');
import('vanilla.io.IOException');

class FileWriter extends StreamWriter
{
	private $file;
	private $useLock=true;

// -------------------------------------------------------------------------->

	public function __construct(File $file, $append=false, $charset="UTF-8")
	{
		try
		{
			$exists = $file->exists();
			parent::__construct(fopen($file->getPath(), $append ? "ab" : "wb"), $charset);

			if ( !$exists && $file->exists() )
			{
				$file->chmod(File::$fileMode);
			}
		}
		catch(Exception $e)
		{
			// TODO gérer les causes
			throw new IOException("Impossible d'ouvrir le fichier : ".$file->getPath()." :\n$e");
		}

		$this->file = $file;
	}

// -------------------------------------------------------------------------->

	public function write($s, $length=-1)
	{
		try
		{
			if ( $this->useLock )
			{
				$this->lock();
			}
			
			parent::write($s, $length);
			
			if ( $this->useLock )
			{
				$this->unlock();
			}
		}
		catch(Exception $e)
		{
			$this->unlock();
			throw $e;
		}
	}

	private function lock()
	{
		if ( empty($this->handle) )
		{
			throw new IOException("Impossible de locker le fichier, flux fermé");
		}

		flock($this->handle, LOCK_EX);
	}

	private function unlock()
	{
		try
		{
			if ( !empty($this->handle) )
			{
				flock($this->handle, LOCK_UN);
			}
		}
		catch(Exception $e)
		{
		}
	}
	
	public function isUsingLock()
	{
		return $this->useLock;
	}
	
	public function setUsingLock($usingLock)
	{
		$this->useLock = ($usingLock == true);
	}

	public function getFile()
	{
		return $this->file;
	}

	public function close()
	{
		if ( !empty($this->handle) )
		{
			try
			{
				fclose($this->handle);
			}
			catch(Exception $e)
			{
			}
		}

		parent::close();
	}
}
?>
