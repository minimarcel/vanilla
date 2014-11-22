<?php

/*
 * Permet de comparer deux objets (de même classe de préférence)
 */
interface Comparator
{
    /**
     * Retourne -1, 0 ou 1 si l'objet o1 est inférieur, 
     * égal ou supérieur à l'objet o2
     * Attention : seules ses valeurs sont acceptées.
     */
    public function compare($o1, $o2);
}
?>
