<?php

import('vanilla.gfx.Gfx');
import('vanilla.net.HTTP');
import('vanilla.security.captcha.CaptchaHelper');

class CaptchaImageChallenge
{
//  ----------------------------------------------->
    
    private /*Color*/ $textColor;
    private /*Color*/ $backgroundColor;
    private /*Font*/ $font;

//  ----------------------------------------------->

    public function __construct()
    {
	$this->font		= Font::load("sans-serif", 30, FONT::BOLD | FONT::ITALIC);
	$this->textColor	= Color::RGB(0, 0, 0);
	$this->backgroundColor	= Color::RGB(0xFF, 0xFF, 0xFF);
    }

//  ----------------------------------------------->

    public function setBackground(Color $color)
    {
	$this->backgroundColor = $color;
    }

    public function setTextColor(Color $color)
    {
	$this->textColor = $color;
    }

    public function setFont(Font $font)
    {
	$this->font = $font;
    }

//  ----------------------------------------------->

    public function generateForText($text)
    {
	// on calcule les tailles
	$letterSpace	= floor($this->font->getSize() / 8);

	// on calcule la taille de l'image
	$bounds = new ArrayList();
	$w = 0;
	$h = 0;

	$l = strlen($text);
	for ( $i = 0 ; $i < $l ; $i++ )
	{
	    $c = $text[$i];
	    $r = Gfx::getStringBoundingBox($this->font, $c);
	    $w += $r->getWidth() + $letterSpace;
	    $h = max($h, $r->getHeight());
	    
	    $bounds->add($r);
	}

	// on prends une marge de chaque côté
	$margin	= $w*0.1;
	$w += $margin * 2;
	$h += $margin * 2;

	// on crée on image temporaire
	$tmp = Gfx::createImage($w, $h, $this->backgroundColor);
	$gfx = $tmp->getGraphics();

	$gfx->setFont($this->font);
	$gfx->setColor($this->textColor);

	$x = $margin;
	$y = $margin;
	for ( $i = 0 ; $i < $l ; $i++ )
	{
	    $c = $text[$i];
	    $r = $bounds->get($i);
	    $gfx->drawString($c, $x-$r->getLeft(), $y-$r->getTop(), 0);

	    $x += $r->getWidth() + $letterSpace;
	}


	/*
	   Deformation
	   TODO rendre paramétrable les différents brouillages
	*/

	// déformation horizontale en vague

	$img = Gfx::createImage($w, $h, $this->backgroundColor);
	$gfx = $img->getGraphics();

	$pi	    = pi();
	$period	    = ($w / $l);
	$amplitude  = $h / 8;
	$rand	    = rand(0, $pi); 

	for( $i = 0 ; $i < $w ; $i++) 
	{
	    $delta = sin($i * $pi / $period + $rand) * $amplitude;
	    $gfx->drawImageArea($tmp, $i, $delta, 1, $h, $i, 0, 1, $h);
	}

	$gfx = $tmp->getGraphics();
	$gfx->setColor($this->backgroundColor);
	$gfx->fill();
	$gfx->setColor($this->textColor);

	// déformation verticale

	$period	    = $h;
	$amplitude  = $w / $l / 3;
	$rand	    = rand(0, $pi);
	$decalage   = sin($pi/2+$rand)*$amplitude;

	for( $i = 0 ; $i < $h ; $i += 1) 
	{
	    $delta = sin($i * $pi / $period + $rand) * $amplitude - $decalage;
	    $gfx->drawImageArea($img, $delta, $i, $w, 1, 0, $i, $w, 1);
	}

	$img->release();

	/*
	   Lignes parasites
	*/

	$gfx->drawLine(rand(0, $w) - $w/2, 0, rand(0, $w) + $w/2, $h - 1);
	$gfx->drawLine(rand(0, $w) + $w/2, 0, rand(0, $w) - $w/2, $h - 1);

	$gfx->drawLine(0, rand(0, $h) - $h/2, $w - 1, rand(0, $h) + $h/2);
	$gfx->drawLine(0, rand(0, $h) + $h/2, $w - 1, rand(0, $h) - $h/2);

	return $tmp;
    }

    public function generateForEncodedText($encoded)
    {
	return $this->generateForText( CaptchaHelper::decodeCaptcha($encoded) );
    }

//  ----------------------------------------------->
//  Pre-defined methods

    public static function createFrom(/*Color*/ $bgColor=null, /*Color*/ $fgColor=null)
    {
	$challenge = new CaptchaImageChallenge();

	if ( !empty($bgColor) )
	{
	    $challenge->setBackground($bgColor);
	}

	if ( !empty($fgColor) )
	{
	    $challenge->setTextColor($fgColor);
	}

	return $challenge;
    }

    public static function generateOnStdout($encodedText, /*Color*/ $bgColor=null, /*Color*/ $fgColor=null)
    {
	$challenge = self::createFrom($bgColor, $fgColor);
	$img = $challenge->generateForEncodedText($encodedText);

	HTTP::setContentType("image/png");
	Gfx::writeImageToStandardOutput($img, ImageInfo::PNG);

	$img->release();
    } 
}
?>
