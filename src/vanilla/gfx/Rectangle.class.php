<?php

import('vanilla.gfx.Dimension');
import('vanilla.gfx.Point');

/**
 * 
 */
class Rectangle
{
    private $x;
    private $y;
    private $width;
    private $height;

//  ------------------------------------->

    public function __construct($x=0, $y=0, $width=0, $height=0)
    {
	$this->x	= $x;
	$this->y	= $y;
	$this->width	= ($width < 0 ? 0 : $width);
	$this->height	= ($height < 0 ? 0 : $height);
    }

//  ------------------------------------->

    public function getWidth()
    {
	return $this->width;
    }

    public function setWidth($width)
    {
	$this->width = ($width < 0 ? 0 : $width);
    }

    public function getHeight()
    {
	return $this->height;
    }

    public function setHeight($height)
    {
	$this->height = ($height < 0 ? 0 : $height);
    }

    public function getX()
    {
	return $this->x;
    }

    public function setX($x)
    {
	$this->x = $x;
    }

    public function getY()
    {
	return $this->y;
    }

    public function setY($y)
    {
	$this->y = $y;
    }

    public function getSize()
    {
	return new Dimension($this->width, $this->height);
    }

    public function setSize(Dimension $size)
    {
	$this->width	= $size->getWidth();
	$this->height	= $size->getHeight();
    }

    public function getPoint()
    {
	return new Point($this->x, $this->y);
    }

    public function setPoint(Point $p)
    {
	$this->x = $p->getX();
	$this->y = $y->getY();
    }

    public function getLeft()
    {
	return $this->x;
    }

    public function getTop()
    {
	return $this->y;
    }

    public function getRight()
    {
	return $this->x + $this->width;
    }

    public function getBottom()
    {
	return $this->y + $this->height;
    }

//  ----------------------------------------------->

    /**
     * Définit si ce rectangle intersect le rectangle passé en paramètres
     */
    public function intersect(Rectangle $rectangle)
    {
	$tw = $this->width;
	$th = $this->height;
	$rw = $rectangle->width;
	$rh = $rectangle->height;

	if ( $rw <= 0 || $rh <= 0 || $tw <= 0 || $th <= 0 )
	{
	    return false;
	}

	$tx = $this->x;
	$ty = $this->y;
	$rx = $rectangle->x;
	$ry = $rectangle->y;

	$rw += $rx;
	$rh += $ry;
	$tw += $tx;
	$th += $ty;

	return (($rw < $rx || $rw > $tx) &&
		($rh < $ry || $rh > $ty) &&
		($tw < $tx || $tw > $rx) &&
		($th < $ty || $th > $ry));
    }

    /**
     * Retourne un rectangle correspondant 
     * à l'intersection de ce rectangle et de celui passé en paramètres.
     * FIXME si il n'u a pas d'intersection ?
     */
    public function intersection(Rectangle $rectangle)
    {
	$tx1 = $this->x;
	$ty1 = $this->y;
	$rx1 = $rectangle->x;
	$ry1 = $rectangle->y;

	$tx2 = $tx1; $tx2 += $this->width;
	$ty2 = $ty1; $ty2 += $this->height;
	$rx2 = $rx1; $rx2 += $rectangle->width;
	$ry2 = $ry1; $ry2 += $rectangle->height;

	if ($tx1 < $rx1) $tx1 = $rx1;
	if ($ty1 < $ry1) $ty1 = $ry1;
	if ($tx2 > $rx2) $tx2 = $rx2;
	if ($ty2 > $ry2) $ty2 = $ry2;

	$tx2 -= $tx1;
	$ty2 -= $ty1;

	// TODO gérer le cas ou il n'y a pas d'intersection
	return new Rectangle($tx1, $ty1, $tx2, $ty2);
    }

    public function containsPoint(Point $p)
    {
	return $this->contains($p->getX(), $p->getY());
    }

    public function containsPointCoords($x, $y)
    {
	// TODO
    }

    public function __toString()
    {
	$x = $this->x;
	$y = $this->y;
	$w = $this->width;
	$h = $this->height;

	return "#Rectangle[x=$x, y=$y, width=$w, height=$h]";
    }
}
?>
