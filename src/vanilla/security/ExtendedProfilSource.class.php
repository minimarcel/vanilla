<?php

import('vanilla.security.IProfilSource');

/**
 * 
 */
interface ExtendedProfilSource extends IProfilSource
{
    /**
     * Retourne un profil pour ces identifiants de connexion, généralement login and password
     * Utilisé généralement pour les connexion depuis un formulaire par un utilisateur.
     */
    public function getProfilByLogins(Array $logins);

    /**
     * Retourne un profil par son identifiant ou ses identifiants, généralement le login.
     * Utilisé pour les connexion interne, ou via un token, avec simplement l'identifiant sans verifications.
     */
    public function getProfilByIds(Array $identifiants);
}
?>
