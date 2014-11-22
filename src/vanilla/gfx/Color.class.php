<?php

/**
 * 
 */
class Color
{
    private $value;

//  ------------------------------------->

    public function __construct($rgba)
    {
	$this->value = $rgba;
    }

//  ------------------------------------->

    public function getRGB()
    {
	return ($this->value & 0xFFFFFF);
    }

    public function getRGBA()
    {
	return $this->value;
    }

    public function getRed()
    {
	return ($this->value >> 16) & 0xFF;
    }

    public function getGreen()
    {
	return ($this->value >> 8) & 0xFF;
    }

    public function getBlue()
    {
	return ($this->value >> 0) & 0xFF;
    }

    /**
     * entre 0 et 1. 0 est une transparence complète et 1 une opacité complète. 
     */
    public function getAlpha()
    {
	return ((($this->value >> 24) & 0xFF) / 255);
    }

//  ------------------------------------->

    public static function RGB($r, $g, $b)
    {
	return self::RGBA($r, $g, $b, 1);
    }

    /**
     * Alpha allant de 0 à 1
     */
    public static function RGBA($r, $g, $b, $a)
    {
	return new Color(((round($a * 255) & 0xFF) << 24) | (($r & 0xFF) << 16) | (($g & 0xFF) << 8) | ($b & 0xFF));	
    }
}
?>
