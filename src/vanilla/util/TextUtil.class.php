<?php

import('vanilla.text.xhtml.XHTMLParser');
import('vanilla.util.Stack');

class TextUtil
{
    const WORD_SEPARATORS   = " &\"{([-])+=}<>.?;.:!";

    /**
     * Remplace les accents et caractères non ascci, 
     * soit par la lettre sans l'accent soit par un tiret.
     */
    public static function stripNonAscci($s)
    {
	$code = '';
	$s = self::replaceAccents(strtolower($s));
	$lastCharWasATiret = true; // ne peut démarrer par un tiret

	for ( $i = 0 ; $i < strlen($s) ; $i++ )
	{
	    $c = $s[$i];

	    if ( ($c >= 'a' && $c <= 'z') || 
		($c >= '0' && $c <= '9') || 
		($c == '.') || ($c == '_') )
	    {
		$lastCharWasATiret = ($c == '-');
		$code = "$code$c";
	    }
	    else if ( !$lastCharWasATiret )
	    {
		$lastCharWasATiret = true;
		$code = "$code-";
	    }
	}

	// supprime le dernier tiret
	while ( $code[strlen($code) - 1] == '-' )
	{
	    $code = substr($code, 0, strlen($code) - 1);
	}

	return $code;
    }

    /**
     * Remplace les accents dans une chaîne de caractères
     */
    public static function replaceAccents($s)
    {
	$s = str_replace('é', 'e', $s);
	$s = str_replace('è', 'e', $s);
	$s = str_replace('ë', 'e', $s);
	$s = str_replace('ê', 'e', $s);

	$s = str_replace('à', 'a', $s);
	$s = str_replace('ä', 'a', $s);
	$s = str_replace('â', 'a', $s);

	$s = str_replace('ù', 'u', $s);
	$s = str_replace('ü', 'u', $s);
	$s = str_replace('û', 'u', $s);

	$s = str_replace('î', 'i', $s);
	$s = str_replace('ï', 'i', $s);

	$s = str_replace('ô', 'o', $s);
	$s = str_replace('ö', 'o', $s);

	return $s;
    }

    /**
     * Coupe un texte au nombre de caratères donnés, en conservant les mots.
     * 
     * @param	maxChars	le nombre maximum de caractères
     * @param	finalChars	la terminaison à apposer en fin de chaîne coupée
     */
    public static function cutPlainText($text, $maxChars, $finalChars=' [...]')
    {
	if ( mb_strlen($text) <= $maxChars )
	{
	    return $text; 
	}

        $s = mb_substr($text, 0, $maxChars + 1); // on rajoute 1 pour récupérer le dernier espace

	$found = false;
	for ( $i = mb_strlen($s) - 1; $i > 0 ; $i-- )
	{
	    $c = mb_substr($s, $i, 1);
	    if ( strpos(self::WORD_SEPARATORS, $c) !== false )
	    {
		// TODO ne pas couper après une virgule par ex
		$s = mb_substr($s, 0, $i);
		$found = true;
		break;
	    }
	}

	if ( !$found )
	{
	    // FIXME peut être transigé suivant la taille ?
	    // on n'a pas trouvé de séparateur, on coupe tout !
	    $s = '';
	}

	if ( mb_strlen($s) < mb_strlen($text) )
	{
	    $s .= $finalChars;
	}

	return $s;
    }

    public static function _cutPlainText($text, $maxChars, $finalChars=' [...]')
    {
	$textIso = utf8_decode($text);
	if ( strlen($textIso) <= $maxChars )
	{
	    return $text; 
	}

        $s = substr($textIso, 0, $maxChars + 1); // on rajoute 1 pour récupérer le dernier espace

	$found = false;
	for ( $i = strlen($s) - 1; $i > 0 ; $i-- )
	{
	    if ( strpos(self::WORD_SEPARATORS, $s[$i]) !== false )
	    {
		// TODO ne pas couper après une virgule par ex
		$s = substr($s, 0, $i);
		$found = true;
		break;
	    }
	}

	if ( !$found )
	{
	    // FIXME peut être transigé suivant la taille ?
	    // on n'a pas trouvé de séparateur, on coupe tout !
	    $s = '';
	}

	if ( strlen($s) < strlen($textIso) )
	{
	    $s .= $finalChars;
	}

	return utf8_encode($s);
    }

    /**
     * Coupe un texte html au nombre de caratères donnés, en conservant les mots.
     * 
     * @param	maxChars	le nombre maximum de caractères
     * @param	skipTags	un tableaux de nom de tags en minuscule à enlever
     * @param	finalChars	la terminaison à apposer en fin de chaîne coupée
     */
    public static function cutXHTMLText($html, $maxChars, $skipTags=null, $finalChars=' [...]')
    {
	$result	= "";
	$parser = new XHTMLParser($html);	
	$stack	= new Stack();
	$len	= 0;

	// TODO manage skipTags

	for ( $node = $parser->nextNode() ; !empty($node) ; $node = $parser->nextNode() )
	{
	    if ( $node->getNodeType() == XHTMLNode::TEXT_TYPE )
	    {
		$text = $node->getValue();
		if ( mb_strlen($text) + $len >= $maxChars )
		{
		    $text = self::cutPlainText($text, $maxChars - $len, $finalChars);
		    $result .= $text;
		    break;
		}

		$result .= $text;
		$len += strlen($text);
	    }
	    else 
	    {
		if ( $node->isClosed() && !$node->isEmpty() )
		{
		    // on dépile 
		    while ( !$stack->isEmpty() && $stack->peek()->getNodeName() != $node->getNodeName() )
		    {
			$result .= $stack->pop()->getClosedTag();
		    }

		    if ( !$stack->isEmpty() )
		    {
			// on affiche ce noeud
			$stack->pop();
			$result .= $node;
		    }
		}
		else 
		{
		    $result .= $node;

		    if ( !$node->isEmpty() )
		    {
			// on empile 
			$stack->push($node);
		    }
		}
	    }
	}

	// on dépile les tags restants
	while ( !$stack->isEmpty() )
	{
	    $result .= $stack->pop()->getClosedTag();
	}

	return $result;
    }

    /**
     * Transforme un texte html en texte simple.
     * Supprime les tags non visibles comme les tags script, 
     * remplace les <br /> et les tags de type block par des retours chariots.
     */
    public static function xhtmlToPlainText($html)
    {
	$text	= ""; 
	$parser = new XHTMLParser($html);	

	$breaked = false;
	for ( $node = $parser->nextNode() ; !empty($node) ; $node = $parser->nextNode() )
	{
	    if ( $node->getNodeType() == XHTMLNode::TEXT_TYPE )
	    {
		$text .= $node->getValue();
		$breaked = false; 
	    }
	    else if ( $node->getNodeName() == 'br' || ($node->isBreakingFlow() && !$breaked) )
	    {
		$text .= "\n";
		$breaked = true;
	    }
	}

	// TODO vérifier les espaces en trop

	return $text;
    }

    /**
     * Transforme un texte simple en html.
     * Remplace les retours chariots par des <br /> et remplace les tags spéciaux xml.
     */
    public static function plainTextToXHTML($text)
    {
        $html = strxml($text);
        $html = str_replace("\r\n", '<br />', $html);
        $html = str_replace("\n", '<br />', $html);

        return $html;
    }

    /**
     *	Met en majuscule un nom ou un prénom
     */
    public static function capitalizeName($s)
    {
	return join("-", array_map('ucwords', explode("-", $s)));
    }
}
?>
