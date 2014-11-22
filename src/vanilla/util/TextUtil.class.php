<?php

import('vanilla.text.xhtml.XHTMLParser');
import('vanilla.util.Stack');

/**
 * Text utilities
 */
class TextUtil
{
    const WORD_SEPARATORS   = " &\"{([-])+=}<>.?;.:!";

    /**
     * Replace the accent-letters and non ascci chars 
     * by the letter without the accent or by the tiret.
     * (A tiret will never be followed by a tiret)
     */
    public static function stripNonAscci($s)
    {
        $code = '';
        $s = self::replaceAccents(strtolower($s));
        $lastCharWasATiret = true; // can't start with a '-'

        for ( $i = 0 ; $i < strlen($s) ; $i++ )
        {
            $c = $s[$i];

            if ( ($c >= 'a' && $c <= 'z')
                  ||  ($c >= '0' && $c <= '9')
                  ||  ($c == '.') || ($c == '_') )
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

        // remove the last tiret
        while ( $code[strlen($code) - 1] == '-' )
        {
            $code = substr($code, 0, strlen($code) - 1);
        }

        return $code;
    }

    /**
     * Replace all accents by the letter without accent.
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
     * Cut a plain text to the given number of chars, 
     * without cutting in the middle of a word.
     * 
     * @param    maxChars    the max number of chars to keep
     * @param    finalChars  the elipsis to append at the end of the cutted phrase
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
                // TODO don't cut after a comma
                $s = mb_substr($s, 0, $i);
                $found = true;
                break;
            }
        }

        if ( !$found )
        {
            // FIXME keep a part of the text according the size ?
            // we didn't find the char, cut everything
            $s = '';
        }

        if ( mb_strlen($s) < mb_strlen($text) )
        {
            $s .= $finalChars;
        }

        return $s;
    }

    /**
     * Cut a plain text by decoding the utf8
     * @deprecated
     */
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
     * Cut an xhtml text to the given number of chars, 
     * without cutting in the middle of a word.
     * 
     * @param    maxChars       the max number of chars to keep
     * @param    skipTags       an array of tags to completly remove/skip (tags must be in lowercase) 
     *                          (not implemented yet)
     * @param    finalChars     the elipsis to append at the end of the cutted phrase
     */
    public static function cutXHTMLText($html, $maxChars, $skipTags=null, $finalChars=' [...]')
    {
        $result    = "";
        $parser = new XHTMLParser($html);    
        $stack    = new Stack();
        $len    = 0;

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
     * Convert an xhtml text into a plain text..
     * 
     * Remove script tags, keep text nodes, and replace the <br /> 
     * and the nodes that break the flow (block nodes) by a \n.
     */
    public static function xhtmlToPlainText($html)
    {
        $text    = ""; 
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

        // TODO check for the additional spaces
        return $text;
    }

    /**
     * Convert a plain text into xhtml.
     * Replace all the \n by a <br /> and escape the xhtml special chars.
     */
    public static function plainTextToXHTML($text)
    {
        $html = strxml($text);
        $html = str_replace("\r\n", '<br />', $html);
        $html = str_replace("\n", '<br />', $html);

        return $html;
    }

    /**
     * Capitlize a common name, a lastname, a firstname ...
     */
    public static function capitalizeName($s)
    {
        return join("-", array_map('ucwords', explode("-", $s)));
    }
}
?>
