<?php
import('vanilla.security.User');

interface LocalisedUser extends User
{
    public function getPreferedLocaleCode();
}
?>
