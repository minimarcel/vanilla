<?php

import('vanilla.util.AIterator');

/**
 * The class that implement this interface can be iterator, 
 * and to do so can return an iterator.
 */
interface Iterable
{
    public function getIterator();
}
?>
