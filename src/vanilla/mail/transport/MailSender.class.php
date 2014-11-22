<?php

import('vanilla.mail.Mail');
import('vanilla.mail.transport.MailTransport');

/**
 * 
 */
interface MailSender
{
    /**
     * Initialise un ou plusieurs envois.
     */
    public function init();

    /**
     * Envoie le mail donné
     * L'implémentation de cette méthode ne doit pas oublier d'appliquer les filtres sur les mails en appellant la méthode 
     * (MailTransport::applyFilter) 
     */
    public function send(Mail $mail);

    /**
     * Finalise le ou les envois executés.
     */
    public function finalize();
}
?>
