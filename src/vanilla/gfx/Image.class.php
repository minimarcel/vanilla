<?php

import('vanilla.gfx.Dimension');
import('vanilla.gfx.Graphics');

/**
 * 
 */
interface Image
{
    /**
     * Retourne la largeur de cette image
     */
    public function getWidth();

    /**
     * Retourne la hauteur de cette image
     */
    public function getHeight();
    
    /**
     * Retourne un objet size représentant la width et la height de cette image
     */
    public function getSize();

    /**
     * Retourne une instance de cette image redimensionnée
     */
    public function getScaledInstance($destWidth, $destHeight, $srcWidth=-1, $srcHeight=-1, $x=0, $y=0);

    /**
     * Libère les ressources utilisées pour cette image
     */
    public function release();

    /*
     * Retourne un objet graphics permettant de faire des opération sur une image
     */
    public function getGraphics();
}
?>
