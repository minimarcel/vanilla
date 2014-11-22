<?php

import('vanilla.util.Map');

/**
 * An map with keys type of object or string
 * FIXME utilisation du passage par référence pour les valeurs ?
 */
class ObjectMap implements Map
{
    /**
     * The array of keys and values
     */
    public $keys;
    public $values;

//-------------------------------------------------------------------------->
    
    /**
     * Creates a new Map
     */
    function __construct()
    {
	$this->keys 	= Array();
	$this->values 	= Array();
    }
    
//-------------------------------------------------------------------------->

    /**
     * Returns the size of this map.
     */
    function size()
    {
	return sizeof($this->keys);
    }
    
    /**
     * Determines whether the map is empty.
     */
    function isEmpty()
    {
	return (sizeof($this->keys) <= 0);
    }
    
    /**
     * Returns the element for the given key.
     *
     * @param	key	key whose associated value is to be returned.
     *
     * @return 	the found element; null otherwise.
     */
    function get($key)
    {
	$i = $this->indexOfKey($key);
	if ( $i < 0 )
	{
	    return null;
	}

	return $this->values[$i];
    }
    
    /**
     * Put the value at the index key.
     *
     * @param	key	key with which the specified value is to be associated.
     * @param	value	value to be associated with the specified key
     */
    function put($key, $value)
    {
	if ( empty($key) )
	{
	    throw new Exception('The key is null');
	}

	$i = $this->indexOfKey($key);
	if ( $i < 0 )
	{
	    $i = sizeof($this->keys);
	}

	$this->keys[$i] 	= $key;
	$this->values[$i] 	= $value;
    }
    
    /**
     * Removes the element for the given key
     *
     * @param	key	key whose mapping is to be removed from the map.
     *
     * @return	the removed element; null if has remove nothing.
     */
    function remove($key)
    {
	if ( empty($key) )
	{
	    throw new Exception('The key is null');
	}

	$i = $this->indexOfKey($key);
	if ( $i < 0 )
	{
	    return null;
	}

	$k1 = array_slice($this->keys, 0, $i);
	$k2 = array_slice($this->keys, $i + 1);

	$v1 = array_slice($this->values, 0, $i);
	$v2 = array_slice($this->values, $i + 1);

	$e = $this->elements[$key];

	$this->keys 	= array_merge($k1, $k2);
	$this->values 	= array_merge($v1, $v2);

	return $e;
    }

    public function contains($key)
    {
	return ($this->indexOfKey($key) >= 0);
    }

    private function indexOfKey($key)
    {
	$i = array_search($key, $this->keys);
	if ( $i === false )
	{
	    return -1;
	}

	return $i;
    }

    public function keys()
    {
	return $this->keys;
    }
    
    /**
     * Clear the entire list.
     */
    public function clear()
    {
	$this->keys 	= Array();
	$this->values 	= Array();
    }
}
?>
