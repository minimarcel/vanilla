<?php

import('vanilla.io.Reader');

/*
 * Lecture de textes.
 * La gestion du charset (pour les lectures de flux externes) est à la charge de la classe fille.
 */
interface TextReader extends Reader
{
    // FIXME devrait on rajouter une méthode qui lit cractères par caractères ?
    // Si oui comment être sûr que l'on a bien un cractère complet (UTF-8)

    /**
     * Lit une ligne complète ou un morceau de la ligne.
     * Renvoie false si la fin du fichier est atteinte.
     */
    public function readln($length=-1);

    /**
     * Lit un texte jusqu'à la fin, considère donc les octets lu comme des texte
     */
    public function readTextToEnd();
}
?>
