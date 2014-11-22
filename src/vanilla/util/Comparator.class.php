<?php

/**
 * Compare two objects
 */
interface Comparator
{
    /**
     * Return -1, 0 or 1 if the object o1 is respectivly : 
     * lowers than, equals to or greater than the object o2.
     *
     * Becareful : only those three values are expected.
     */ 
    public function compare($o1, $o2);
}
?>
