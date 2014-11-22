<?php

interface LocaleParserHandler
{
    /**
     * Appellée quand le fichier parsé concerne 
     * une redirection vers une autre.
     */
    public function redirectTo($locale);

    /**
     * Appellé quand une ligne de commentaire est trouvée par le parser.
     */
    public function comment($line);

    /**
     * Appellée quand le fichier parsé concerne la locale suivant
     */
    public function startLocale($code);

    /**
     * Appellé quand la locale actuellement parése est terminée.
     * Est appellé uniquement si startLocale a été appellé.
     */
    public function finish();

    /**
     * Annonce la construction d'une nouvelle collection de propriétés.
     */
    public function startPropertyCollection($name);

    /**
     * Ajoute la propriété donnée à la collection en cours.
     * Est appellé uniquement si startPropertyCollection a été appellé.
     */
    public function addProperty($value);
}
?>
