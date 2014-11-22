<?php

import('vanilla.util.Comparable');
import('vanilla.util.Comparator');
import('vanilla.util.ArrayList');

/**
 * Tools to manipulate collections
 */
class Collections
{
    /**
     * Returns a comparator that will inverse the order of each element in a collection
     * @see #sortList
     */
    public static function reverseOrder()
    {
        return new CollectionsReverseComparator();
    }

    /**
     * Determines whether an array contains a given element
     * @see #arrayIndexOf(array, int)
     */
    public static function arrayContains($array, $e)
    {
        return (self::arrayIndexOf($array, $e) >= 0);
    }

    /**
     * Returns the index of a given element into the given array
     */
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
     * Sort a list.
     * Use the default comparator if no comparator defined.
     * @see #compareObjects(object, object)
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

    /**
     * Sort an array
     * Use the default comparator if no comparator defined.
     * @see #compareObjects(object, object)
     */
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

    /**
     * Compare two objects.
     * This is the default comparator function.
     *
     * If the two objects are instance of Comparable, the compare method is invoked.
     * Otherwise try to compare with the ==, > and < comparison operators.
     */
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

    /**
     * Creates a comparator from a given callback.
     *
     * @param   name                    the name of the function/method
     * @param   instanceOrClassName     if the callback is a method, the classname or the instance
     * @param   parameter               a list of parameters to give back to the callback
     *                                  the parameters will be prepend to the two objects to compare
     */
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

/**
 * The collection callback comparator
 * @see Collections#createComparatorFromCallback(name, string|object, array)
 */
class CollectionsCallbackComparator implements Comparator
{
    private $parameters;
    private $callback;
    private $instance;

    public function __construct($callback, $instance, $parameters)
    {
        $this->instance     = $instance;
        $this->callback     = $callback;
        $this->parameters   = $parameters;
    }

    public function compare($o1, $o2)
    {
        $in    = $this->instance;
        $cb    = $this->callback;
        $p    = Array($o1, $o2);

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

/**
 * The reverse collection comparator
 */
class CollectionsReverseComparator implements Comparator
{
    public function compare($o1, $o2)
    {
        return Collections::compareObjects($o2, $o1);
    }
}
?>
