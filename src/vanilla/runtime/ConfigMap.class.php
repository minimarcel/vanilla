<?php

import('vanilla.util.ObjectMap');
import('vanilla.util.ArrayList');

/**
 * 
 */
class ConfigMap
{
    private $map;
    // FIXME crée un quite mode qui ne throw pas d'exception en cas de config manqante ?

//  ------------------------------->

    public function __construct()
    {
	$this->map = new ObjectMap();
    }

//  ------------------------------->

    /**
     * Determines if a key exists
     */
    public function exists($key)
    {
	return $this->map->contains($key);
    }

    /**
     * Renvoie une valeur de la config key
     */
    public function get($key)
    {
	if ( $this->exists($key) )
	{
	    return $this->map->get($key)->get(0);
	}

	throw new Exception("Missing config [$key]");
    }

    /**
     * Renvoie la liste de valeur de la config key
     */
    public function getList($key)
    {
	if ( $this->exists($key) )
	{
	    // FIXME return a copy ?
	    return $this->map->get($key);
	}

	throw new Exception("Missing config [$key]");
    }

    /**
     * Définit une valeur pour la config key.
     * Ecrase toutes configs précédement définies, sauf si override est à false.
     * Si c'est un tableau ou un array list, ajoute toutes les valeurs
     */
    public function put(/*string*/ $key, /*mixed*/ $value, $override=true)
    {
	if ( $this->exists($key) && !$override )
	{
	    return $this;
	}

	$v = new ArrayList();
	if ( $value instanceof ArrayList || is_array($value) ) 
	{
	    $v->addAll($value);
	}
	else
	{
	    $v->add($value);
	}

	$this->map->put($key, $v);
	return $this;
    }

    /**
     * Ajoute une valeur à la config key.
     * Si c'est un tableau ou un array list, ajoute toutes les valeurs.
     */
    public function add(/*string*/ $key, /*mixed*/ $value)
    {
	$l = null;
	if ( !$this->exists($key) )
	{
	    $l = new ArrayList();
	    $this->map->put($key, $l);
	}
	else
	{
	    $l = $this->map->get($key);
	}

	$l->add($value);
	return $this;
    }

    public function remove($key)
    {
	$this->remove($key);
	return $this;
    }
}
?>
