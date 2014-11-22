<?php

import('vanilla.localisation.Language');

/**
 * Permet de charger les langues
 */
interface LanguageSource
{
    /**
     * Retourne un array de langues
     */
    public function getLanguages();

    /**
     * Retourne une langue pour un code donné     
     */
    public function getLanguageForCode($code);

    /**
     * Retourne la langue par défaut.
     */
    public function getDefaultLanguage();
}
?>
