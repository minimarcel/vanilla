<?php

import('vanilla.util.StringMap');
import('vanilla.util.Collections');
import('vanilla.text.css.CSSRuleData');
import('vanilla.text.css.CSSHelper');

class CSSStyleSelector
{
    private /*StringMap*/  $idRules;
    private /*StringMap*/  $classRules;
    private /*StringMap*/  $tagRules;
    private /*ArrayList*/   $universalRules;
    private/*int*/ $ruleCount = 0;

//  ------------------------------------->

    public function __construct()
    {
	$this->idRules		= new StringMap();
	$this->classRules	= new StringMap();
	$this->tagRules		= new StringMap();
	$this->universalRules	= new ArrayList();
    }

//  ------------------------------------->

    public function addRule(CSSStyleRule $rule)
    {
	foreach ( $rule->getSelectors()->elements as $sel )
	{
	    $sub = $sel->getSubSelectorTypeOfId();
	    if ( !empty($sub) )
	    {
		$this->addRuleToSet($this->createRuleData($rule, $sel), $this->idRules, $sub->getValue());
		continue;
	    }

	    $sub = $sel->getSubSelectorTypeOfClass();
	    if ( !empty($sub) )
	    {
		$this->addRuleToSet($this->createRuleData($rule, $sel), $this->classRules, $sub->getValue());
		continue;
	    }

	    $tag = $sel->getTag();
	    if ( !empty($tag) )
	    {
		$this->addRuleToSet($this->createRuleData($rule, $sel), $this->tagRules, $tag);
		continue;
	    }

	    $this->universalRules->add($this->createRuleData($rule, $sel));
	}
    }

    private function addRuleToSet(CSSRuleData $data, StringMap $map, $val)
    {
	$list = $map->get($val);
	if ( empty($list) )
	{
	    $list = new ArrayList();
	    $map->put($val, $list);
	}

	$list->add($data);
    }

    private function createRuleData(CSSStyleRule $rule, CSSSelector $sel)
    {
	return new CSSRuleData($this->ruleCount++, $rule, $sel);
    }

//  ------------------------------------->

    public function styleForElement(DOMElement $element)
    {
	/*
	   On collecte toutes les rules
	   pour cet élément
	*/

	$collectedRules = new ArrayList();

	$id = $element->getAttribute("id");
	if ( !empty($id) )
	{
	    $this->matchRulesForList($collectedRules, $element, $this->idRules->get($id));
	}

	$classes = CSSHelper::getElementClasses($element);
	if ( !empty($classes) )
	{
	    foreach ( $classes as $c )
	    {
		$this->matchRulesForList($collectedRules, $element, $this->classRules->get($c));
	    }
	}

	$this->matchRulesForList($collectedRules, $element, $this->tagRules->get($element->nodeName));
	$this->matchRulesForList($collectedRules, $element, $this->universalRules);

	/*
	   On sort les rules
	   ATTENTION : elles sont triées des règles les moins fortes aux plus fortes
	*/

	Collections::sortList($collectedRules);

	/*
	    TODO parser le inline style ? 
	    TODO faire une déclaration propre
	*/

	$decl = new CSSDeclaration();
	foreach ( $collectedRules->elements as $data )
	{
	    $decl->merge($data->getRule()->getDeclaration());
	}
	    
	return $decl;
    }

    private function matchRulesForList(ArrayList $collectedRules, DOMElement $element, /*ArrayList*/ $list)
    {
	if ( empty($list) || $list->isEmpty() )
	{
	    return;
	}
	
	foreach ( $list->elements as $data )
	{
	    $rule = $data->getRule();

	    // on skip les rules empty
	    $decl = $rule->getDeclaration();
	    if ( empty($decl) )
	    {
		continue;
	    }

	    // on check le selector
	    $sel = $data->getSelector();
	    if ( CSSHelper::checkSelector($element, $sel) )
	    {
		$collectedRules->add($data);
	    }
	}
    }
}
?>
