<?php

import('vanilla.gfx.Font');

/**
 * Permet de récupérer une locale
 */
class FontSource
{
    private $codes;
    private $cache;
    private $webFontsDirectory;
    private $vanillaFontsDirectory;

//  ---------------------------------------->

    function __construct()
    {
	$this->cache = new StringMap();
	$this->codes = new StringMap();

	/*
	   Récupération des répertoires contenant les fonts
	*/

	$dir = File::fromRelativePath('WEB/fonts');
	if ( $dir->exists() && $dir->isDirectory() )
	{
	    $this->webFontsDirectory = $dir;
	}

	$package = LibraryPackage::$packages['fr.vanilla'];
	if ( empty($package) )
	{
	    throw new Exception("Unable to find the fr.vanilla package");
	}

	$dir = new File($package->getAbsolutePath() . "/fonts");
	if ( !$dir->exists() || !$dir->isDirectory() )
	{
	    throw new Exception("Unable to find the fonts directory :" . $dir);
	}

	$this->vanillaFontsDirectory = $dir;
    }

//  ---------------------------------------->

    /**
     *
     */
    public function findFontFileBy($name, $style)
    {
	$name = strtolower(trim($name));
	if ( empty($name) )
	{
	    return null;
	}

	/*
	   A-t-on déjà résolu ce nom ?
	*/

	$code = null;
	if ( $this->codes->contains($name) )
	{
	    $code = $this->codes->get($name);
	}
	else
	{
	    $c = explode("-", $name);
	    foreach ( $c as $p )
	    {
		$code .= strtoupper(substr($p, 0, 1)) . strtolower(substr($p, 1));
	    }

	    $this->codes->put($name, $code);
	}


	/*
	   On déduit le nom du fichier
	*/

	if ( ($style & Font::BOLD) > 0 )
	{
	    $code .= "_Bold";
	}

	if ( ($style & Font::ITALIC) > 0 )
	{
	    $code .= "_Italic";
	}

	$code .= ".ttf";

	/*
	   L'a t-on déjà en cache ?
	*/

	if ( $this->cache->contains($code) )
	{
	    return $this->cache->get($code);
	}

	/*
	   On cherche son fichier
	*/

	$f = $this->findFontFileForCode($code);
	if ( empty($f) )
	{
	    return null;
	}

	$this->cache->put($code, $f);

	return $f;
    }

    
    private function findFontFileForCode($code)
    {
	/*
	   On teste avec le répertoire fonts du site web
	*/

	$f = $this->findFontFileInDirectory($this->webFontsDirectory, $code);
	if ( !empty($f) )
	{
	    return $f;
	}

	return $this->findFontFileInDirectory($this->vanillaFontsDirectory, $code);
    }

    protected function findFontFileInDirectory($dir, $code)
    {
	if ( empty($dir) )
	{
	    return null;
	}

	$f = File::fromChildPath($dir, $code);
	if ( !$f->exists() )
	{
	    return null;
	}

	return $f;
    }
}
?>
