<?php

/**
 * 
 */
class Dimension
{
    private $width;
    private $height;

//  ------------------------------------->

    public function __construct($width=0, $height=0)
    {
	$this->width	= ($width < 0 ? 0 : $width);
	$this->height	= ($height < 0 ? 0 : $height);
    }

//  ------------------------------------->

    /**
     *
     */
    public function getWidth()
    {
	return $this->width;
    }

    /**
     *
     */
    public function setWidth($width)
    {
	$this->width = ($width < 0 ? 0 : $width);
    }

    /**
     *
     */
    public function getHeight()
    {
	return $this->height;
    }

    /**
     *
     */
    public function setHeight($height)
    {
	$this->height = ($height < 0 ? 0 : $height);
    }

    public function __toString()
    {
	$w = $this->width;
	$h = $this->height;

	return "#Dimension[width=$w, height=$h]";
    }
}
?>
