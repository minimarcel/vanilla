<?php

import('vanilla.security.Profil');

interface Authorisation
{
    /**
     * Définit si le profil donné est authorisé 
     * suivant les régles définit par l'objet implémentant cette interface
     *
     * Le profil peut être null étant donné qu'un utilisateur 
     * non connecté peut être authorisé
     */
    public function isAuthorized(/*Profil*/ $profil); 
}
?>
