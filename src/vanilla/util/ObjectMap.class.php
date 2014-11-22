<?php

import('vanilla.util.Map');

/**
 * A map with keys type of object or string
 * FIXME should we use pass objects by reference or value.
 */
class ObjectMap implements Map
{
    /**
     * The array of keys and values
     */
    public $keys;
    public $values;

// -----------------------------------> 
    
    /**
     * Creates a new Map
     */
    function __construct()
    {
        $this->keys     = Array();
        $this->values     = Array();
    }
    
// -----------------------------------> 

    /**
     * @see Map#size()
     */
    public function size()
    {
        return sizeof($this->keys);
    }
    
    /**
     * @see Map#isEmpty()
     */
    public function isEmpty()
    {
        return (sizeof($this->keys) <= 0);
    }
    
    /**
     * @see Map#get(Object)
     */
    public function get($key)
    {
        $i = $this->indexOfKey($key);
        if ( $i < 0 )
        {
            return null;
        }

        return $this->values[$i];
    }
    
    /**
     * @see Map#put(Object, Object)
     */
    public function put($key, $value)
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

        $this->keys[$i]     = $key;
        $this->values[$i]     = $value;
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

        $this->keys     = array_merge($k1, $k2);
        $this->values     = array_merge($v1, $v2);

        return $e;
    }

    /**
     * @see Map#contains(Object)
     */
    public function contains($key)
    {
        return ($this->indexOfKey($key) >= 0);
    }

    /**
     * @see Map#indexOfKey(Object)
     */
    private function indexOfKey($key)
    {
        $i = array_search($key, $this->keys);
        if ( $i === false )
        {
            return -1;
        }

        return $i;
    }

    /**
     * @see Map#keys()
     */
    public function keys()
    {
        return $this->keys;
    }
    
    /**
     * @see Map#clear()
     */
    public function clear()
    {
        $this->keys     = Array();
        $this->values   = Array();
    }
}
?>
