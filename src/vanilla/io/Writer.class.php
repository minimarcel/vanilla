<?php

interface Writer
{
    /**
     * Ecrit une chaîne comme des bytes.
     * La taille ne tient pas compte du charset, elle mesure la taille de la chaîne directement
     * Utiliser un text writer pour des écritures de texte.
     */
    public function write($s, $length=-1);
    public function close();
}
?>
