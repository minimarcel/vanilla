<?php

import('vanilla.gfx.ImageInfo');

/**
 * 
 */
class GdImageInfo implements ImageInfo
{
    private $gdInfo;

//  ----------------------------------------------->

    public function __construct($gdInfo)
    {
	$this->gdInfo = $gdInfo;
    }

//  ----------------------------------------------->

    public function getWidth()
    {
	return $this->gdInfo[0];
    }

    public function getHeight()
    {
	return $this->gdInfo[1];
    }

    public function getSize()
    {
	return new Dimension($this->getWidth(), $this->getHeight());
    }

    public function getFormat()
    {
	switch( $this->gdInfo[2] )
	{
	    case IMAGETYPE_JPEG : return ImageInfo::JPEG;
	    case IMAGETYPE_GIF	: return ImageInfo::GIF;
	    case IMAGETYPE_PNG	: return ImageInfo::PNG;
	}

	return null;
    }

    public function getMimeType()
    {
	return $this->gdInfo["mime"];
    }

    public function getBits()
    {
	return $this->gdInfo["bits"];
    }
}
?>
