<?php

import('vanilla.util.Iterable');

/**
 * An array list
 * FIXME implements Iterable
 */
class ArrayList implements /*Iterable,*/ SerializableObject
{
    /**
     * The array of elements
     */
    public $elements;

// -----------------------------------> 

    /**
     * Creates a new ArrayList
     */
    public function __construct()
    {
        $this->elements = Array();
    }

// -----------------------------------> 

    /**
     * implements the SerializableObject method
     */ 
    public function getClassPaths()
    {
        $a = Array('vanilla.util.ArrayList');
        foreach ( $this->elements as $value )
        {
            if ( $value instanceof SerializableObject )
            {
                $a = array_merge($a, $value->getClassPaths());
            }
        }

        return $a;
    }

// -----------------------------------> 

    /**
     * Set a new array.
     * Becareful : the elements are given as a pointer, 
     * the given array will be modified
     */
    public function setArray(&$elements)
    {
        if ( !empty($elements) )
        {
            $this->elements =& $elements;
        }
    }

// -----------------------------------> 

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
     * Returns the first element of this list
     */
    public function first()
    {
        return $this->get(0);
    }

    /**
     * Returns the last element of this list
     */
    public function last()
    {
        return $this->get($this->size() - 1);
    }

    /**
     * Returns the element at the index i.
     *
     * @param  i the index of the disered element.
     *
     * @return the found element; null otherwise.
     */
    public function get($i)
    {
        if ( $i < 0 || $i >= $this->size() )
        {
            return null;
        }

        return $this->elements[$i];
    }

    /**
     * Set the element e at the index i.
     *
     * @param    i    the index where to set the element
     * @param    e    the element to be set.
     *
     * @return    true if the element has been setted; false otherwise.
     */
    public function set($i, $e)
    {
        if ( $i < 0 || $i >= $this->size() )
        {
            return false;
        }

        $this->elements[$i] = $e;
        return true;
    }

    /**
     * Insert the element e at the index i.
     *
     * @param    i    the index where to insert the element
     * @param    e    the element to be set.
     *
     * @return    true if the element has been inserted; false otherwise.
     */
    public function insert($i, $e)
    {
        if ( $i < 0 || $i > $this->size() )
        {
            return false;
        }

        if ( $i == $this->size() )
        {
            return $this->add($e);
        }

        // compute a new array with the new value
        $elements = Array();
        $l = $this->size();
        $inserted = false;
        for ( $j = 0 ; $j < $l ; )
        {
            if ( ($j == $i) && !$inserted )
            {
                // insert the element
                $elements[] = $e;
                $inserted = true;
            }
            else
            {
                $elements[] = $this->elements[$j++];
            }
        }

        // replace the array by this new one.
        $this->elements =& $elements;

        return true;
    }

    /**
     * Add the given element e.
     *
     * @param   e   the element to be set.
     *
     * @return  true if the element has been setted; false otherwise.
     */
    public function add($e)
    {
        $this->elements[] = $e;
        return true;
    }

    /**
     * Add the given array or array list.
     *
     * @param   a   the array or array list to be added.
     *
     * @return  true if the element has been setted; false otherwise.
     */
    public function addAll($a)
    {
        if ( empty($a) )
        {
            return false;
        }

        if ( $a instanceOf $this )
        {
            $a = $a->elements;
        }

        for ( $i = 0 ; $i < sizeof($a) ; $i++ )
        {
            $this->add($a[$i]);
        }
    }

    /**
     * Removes the element at the index i
     *
     * @param   i   the index of the element to be removed.
     *
     * @return  the removed element; null if has remove nothing.
     */
    public function remove($i)
    {
        if ( $i < 0 || $i >= $this->size() )
        {
            return null;
        }

        // keep the removed element
        $e = $this->elements[$i];

        // compute a new array without the element
        // FIXME do it with array_slice ?
        $elements = Array();
        $l = $this->size();
        for ( $j = 0 ; $j < $l ; $j++ )
        {
            if ( $j != $i )
            {
                $elements[] = $this->elements[$j];
            }
        }

        // replace the array by this new one.
        $this->elements =& $elements;

        return $e;
    }

    /**
     * Search the given value, and remote the first element found
     * @see #indexOf()
     */
    public function removeForValue($value)
    {
        $i = $this->indexOf($value);
        if ( $i > -1 )
        {
            return $this->remove($i);
        }
    }

    /**
     * Returns the first index of the given element.
     *
     * @return  the index of the element if found; -1 if not found.
     */
    public function indexOf($e)
    {
        $l = $this->size();
        for ( $i = 0 ; $i < $l ; $i++ )
        {
            // TODO utiliser une méthode equals si c'est un objet !
            $a = $this->elements[$i];
            if ( $a === $e )
            {
                return $i;
            }
        }

        return -1;
    }

    /**
     * Determines whether the list contains the given object.
     *
     * @return    true if the list contains the given object; false otherwise.
     */
    public function contains($e)
    {
        return ($this->indexOf($e) >= 0);
    }

    /**
     * Clear the entire list.
     */
    public function clear()
    {
        $this->elements = Array();
    }

// -----------------------------------> 

    /**
     * Returns an AIterator
     */
    public function getIterator()
    {
        return new ArrayListIterator($this);
    }

    /**
     * Returns a string representation of this array list
     */
    public function __toString()
    {
        $s = "";
        foreach ( $this->elements as $e )
        {
            $s .= "{" . toString($e) . "}";
        }

        return "#ArrayList[$s]";
    }

// -----------------------------------> 

    /**
     * Creates an array list from the given values
     */
    public static function FromValues(/*parameters*/)
    {
        $parameters = func_get_args();

        $list = new ArrayList();
        $list->setArray($parameters);

        return $list;
    }
}

// -----------------------------------> 

/**
 * The array list _vanilla_ iterator
 */
class ArrayListIterator implements AIterator
{
    private $index;
    private $list;

// -----------------------------------> 

    /**
     * Creates a new ArrayListIterator
     */
    public function __construct($list)
    {
        $this->list = $list;
        $this->index = -1;
    }

// -----------------------------------> 

    public function hasNext()
    {
        return ( ($this->index+1) < $this->list->size() );
    }

    public function hasPrevious()
    {
        return ( ($this->index-1) > 0 );
    }
    
    public function current()
    {
        $this->list->get($this->index);
    }

    public function next()
    {
        if ( $this->hasNext() )
        {
            return $this->list->get(++$this->index);
        }

        $i = $this->index + 1;
        throw new Exception("Index out of bounds : $i > " . $this->list->size());
    }

    public function rewind()
    {
        if ( $this->hasPrevious() )
        {
            return $this->list->get(--$this->index);
        }

        $i = $this->index - 1;
        throw new Exception("Index out of bounds : $i > " . $this->list->size());
    }

    public function remove()
    {
        if ( $this->index < 0 )
        {
            throw new Exception("No element selected");
        }

        if ( $this->list->isEmpty() )
        {
            throw new Exception("No more elements");
        }

        $this->list->remove($this->index--);
    }
}
?>
