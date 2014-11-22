<?php

import('vanilla.gfx.Image');
import('vanilla.gfx.Color');
import('vanilla.gfx.Rectangle');
import('vanilla.gfx.Font');

/**
 * Contexte graphique. 
 * Permet de dessiner sur des images.
 * Voir Image->getGraphics()
 */
abstract class Graphics
{
    /**
     * Crée un nouveau contexte graphique basé sur celui-là et le renvoie.
     * L'origine sera en 0, 0, et la zone de cliping sera égale à la taille de l'image
     */
    public abstract function create();

    /**
     * Crée un nouveau contexte graphique basé sur celui-ci et le renvoie.
     * L'origine sera en (x, y) et la zone de cliping aura la taille spécifiée par width an height
     * Reviens à appeller créate puis faire une translation et définir une zone de cliping
     */
    public function createWith($x, $y, $width, $height) 
    {
	$g = $this->create();
	if ( empty($g) )
	{
	    return null;
	}

	$g->translate($x, $y);
	$g->setClip(new Rectangle(0, 0, $width, $height));

	return $g;
    }

//  ------------------------------------------------------------>
//  Définition du contexte

    /**
     * Retourne la couleur conrante.
     */
    public abstract function getColor();

    /**
     * Définit la couleur courante.
     */
    public abstract function setColor(Color $color);

    /**
     * Translate l'origine du contexte graphique au point (x, y).
     * Tous les opérations qui vont suivre seront dans ce nouveau système.
     */
    public abstract function translate($x, $y);

    /**
     * Renvoie la font courante
     */
    public abstract function getFont();

    /**
     * Définit la font courante
     */
    public abstract function setFont(Font $font);

    /**
     * Retourne un rectangle définissant la zone de cliping.
     */
    public abstract function getClipBounds();

    /**
     * Définit la zone de cliping.
     * Toutes les opérations de dessins n'auront aucun effet en dehors de la zone.
     */
    public abstract function setClip($x, $y, $width, $height);

    /**
     * Définit si le mode dessin "alpha blending" est activé que le la couleur est mixée avec la couleur
     * déjà définie sur l'image, dans le cas contraire la valeur de la couleur est simplement copiée.
     * Par défaut le mode alpha blending est activé.
     */
    public abstract function setAlphaBlending($mode);

    /**
     * Définit dans quel mode dessin nous sommes.
     */
    public abstract function isAlphaBlending();

//  ------------------------------------------------------------>
//  Fonctions de dessin

    /**
     * Remplit toute l'image avec la couleur courrante
     */
    public abstract function fill();

    /**
     * Recopie la zone définit par le rectangle vers le point p.
     * FIXME dans java ils utilisent une distance et non un point.
     */
    public abstract function copyArea($x, $y, $width, $height, $px, $py);

    /** 
     * Dessine une ligne en utilisant la couleur courrante du point p1 au point p2
     */
    public abstract function drawLine($x1, $y1, $x2, $y2);

    /** 
     * Remplit un rectangle avec la couleur courante.
     */
    public abstract function fillRect($x, $y, $width, $height);

    /** 
     * Dessine les bords d'un rectangle avec la couleur courante.
     */
    public function drawRect($x, $y, $width, $height)
    {
	if ( $width < 0 || $height < 0 )
	{
	    return;
	}

	if ( $height == 0 || $width == 0) 
	{
	    $this->drawLine($x, $y, $x + $width, $y + $height);
	} 
	else 
	{
	    $this->drawLine($x, $y, $x + $width - 1, $y);
	    $this->drawLine($x + $width, $y, $x + $width, $y + $height - 1);
	    $this->drawLine($x + $width, $y + $height, $x + 1, $y + $height);
	    $this->drawLine($x, $y + $height, $x, $y + 1);
	}
    }

    /**
     * Dessine les bord d'une ellipse avec la couleur courrante.
     */
    public abstract function drawEllipse($x, $y, $width, $height);

    /**
     * Dessine une ellipse pleine avec la couleur courrante.
     */
    public abstract function fillEllipse($x, $y, $width, $height);
    
    /**
     * Dessine les bord d'un arc
     */
    public abstract function drawArc($x, $y, $width, $height, $startAngle, $arcAngle);

    /**
     * Dessine et remplit un arc
     */
    public abstract function fillArc($x, $y, $width, $height, $startAngle, $arcAngle);

    // TODO polygon ?

    /** 
     * Dessine le text donné à l'emplacement donné en utilisant la couleur courante et la font courante.
     * Le point (x, y) dans la base line.
     * L'angle donne l'orientation du texte, 0 est horizontal, 90 de bas en haut.
     */
    public abstract function drawString($str, $x, $y, $angle=0);

    /**
     * Renvoie un objet Rectangle contenant les dimensions de la chaîne donnée
     * L'angle donne l'orientation du texte, 0 est horizontal, 90 de bas en haut.
     * Le rectangle correspond aux coordonées si la chaine avait été écrite en (0,0)
     */
    public function getStringBoundingBox($str, $angle=0)
    {
	return Gfx::getStringBoundingBox($this->getFont(), $str, $angle);
    }

    /**
     * Dessine une image à l'emplacement donnée avec la taille donnée.
     * Si la width et négative alors la taille de l'image est inchangée, et de même pour la height.
     * Une couleur de fond peut être spécifiée pour les images transparentes.
     */
    public abstract function drawImage(Image $img, $x, $y, $width=-1, $height=-1, /*Color*/ $bgColor=null);

    /**
     * Copie une partie de l'image définie par le rectangle (sx, sy, sw sh) vers la destination définie par le rectangle
     * (dx, dy, dw, dh).
     */
    public abstract function drawImageArea(Image $img, $dx, $dy, $dw, $dh, $sx, $sy, $sw, $sh, /*Color*/ $bgColor=null);

    /**
     * Dessine les bords d'un polygon avec l'ensemble des points donnés.
     */
    public abstract function drawPoligonPoints(/*Array*/ $xPoints, /*Array*/ $yPoints, $nbPoints);

    /**
     * Remplit un polygon avec l'ensemble des points donnés.
     */
    public abstract function fillPoligonPoints(/*Array*/ $xPoints, /*Array*/ $yPoints, $nbPoints);

    /* TODO
    public function drawPoligon(Poligon $poligon)
    {
	$this->drawPoligon($poligon->getXPoints(), $poligon->getYPoints(), $poligin->getNbPoints());
    }

    public function fillPoligon(Poligon $poligon)
    {
	$this->fillPoligon($poligon->getXPoints(), $poligon->getYPoints(), $poligin->getNbPoints());
    }*/

    /**
     * Retourne un objet Color représentant la couleur du pixel au coordonnées (x, y)
     */
    public abstract function getPixel($x, $y);

    /**
     * Définit la couleur d'un pixel à la coordonnée (x, y)
     */
    public abstract function setPixel(Color $color, $x, $y);
}
?>
