<?php

import('vanilla.security.Profil');

/**
 * 
 */
interface IProfilSource
{
    /**
     * Desérialize un profil avec l'objet donné !
     */
    public function unserializeProfil($serialized);
}
?>
