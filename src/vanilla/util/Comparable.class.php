<?php

/**
 * Determines that the class that implements this method can be compare to an other Comparable.
 * Usualy the objects of the same class are compared.
 */
interface Comparable
{
    /**
     * Return -1, 0 or 1 if this object is respectivly : 
     * lowers than, equals to or greater than the given object.
     */
    public function compareTo(Comparable $o);
}
?>
