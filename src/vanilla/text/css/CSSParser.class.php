<?php

import("vanilla.text.css.CSSTokenizer");
import("vanilla.text.css.CSSStyleRule");

class CSSParser
{
    const SELECTOR_START	= 0;
    const SELECTOR_CONTINUE	= 1;
    const SELECTOR_MATCH	= 2;
    const SELECTOR_RELATION	= 3;
    const DECLARATION_START	= 4;
    const PROPERTY_NAME    	= 5;
    const PROPERTY_VALUE    	= 6;
    const DECLARATION_END	= 7;

    // gestion des erreurs
    const SELECTOR_ERROR	= 10;
    const DECLARATION_ERROR	= 11;

//  ------------------------------------------------->

    private $tokenizer;
    private $rule;
    private $state;

    private $selector;
    private $match;
    private $relation;
    private $propName;
    private $propValue;
    private $propImportant;

    private $startIndex;

    // config
    private $returningEmptyDeclarations;

//  ------------------------------------------------->

    public function __construct($s)
    {
	$this->tokenizer = new CSSTokenizer($s);
    }

//  ------------------------------------------------->

    public function setReturningEmptyDeclarations($returningEmptyDeclarations)
    {
	$this->returningEmptyDeclarations = ($returningEmptyDeclarations == true);
    }

    public function isReturningEmptyDeclarations()
    {
	return $this->returningEmptyDeclarations;
    }

//  ------------------------------------------------->

    /**
     * Renvoie une liste de rule
     */
    public function hasNextRule()
    {
	// on boucle jusqu'à avoir une rule valide
	while ( true )
	{
	    try
	    {
		$this->parseNextRule();
	    }
	    catch(Exception $e)
	    {
		$s = substr($this->tokenizer->getString(), $this->startIndex, $this->tokenizer->getIndex());
		warning("Error while parsing css string [$s]", $e);
		continue;
	    }

	    return ( !empty($this->rule) );
	}
    }

    public function getRule()
    {
	return $this->rule;
    }

    private function parseNextRule()
    {
	$this->rule		= null;
	$this->selector		= null;
	$this->match		= null;
	$this->relation		= null;
	$this->propName		= null;
	$this->propValue	= null;
	$this->state		= self::SELECTOR_START;
	$this->startIndex	= $this->tokenizer->getIndex();

	if ( !$this->tokenizer->hasNextToken() )
	{
	    return;
	}

	while ( true )
	{
	    $tok = $this->tokenizer->getToken();
	    switch ( $this->state)
	    {
		case self::SELECTOR_START  : 
		{
		    $this->state = $this->selectorStart($tok);
		    break;
		}

		case self::SELECTOR_CONTINUE :
		{
		    $this->state = $this->selectorContinue($tok);
		    break;
		}
		
		case self::SELECTOR_MATCH :
		{
		    $this->state = $this->selectorMatch($tok);
		    break;
		}

		case self::SELECTOR_RELATION :
		{
		    $this->state = $this->selectorStart($tok, true);
		    break;
		}

		case self::SELECTOR_ERROR :
		{
		    $this->state = $this->selectorError($tok);
		    break;
		}
		
		case self::DECLARATION_START :
		{
		    $this->state = $this->declarationStart($tok);
		    break;
		}

		case self::PROPERTY_NAME : 
		{
		    $this->state = $this->propertyName($tok);
		    break;
		}

		case self::PROPERTY_VALUE :
		{
		    $this->state = $this->propertyValue($tok);
		    break;
		}

		case self::DECLARATION_ERROR :
		{
		    $this->state = $this->declarationError($tok);
		    break;
		}
	    }

	    if ( $this->state == self::DECLARATION_END )
	    {
		break;
	    }

	    if ( !$this->tokenizer->hasNextToken() )
	    {
		throw new Exception("Unexcepted end of string");
	    }
	}

	if ( empty($this->rule) )
	{
	    return;
	}

	if ( $this->rule->getSelectors()->isEmpty() )
	{
	    throw new Exception("No selectors");
	}

	$decl = $this->rule->getDeclaration();
	if ( empty($decl) && !$this->returningEmptyDeclarations )
	{
	    throw new Exception("No déclaration");
	}
    }

//  ---------------------------------------->
//  States

    /**
     * Début d'un selector, on a affaire soit à une déclaration d'un match
     * ou d'un tag
     * Le selector start indique si on est juste après une relation ou non
     */
    private function selectorStart($tok, $relation=false)
    {
	if ( $tok->special )
	{
	    switch($tok->value)
	    {
		// '#'
		case CSSToken::SELECTOR_ID : 
		// '.'
		case CSSToken::SELECTOR_CLASS :
		// ':'
		case CSSToken::SELECTOR_PSEUDO :
		{
		    $this->createSelector("", $relation);
		    $this->match = $this->tokenToMatch($tok);
		    return self::SELECTOR_MATCH;
		}

		// '{'
		case CSSToken::START_DECLARATION :
		{
		    // on fait comme-ci on était en error
		    return selectorError($tok);
		}
	    }

	    return self::SELECTOR_ERROR;
	}

	$this->createSelector($tok->value, $relation);
	return self::SELECTOR_CONTINUE;
    }

    /**
     * On est dans l'attente soit d'une fin de selector , ou {
     * ou d'une relation ou d'un match
     */
    private function selectorContinue($tok)
    {
	if ( $tok->special )
	{
	    switch($tok->value)
	    {
		// ','
		case CSSToken::SELECTOR_SEPARATOR :
		{
		    $this->rule->addSelector($this->selector);
		    $this->selector = null;
		    return self::SELECTOR_START;
		}

		// '{'
		case CSSToken::START_DECLARATION :
		{
		    $this->rule->addSelector($this->selector);
		    $this->selector = null;
		    return self::DECLARATION_START;
		}

		// '#'
		case CSSToken::SELECTOR_ID :
		// '.'
		case CSSToken::SELECTOR_CLASS :
		// ':'
		case CSSToken::SELECTOR_PSEUDO :
		{
		    $this->match = $this->tokenToMatch($tok);
		    return self::SELECTOR_MATCH;
		}

		// '>'
		case CSSToken::SELECTOR_CHILD :
		// ' '
		case CSSToken::SELECTOR_DESCENDANT :
		// '+'
		case CSSToken::SELECTOR_DIRECT_ADJACENT :
		// '~'
		case CSSToken::SELECTOR_INDIRECT_ADJACENT :
		{
		    $this->relation = $this->tokenToRelation($tok);
		    return self::SELECTOR_RELATION;
		}
	    }
	}

	return self::SELECTOR_ERROR;
    }

    /**
     * On est dans l'attente d'une valeur
     */
    private function selectorMatch($tok)
    {
	if ( $tok->special )
	{
	    return self::SELECTOR_ERROR;
	}

	$sub = new CSSSubSelector($tok->value, $this->match);
	$this->selector->addSubSelector($sub);
	$this->match = null;

	return self::SELECTOR_CONTINUE;
    }

    /**
     * Gestion des erreurs dans un selector, 
     * on passe le selector jusqu'à { ou ,
     * Si aucun sélector on va jusqu'à }
     */
    private function selectorError($tok)
    {
	if ( !$tok->special )
	{
	    return self::SELECTOR_ERROR;
	}

	switch($tok->value)
	{
	    case CSSToken::START_DECLARATION :
	    {
		if ( empty($this->rule) )
		{
		    return self::SELECTOR_ERROR;
		}

		if ( $this->rule->getSelectors()->isEmpty() )
		{
		    return self::SELECTOR_ERROR;
		}

		return self::DECLARATION_START;
	    }

	    case CSSToken::SELECTOR_SEPARATOR :
	    {
		return self::SELECTOR_START;
	    }

	    case CSSToken::END_DECLARATION :
	    {
		throw new Exception("Invalid selector");
	    }
	}

	return self::SELECTOR_ERROR;
    }

    /**
     * On est dans l'attente d'une property on d'une fin de déclaration
     */
    private function declarationStart($tok)
    {
	if ( $tok->special )
	{
	    if ( $tok->value == CSSToken::END_DECLARATION )
	    {
		return self::DECLARATION_END;	
	    }

	    return self::DECLARATION_ERROR;
	}

	$this->propName = $tok->value;
	return self::PROPERTY_NAME;
    }

    /**
     * On est dans l'attente d'un ':'
     */
    private function propertyName($tok)
    {
	if ( !$tok->special || $tok->value != CSSToken::PROPERTY_SEPARATOR )
	{
	    return self::DECLARATION_ERROR;
	}

	$this->propValue	= "";
	$this->propImportant	= false;
	return self::PROPERTY_VALUE;
    }

    /**
     * On est dans l'attente d'une valeur ou d'un } ou d'un ;
     */
    private function propertyValue($tok)
    {
	if ( $tok->special )
	{
	    switch($tok->value)
	    {
		case CSSToken::END_INSTRUCTION : 
		{
		    $this->createProperty();
		    return self::DECLARATION_START;
		}

		case CSSToken::END_DECLARATION :
		{
		    $this->createProperty();
		    return self::DECLARATION_END;
		}

		case CSSToken::START_DECLARATION :
		case CSSToken::PROPERTY_SEPARATOR :
		case CSSToken::SELECTOR_INDIRECT_ADJACENT :
		{
		    return self::DECLARATION_ERROR;
		}
	    }
	}

	if ( $tok->value == "!important" )
	{
	    $this->propImportant = true;
	}
	else
	{
	    $this->propValue .= $tok->value;
	}

	return self::PROPERTY_VALUE;
    }

    /** 
     * On essaye soit d'aller à la prochaine property donc d'avoir un ;
     * soit d'aller à la fin de la déclaration
     */
    private function declarationError($tok)
    {
	if ( $tok->special )
	{
	    switch($tok->value)
	    {
		case CSSToken::END_INSTRUCTION : 
		{
		    return self::DECLARATION_START;
		}

		case CSSToken::END_DECLARATION :
		{
		    return self::DECLARATION_END;
		}
	    }
	}

	return self::DECLARATION_ERROR;
    }

//  ---------------------------------------->

    private function createSelector($tag, $relation=false)
    {
	if ( !$relation && empty($this->rule) )
	{
	    $this->rule = new CSSStyleRule();
	}

	$sel = new CSSSelector($tag);
	if ( $relation )
	{
	    $sel->setTagHistory($this->selector, $this->relation);
	    $this->relation = null;
	}

	$this->selector = $sel;
    }

    private function tokenToMatch($tok)
    {
	switch($tok->value)
	{
	    case CSSToken::SELECTOR_ID	    :	return CSSSubSelector::MATCH_ID;
	    case CSSToken::SELECTOR_CLASS   :	return CSSSubSelector::MATCH_CLASS;
	    case CSSToken::SELECTOR_PSEUDO  :	return CSSSubSelector::MATCH_PSEUDO_CLASS;
	}

	return CSSSubSelector::MATCH_NONE;
    }

    private function tokenToRelation($tok)
    {
	switch($tok->value)
	{
	    case CSSToken::SELECTOR_CHILD		: return CSSSelector::RELATION_CHILD;
	    case CSSToken::SELECTOR_DIRECT_ADJACENT	: return CSSSelector::RELATION_DIRECT_ADJACENT;
	    case CSSToken::SELECTOR_INDIRECT_ADJACENT	: return CSSSelector::RELATION_INDIRECT_ADJACENT;
	}

	return CSSSelector::RELATION_DESCENDANT;
    }

    private function createProperty()
    {
	$decl = $this->rule->getDeclaration();
	if ( empty($decl) )
	{
	    $decl = new CSSDeclaration();
	    $this->rule->setDeclaration($decl);
	}

	$prop = new CSSProperty(trim($this->propName), trim($this->propValue), $this->propImportant);
	$decl->addProperty($prop);
    }

//  ---------------------------------------->

    /**
     * Retourne un tableau de sélector
     */
    public static function parseSelector($s)
    {
	$parser = new CSSParser("$s{}");
	$parser->setReturningEmptyDeclarations(true);

	if ( !$parser->hasNextRule() )
	{
	    return null;
	}
	
	return $parser->getRule()->getSelectors();
    }
}
?>
