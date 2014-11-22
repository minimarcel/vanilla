<?php

import('vanilla.security.IProfilSource');

/**
 * 
 */
interface ProfilSource extends IProfilSource
{
    /**
     * Retourne un profil pour ces identifiants de connexion
     */
    public function getProfilByLoginAndPassword($login, $password);
}
?>
