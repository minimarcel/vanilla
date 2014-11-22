<?php

/*
 * Permet de comparer l'objet qui implémente cette interface 
 * a un autre objet (de même classe normalement, donc Comparable aussi)
 */
interface Comparable
{
    /**
     * Retourne -1, 0 ou 1 si cet objet est inférieur, 
     * égal ou supérieur à l'objet donné
     */
    public function compareTo(Comparable $o);
}
?>
