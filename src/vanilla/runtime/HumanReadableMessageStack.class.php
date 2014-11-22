<?php

import('vanilla.runtime.HumanReadableMessage');
import('vanilla.runtime.HumanReadableException');
import('vanilla.util.Stack');

/**
 * 
 */
class HumanReadableMessageStack
{
    private static $stack;

//----------------------------------------------->

    private static function getStack()
    {
	if ( empty(self::$stack) )
	{
	    /*
	       Récupération de la stack dans la session
	    */

	    self::$stack = HttpSession::get('vanilla.runtime.HumanReadableMessageStack');
	}

	if ( empty(self::$stack) )
	{
	    /*
	       On en crée une nouvelle 
	    */

	    self::$stack = new Stack();
	}

	return self::$stack;
    }

//----------------------------------------------->

    public static function push(HumanReadableMessage $message)
    {
	self::getStack()->push($message);
	// on la met dans la session ... FIXME ??
	HttpSession::put('vanilla.runtime.HumanReadableMessageStack', self::$stack);
    }

    public static function pushMessage($message, $exception=null)
    {
	$hm = new HumanReadableMessage($message, $exception);
	self::push($hm);

	return $hm;
    }

    public static function isEmpty()
    {
	return self::getStack()->isEmpty();
    }

    /**
     * Récupère le message en haut de la pile
     */
    public static function pop()
    {
	$m = self::getStack()->pop();
	if ( self::isEmpty() )
	{
	    HttpSession::remove('vanilla.runtime.HumanReadableMessageStack');
	}

	return $m;
    }

    /**
     * Récupère le premier message en haut de pile qui correspond au filtre donné
     *
     * @param	sources		liste de sources à filtrer; peut être un tableau, un ArrayList, une string séparée par des ,
     * @param	mustContains	défini si la source du message doit être comprise dans les source données, ou non compris
     * @param	includeOrphans	défini si les orphelins (message sans sources) doivent être retournés
     */
    public static function popFilter(/*(Array or String or ArrayList) */ $sources, $mustContains=true, $includeOrphans=true)
    {
	if ( empty($sources) )
	{
	    $sources = new ArrayList();
	}
	else if ( is_string($sources) )
	{
	    $a = $sources;
	    $sources = new ArrayList();
	    $sources->addAll(explode(",", $a));
	}
	else if ( is_array($sources) )
	{
	    $a = $sources;
	    $sources = new ArrayList();
	    $sources->setArray($a);
	}

	$found = -1; 
	$l = self::getStack()->size();	
	for ( $i = $l - 1 ; $i >= 0 ; $i-- )
	{
	    $m = self::getStack()->get($i);
	    $s = $m->getSource();

	    if ( empty($s) )
	    {
		if ( $includeOrphans )
		{
		    $found = $i;
		    break;
		}

		continue;
	    }
	     
	    if ( $mustContains && $sources->contains($s) )
	    {
		$found = $i;
		break;
	    }

	    if ( !$mustContains && !$sources->contains($s) )
	    {
		$found = $i;
		break;
	    }
	}

	$m = null;
	if ( $found > -1 )
	{
	    $m = self::getStack()->get($found);
	    self::getStack()->remove($found);
	}

	if ( self::isEmpty() )
	{
	    HttpSession::remove('vanilla.runtime.HumanReadableMessageStack');
	}

	return $m;
    }
}
