<?php
interface Language
{
    /**
     * Retourne le code de cette langue (ex : fr)
     */
    public function getCode();

    /**
     * Retourne le libelle de cette langue tranduit dans cette même langue.
     * Ex : en = English
     *      fr = Français
     */
    public function getDefaultLabel();
}
?>
