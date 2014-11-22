<?php

import('vanilla.util.Comparable');
import('vanilla.util.Comparator');
import('vanilla.util.ArrayList');

class Collections
{
    /**
     * Retourne un comparator qui inverse l'odre du tri
     */
    public static function reverseOrder()
    {
	return new CollectionsReverseComparator();
    }

    public static function arrayContains($array, $e)
    {
	return (self::arrayIndexOf($array, $e) >= 0);
    }

    public static function arrayIndexOf($array, $e)
    {
	$l = sizeof($array);
	for ( $i = 0 ; $i < $l ; $i++ )
	{
	    // TODO utiliser une méthode equals si c'est un objet !
	    $a = $array[$i];
	    if ( $a === $e )
	    {
		return $i;
	    }
	}

	return -1;
    }

    /**
     * 
     */
    public static function sortList(ArrayList $list, $comparator=null)
    {
	if ( !empty($comparator) )
	{
	    //uasort($list->elements, Array($comparator, "compare"));
	    usort($list->elements, Array($comparator, "compare"));
	}
	else
	{
	    //uasort($list->elements, Array('Collections', "compareObjects"));
	    usort($list->elements, Array('Collections', "compareObjects"));
	}
    }

    public static function sortArray(&$array, $comparator=null)
    {
	if ( !empty($comparator) )
	{
	    //uasort($list->elements, Array($comparator, "compare"));
	    usort($array, Array($comparator, "compare"));
	}
	else
	{
	    //uasort($list->elements, Array('Collections', "compareObjects"));
	    usort($array, Array('Collections', "compareObjects"));
	}
    }

    public static function compareObjects($o1, $o2)
    {
	if ( $o1 instanceof Comparable && $o2 instanceof Comparable )
	{
	    return $o1->compareTo($o2);
	}

	if ( $o1 == $o2 )
	{
	    return 0;
	}

	return ($o1 > $o2 ? 1 : -1);
    }

    public static function createComparatorFromCallback($name, $instanceOrClassName=null, $parameters=null)
    {
	$className  = null;
	$instance   = null;
	if ( !empty($instanceOrClassName) )
	{
	    if ( is_string($instanceOrClassName)  )
	    {
		$instance   = null;
		$className  = $instanceOrClassName; 
	    }
	    else
	    {
		$instance   = $instanceOrClassName; 
		$className  = get_class($instanceOrClassName);
	    }
	}

	$callback   = empty($className) ? new ReflectionFunction($name) : new ReflectionMethod($className, $name);
	$comparator = new CollectionsCallbackComparator($callback, $instance, $parameters);

	return $comparator;
    }
}

class CollectionsCallbackComparator implements Comparator
{
    private $parameters;
    private $callback;
    private $instance;

    public function __construct($callback, $instance, $parameters)
    {
	$this->instance	    = $instance;
	$this->callback	    = $callback;
	$this->parameters   = $parameters;
    }

    public function compare($o1, $o2)
    {
	$in	= $this->instance;
	$cb	= $this->callback;
	$p	= Array($o1, $o2);

	if ( !empty($this->parameters) )
	{
	    $p = array_merge($this->parameters, $p);
	}

	if ( $cb instanceof ReflectionMethod )
	{
	    return $cb->invokeArgs($in, $p);
	}
	else
	{
	    return $cb->invokeArgs($p);
	}
    }
}

class CollectionsReverseComparator implements Comparator
{
    public function compare($o1, $o2)
    {
	return Collections::compareObjects($o2, $o1);
    }
}
?>
