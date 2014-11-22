<?php

import('vanilla.gfx.gd.GdGraphics');

/**
 * 
 */
class GdImage implements Image
{
    private $resource;

//  ----------------------------------------------->

    public function __construct($resource)
    {
	$this->resource = $resource;
    }

    public function __destruct()
    {
	$this->release();
    }

//  ----------------------------------------------->

    public function getResource()
    {
	return $this->resource;
    }

    public function getWidth()
    {
	return imagesx($this->resource);
    }

    public function getHeight()
    {
	return imagesy($this->resource);
    }

    public function getSize()
    {
	return new Dimension($this->getWidth(), $this->getHeight());
    }

    public function getScaledInstance($destWidth, $destHeight, $srcWidth=-1, $srcHeight=-1, $x=0, $y=0)
    {
	$dest = Gfx::createImage($destWidth, $destHeight);

	if ( $srcWidth < 0 )
	{
	    $srcWidth	= $this->getWidth();
	}

	if ( $srcHeight < 0 )
	{
	    $srcHeight	= $this->getHeight();
	}

	imagecopyresampled($dest->resource, $this->resource, 0, 0, $x, $y, $destWidth, $destHeight, $srcWidth, $srcHeight);

	return $dest;
    }

    public function getGraphics()
    {
	return new GdGraphics($this->resource);
    }

    public function release()
    {
	if ( isset($this->resource) )
	{
	    imagedestroy($this->resource);
	    $this->resource = null;
	}
    }
}
?>
