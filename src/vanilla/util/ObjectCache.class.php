<?php

import('vanilla.util.StringMap');

/**
 * Maintain a cache by classname, each cache contains a map of object indexed by they keys.
 */
class ObjectCache
{
    /**
     * The array of elements
     */
    public $cache;

//-------------------------------------------------------------------------->
    
    /**
     * Creates a new ObjectCache
     */
    function __construct()
    {
        $this->cache = new StringMap();
    }
    
//-------------------------------------------------------------------------->

    private function keysToString($keys)
    {
        $s = '';
        foreach ( $keys as $key )
        {
            if ( !empty($s) )
            {
                $s = $s . '-';
            }

            $s = $s . $key;
        }

        return $s;
    }

    /**
     * Cahe a new object for the given keys
     */
    public function add($object/*,...$key*/)
    {
        $keys = func_get_args();
        array_shift($keys);

        if ( !empty($object) && !empty($keys) )
        {
            $this->getCacheForObject($object)->put( $this->keysToString($keys) , $object);
        }
    }

    /**
     * Remove the given keys for the cache representing by the classname of the given object.
     */
    public function remove($object/*,...$key*/)
    {
        $keys = func_get_args();
        array_shift($keys);

        if ( !empty($object) && !empty($keys) )
        {
            $this->getCacheForObject($object)->remove($this->keysToString($keys));
        }
    }

    /**
     * Cache an object by specifying the class name, instead of descovering it.
     */
    public function addByClassName($classname, $object/*,...$key*/)
    {
        $keys = func_get_args();
        array_shift($keys);
        array_shift($keys);

        if ( !empty($object) && !empty($keys) && !empty($classname) )
        {
            if ( !is_subclass_of($object, $classname) )
            {
                throw new Exception("The object is not a subclass of $classname");    
            }

            $this->getCacheForClassName($classname)->put( $this->keysToString($keys) , $object);
        }
    }

    /**
     * Retrieve a cached object for the given classname (the classname of the searched object), and the given keys.
     */
    public function get($classname/*,...$keys*/)
    {
        $keys = func_get_args();
        array_shift($keys);

        if ( empty($classname) || empty($keys) )
        {
            return null;
        }

        return $this->getCacheForClassName($classname)->get( $this->keysToString($keys) );
    }

    /**
     * Clear the entire cache.
     */
    public function clean()
    {
        $this->cache->clear();
    }

    /**
     * Clear the cache of the given classname
     */
    public function cleanByClassName($classname)
    {
        $map = $this->getCacheForClassName($classname);
        if ( !empty($map) )
        {
            $map->clear();
        }
    }

//-------------------------------------------------------------------------->

    private function getCacheForObject($object)
    {
        if ( empty($object) )
        {
            return null;
        }

        return $this->getCacheForClassName( get_class($object) );
    }

    private function getCacheForClassName($classname)
    {
        $map = $this->cache->get($classname);
        if ( empty($map) )
        {
            $map = new StringMap();
            $this->cache->put($classname, $map);
        }

        return $map;
    }
}
?>
