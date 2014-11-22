<?php

import('vanilla.gfx.Gfx');

/**
 * 
 */
class Font
{
    const PLAIN	    = 0;
    const BOLD	    = 1;
    const ITALIC    = 2;

//  ------------------------------------->

    private $name;
    private $file;
    private $size;
    private $style;

//  ------------------------------------->

    public function __construct($name, File $file, $size=10, $style=0)
    {
	$this->name	= $name;
	$this->file	= $file;
	$this->size	= $size;
	$this->style	= $style;
    }

//  ------------------------------------->

    public function getName()
    {
	return $this->name;
    }

    public function getFile()
    {
	return $this->file;
    }

    public function getSize()
    {
	return $this->size;	
    }

    public function getStyle()
    {
	return $this->style;	
    }

    public function isItalic()
    {
	return $this->style & self::ITALIC > 0;	
    }

    public function isBold()
    {
	return $this->style & self::BOLD > 0;	
    }

//  ------------------------------------->

    public function deriveFont($size, $style)
    {
	if ( $style == $this->style )
	{
	    return $this->deriveFontBySize($size);
	}

	return self::load($this->name, $size, $style);
    }

    public function deriveFontByStyle($style)
    {
	return self::load($this->name, $this->size, $style);
    }

    public function deriveFontBySize($size)
    {
	return new Font($this->name, $this->file, $size, $this->style);
    }

//  ------------------------------------->

    public static function load($name, $size=10, $style=0)
    {
	return Gfx::loadFont($name, $size, $style);
    }
}
?>
