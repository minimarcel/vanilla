<?php

import('vanilla.util.Map');

/**
 * A map with keys type of string
 */
class StringMap implements Map, SerializableObject
{
    /**
     * The array of elements
     */
    public $elements;

//-------------------------------------------------------------------------->
    
    /**
     * Creates a new string map
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
     * @see Map#size()
     */
    public function size()
    {
        return sizeof($this->elements);
    }
    
    /**
     * @see Map#isEmpty()
     */
    public function isEmpty()
    {
        return (sizeof($this->elements) <= 0);
    }
    
    /**
     * @see Map#get(Object)
     */
    public function get($key)
    {
        if ( $this->contains($key) )
        {
            return $this->elements[$key];
        }

        return null;
    }

    /**
     * @see Map#contains(Object)
     */
    public function contains($key)
    {
        return array_key_exists($key, $this->elements);
    }
    
    /**
     * @see Map#put(Object, Object)
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
     * @see Map#remove(Object)
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

    /**
     * @see Map#keys()
     */
    public function keys()
    {
        return array_keys($this->elements);
    }
    
    /**
     * @see Map#clear()
     */
    public function clear()
    {
        $this->elements = Array();
    }

    /**
     * Clone this list
     */
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
