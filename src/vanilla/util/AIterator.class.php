<?php

/**
 * An iterator 
 * (rename it since PHP implements its own Iterator class)
 */
interface AIterator
{
    public function hasNext();
    public function hasPrevious();
    public function current();
    public function remove();
    public function next();
    public function rewind();
}
?>
