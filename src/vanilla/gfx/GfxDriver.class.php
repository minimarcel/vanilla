<?php

import('vanilla.io.File');
import('vanilla.gfx.Image');
import('vanilla.gfx.ImageInfo');

/**
 * 
 */
interface GfxDriver
{
    /**
     * Retourne la version de ce driver
     */
    public function getVersion();

    /**
     * Crée une image. Par défaut le fond est blanc
     */
    public function createImage($width, $height, /*Color */$background=null);

    /**
     * Retourne les informations d'une image depuis un fichier.
     */
    public function getImageInfoFromFile(File $file);

    /**
     * Load une image depuis un fichier
     */
    public function loadImageFromFile(File $file);

    /**
     * Ecrit une image dans un fichier
     */
    public function writeImageToFile(Image $image, File $file, $format);

    /**
     * Ecrit une image sur la sortie standard
     */
    public function writeImageToStandardOutput(Image $image, $format);

    // FIXME doit-on mettre les méthodes pour régler la compression JPG ici ?

    /*
     * Renvoie le rectangle entourant une chaîne pour une font donnée
     */
    public function getStringBoundingBox(Font $font, $str, $angle=0);
}
?>
