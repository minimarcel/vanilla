<?php

import('vanilla.text.json.JSONPropertyBag');
import('vanilla.text.json.JSONPropertyVal');

/**
 * The remplacement of JSONSerializable 
 * that may not be used anymore in PHP 5.4
 * @author Nicolas DOUILLET
 */
interface VJSONSerializable
{
    /**
     * Retourne un JSONPropertyValue 
     * contenant l'objet sérialisé
     */
    public function toJSON(); 
}
?>
