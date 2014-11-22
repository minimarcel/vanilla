<?php

import('vanilla.util.ArrayList');

/**
 * Une pile
 */
class Stack extends ArrayList
{
    /**
     * Crée une nouvelle pile vide
     */
    function __construct()
    {
	parent::__construct();
    }
    
//-------------------------------------------------------------------------->
    
    /**
     * Retourne et supprime l'élement se trouvant en haut de la pile.
     */
    function pop()
    {
	if ( $this->isEmpty() )
	{
	    return null;
	}

	return $this->remove($this->size() - 1);
    }
    
    /**
     * Regarde sans supprimer l'élement se trouvant en haut de la pile.
     */
    function peek()
    {
	if ( $this->isEmpty() )
	{
	    return null;
	}

	return $this->get($this->size() - 1);
    }

    /**
     * Ajoute un élement en haut de la pile
     */
    function push($e)
    {
	return $this->add($e);
    }
}
?>
