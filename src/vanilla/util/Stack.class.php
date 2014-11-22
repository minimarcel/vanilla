<?php

import('vanilla.util.ArrayList');

/**
 * A stack.
 * Add the pop, pull, peek, methods to an ArrayList.
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
     * Returns and remove the top element of the stack.
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
     * Just get the top element of the stack, without removing it.
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
     * Push an element of the top of the list.
     */
    function push($e)
    {
        return $this->add($e);
    }
}
?>
