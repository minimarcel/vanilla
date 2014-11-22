<?php

import('vanilla.io.Writer');

/*
 * Ecriture de textes.
 * La gestion du charset (pour les lectures de flux externes) est à la charge de la classe fille.
 */
interface TextWriter extends Writer
{
    /**
     * Ecrit du texte dans le charset en cours vers le charset de destination (pour les écritures dans des flux externes)
     * Les claculs de taille se font avec le charset en cours.
     * Si la taille est inférieur à zéro on écrit la chaine entière, sinon on écrit une portion de la chaîne
     */
    public function writeText($s, $length=-1);
}
?>
