<?php

interface Map
{
    /**
     * Returns the size of this map.
     */
    public function size();
    
    /**
     * Determines whether the map is empty.
     */
    public function isEmpty();
    
    /**
     * Returns the element for the given key.
     *
     * @param	key	key whose associated value is to be returned.
     *
     * @return 	the found element; null otherwise.
     */
    public function get($key);
    
    /**
     * Put the value at the index key.
     *
     * @param	key	key with which the specified value is to be associated.
     * @param	value	value to be associated with the specified key
     */
    public function put($key, $value);
    
    /**
     * Removes the element for the given key
     *
     * @param	key	key whose mapping is to be removed from the map.
     *
     * @return	the removed element; null if has remove nothing.
     */
    public function remove($key);

    /**
     * Détermine si la map contient la clé donnée
     */
    public function contains($key);

    /**
     * Retourne les clés.
     */
    public function keys();
    
    /**
     * Clear the entire list.
     */
    public function clear();
}
?>
