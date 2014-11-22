<?php
interface Profil
{
    /**
     * Retourne l'utlisateur associé à ce profil
     */
    public function getUser();

    /**
     * Détermine si l'utilisateur est dans le rôle donné pour ce profil
     */
    public function isInRole($role); 
    
    /**
     * Retourne un objet qui peut être sérialisé dans la session
     * que le ProfilSource est capable de déserialiser
     */
    public function serializeProfil(); 
}
?>
