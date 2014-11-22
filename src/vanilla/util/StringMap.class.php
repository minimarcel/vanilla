<?php

import('vanilla.util.Map');

/**
 * A map with keys type of string
 * FIXME utilisation du passage par référence pour les valeurs ?
 */
class StringMap implements Map, SerializableObject
{
    /**
     * The array of elements
     */
    public $elements;

//-------------------------------------------------------------------------->
    
    /**
     * Creates a new ArrayList
     */
    public function __construct()
    {
	$this->elements = Array();
    }
    
//-------------------------------------------------------------------------->

    public function getClassPaths()
    {
	$a = Array('vanilla.util.StringMap');
	foreach ( $this->elements as $value )
	{
	    if ( $value instanceof SerializableObject )
	    {
		$a = array_merge($a, $value->getClassPaths());
	    }
	}

	return $a;
    }

//-------------------------------------------------------------------------->

    /**
     * Returns the size of this list.
     */
    public function size()
    {
	return sizeof($this->elements);
    }
    
    /**
     * Determines whether the list is empty.
     */
    public function isEmpty()
    {
	return (sizeof($this->elements) <= 0);
    }
    
    /**
     * Returns the element for the given key.
     *
     * @param	key	key whose associated value is to be returned.
     *
     * @return 	the found element; null otherwise.
     */
    public function get($key)
    {
	if ( $this->contains($key) )
	{
	    return $this->elements[$key];
	}

	return null;
    }

    public function contains($key)
    {
	return array_key_exists($key, $this->elements);
    }
    
    /**
     * Put the value at the index key.
     *
     * @param	key	key with which the specified value is to be associated.
     * @param	value	value to be associated with the specified key
     */
    public function put($key, $value)
    {
	if ( !isset($key) )
	{
	    throw new Exception('The key is null');
	}

	$this->elements[$key] = $value;
    }
    
    /**
     * Removes the element for the given key
     *
     * @param	key	key whose mapping is to be removed from the map.
     *
     * @return	the removed element; null if has remove nothing.
     */
    public function remove($key)
    {
	if ( empty($key) )
	{
	    throw new Exception('The key is null');
	}

	$i = array_search($key, $this->keys());
	if ( $i === false )
	{
	    return null;
	}

	$a1 = array_slice($this->elements, 0, $i);
	$a2 = array_slice($this->elements, $i + 1);

	$e = $this->elements[$key];
	$this->elements = array_merge($a1, $a2);
	return $e;
    }

    public function keys()
    {
	return array_keys($this->elements);
    }
    
    /**
     * Clear the entire list.
     */
    public function clear()
    {
	$this->elements = Array();
    }

    public function duplicate()
    {
	$clone = new StringMap();		
	foreach ( $this->elements as $key => $value )
	{
	    $clone->elements[$key] = $value;
	}

	return $clone;
    }
}
?>
