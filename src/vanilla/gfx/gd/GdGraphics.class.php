<?php

/**
 * 
 */
class GdGraphics extends Graphics
{
    // la resource de l'image
    private $resource;

    // size
    private $w;
    private $h;
    
    // origine
    private $x = 0;
    private $y = 0;

    // clipping zone
    private $clipX = 0;
    private $clipY = 0;
    private $clipW;
    private $clipH;

    // la couleur courante
    private /*Color*/ $color;
    private /*Font*/ $font;
    private $gdColor;
    private $alphaBlending;

//  ----------------------------------------------->

    public function __construct($resource)
    {
	$this->resource = $resource;
	$this->w = $this->clipW = imagesx($resource);
	$this->h = $this->clipH = imagesy($resource);

	// par défaut la couleur est le noir
	$this->setColor(Color::RGB(0, 0, 0));
	$this->setAlphaBlending(true);
	$this->setFont(Font::load("sans-serif"));
    }

//  ----------------------------------------------->

    public function getResource()
    {
	return $this->resource;
    }

    public function create()
    {
	return new GdGraphics($this->resource);
    }

//  ------------------------------------------------------------>
//  Définition du contexte

    public function getColor()
    {
	return $this->color;
    }

    public function setColor(Color $color)
    {
	if ( !empty($color) )
	{
	    $this->color = $color;
	    $this->gdColor = self::toGdColor($this->resource, $color);
	}
    }

    public function getFont()
    {
	return $this->font;
    }

    public function setFont(Font $font)
    {
	if ( !empty($font) )
	{
	    $this->font = $font;
	}
    }

    public function translate($x, $y)
    {
	$x = max(0, $x);
	$y = max(0, $y);
	$this->x = min($this->w, $x);
	$this->y = min($this->h, $y);
    }

    public function getClipBounds()
    {
	return new Rectangle($this->clipX, $this->clipY, $this->clipW, $this->clipH);
    }

    public function setClip($x, $y, $width, $height)
    {
	// pour l'instant on ne le gère pas
	throw new Exception("Unsupported method : setClip");

	if ( $width <= 0 || $height <= 0 )
	{
	    return false;
	}

	$this->translateCoords($x, $y);

	if ( $x >= $this->w || $y >= $this->h )
	{
	    return false;
	}

	$this->clipX = max(0, $x);
	$this->clipY = max(0, $y);
	$this->clipW = min($this->w, $this->clipX + $width) - $this->clipX;
	$this->clipH = min($this->h, $this->clipY + $height) - $this->clipY;

	// TODO gérer le graphics de cliping
    }

    public function setAlphaBlending($mode)
    {
	$this->alphaBlending = ($mode == true);
	imagealphablending($this->resource, $this->alphaBlending);
    }

    public function isAlphaBlending()
    {
	return $this->alphaBlending;
    }

//  ------------------------------------------------------------>
//  Fonctions de dessin

    public function fill()
    {
	// FIXME le imagefill fonctionne bizarrement, il marche que la première fois avec une couleur transparente
	// alors que le imagefilledrectangle ne marche pas avec la couleur transparente pour la première fois
	imagefilledrectangle($this->resource, 0, 0, $this->w, $this->h, $this->gdColor);
    }

    public function copyArea($x, $y, $width, $height, $px, $py)
    {
	$this->translateCoords($x, $y);
	$this->translateCoords($px, $py);
	imagecopymerge($this->resource, $this->resource, $px, $py, $x, $y, $width, $height, 100);
    }

    public function drawLine($x1, $y1, $x2, $y2)
    {
	$this->translateCoords($x1, $y1);
	$this->translateCoords($x2, $y2);
	imageline($this->resource, $x1, $y1, $x2, $y2, $this->gdColor);
    }

    public function fillRect($x, $y, $width, $height)
    {
	$this->translateCoords($x, $y);
	imagefilledrectangle($this->resource, $x, $y, $x+$width-1, $y+$height-1, $this->gdColor);
    }

    // on surcharge le dessins du rectangle
    public function drawRect($x, $y, $width, $height)
    {
	if ( $width <= 0 || $height <= 0 )
	{
	    return;
	}

	$this->translateCoords($x, $y);
	imagerectangle($this->resource, $x, $y, $x+$width-1, $y+$height-1, $this->gdColor);
    }

    public function drawEllipse($x, $y, $width, $height)
    {
	$this->translateCoords($x, $y);
	imageellipse($this->resource, $x, $y, $width, $height, $this->gdColor);
    }

    public function fillEllipse($x, $y, $width, $height)
    {
	$this->translateCoords($x, $y);
	imagefilledellipse($this->resource, $x, $y, $width, $height, $this->gdColor);
    }
    
    public function drawArc($x, $y, $width, $height, $startAngle, $arcAngle)
    {
	$this->translateCoords($x, $y);
	imagearc($this->resource, $x, $y, $width, $height, $startAngle, $arcAngle, $this->gdColor);
    }

    public function fillArc($x, $y, $width, $height, $startAngle, $arcAngle)
    {
	$this->translateCoords($x, $y);
	imagefilledarc($this->resource, $x, $y, $width, $height, $startAngle, $arcAngle, $this->gdColor);
    }

    public function drawString($str, $x, $y, $angle=0)
    {
	$this->translateCoords($x, $y);
	
	$size = $this->font->getSize();
	$file = $this->font->getFile()->getPath();

	// TODO gérer la valeur en point ou pixel suivant la 
	// gd1 ou gd2

	imagettftext($this->resource, $size, $angle, $x, $y, $this->gdColor, $file, $str);
    }

    public function drawImage(Image $img, $x, $y, $width=-1, $height=-1, /*Color*/ $bgColor=null)
    {
	if ( $width < 0 )
	{
	    $width = $img->getWidth();
	}

	if ( $height < 0 )
	{
	    $height = $img->getHeight();
	}

	if ( !empty($bgColor) )
	{
	    $bg = self::toGdColor($this->resource, $bgColor);
	    $this->fillRect($x, $y, $width, $height);
	}

	$this->translateCoords($x, $y);
	imagecopy($this->resource, $img->getResource(), $x, $y, 0, 0, $width, $height/*, 100*/);
    }

    public function drawImageArea(Image $img, $dx, $dy, $dw, $dh, $sx, $sy, $sw, $sh, /*Color*/ $bgColor=null)
    {
	if ( !empty($bgColor) )
	{
	    $bg = self::toGdColor($this->resource, $bgColor);
	    $this->fillRect($dx, $dy, $dw, $dh);
	}

	$this->translateCoords($dx, $dy);
	imagecopyresampled($this->resource, $img->getResource(), $dx, $dy, $sx, $sy, $dw, $dh, $sw, $sh);
    }

    public function drawPoligonPoints(/*Array*/ $xPoints, /*Array*/ $yPoints, $nbPoints)
    {
	if ( empty($xPoints) || empty($yPoints) || sizeof($xPoints) < $nbPoints || sizeof($yPoints) < $nbPoints )
	{
	    return;
	}

	imagepolygon($this->resource, $this->toPolygonPoints($xPoints, $yyPoints, $nbPoints), $nbPoints, $this->gdColor);
    }

    public function fillPoligonPoints(/*Array*/ $xPoints, /*Array*/ $yPoints, $nbPoints)
    {
	if ( empty($xPoints) || empty($yPoints) || sizeof($xPoints) < $nbPoints || sizeof($yPoints) < $nbPoints )
	{
	    return;
	}

	imagefilledpolygon($this->resource, $this->toPolygonPoints($xPoints, $yyPoints, $nbPoints), $nbPoints, $this->gdColor);
    }

    /**
     * Retourne un objet Color représentant la couleur du pixel au coordonnées (x, y)
     */
    public function getPixel($x, $y)
    {
	$gdColor    = imagecolorat($this->resource, $x, $y);
	$rgba	    = imagecolorsforindex($this->resource, $gdColor);
	$alpha	    = 1 - ($rgba['alpha'] / 127);

	return Color::RGBA($rgba['red'], $rgba['green'], $rgba['blue'], $alpha);
    }

    /**
     * Définit la couleur d'un pixel à la coordonnée (x, y)
     * TODO, FIXME, attention si on est en alpha blending il écrit en transparent ...
     */
    public function setPixel(Color $color, $x, $y)
    {
	imagesetpixel($this->resource, $x, $y, self::toGdColor($this->resource, $color));
    }

//  ------------------------------------------------------------>

    private function translateCoords(&$x, &$y)
    {
	$x += $this->x;
	$y += $this->y;
    }

    private function toPolygonPoints($xPoints, $yPoints, $nbPoints)
    {
	$points = Array();
	for ( $i = 0 ; $i < $nbPoints ; $i++ )
	{
	    $x = $xPoints[$i];
	    $y = $yPoints[$i];

	    $this->translateCoords($x, $y);

	    $points[] = $x;
	    $points[] = $y;
	}

	return $points;
    }

    public static function toGdColor($resource, Color $color)
    {
	$alpha = 1 - $color->getAlpha();
	return imagecolorallocatealpha($resource, $color->getRed(), $color->getGreen(), $color->getBlue(), round($alpha * 127));
    }
}
?>
