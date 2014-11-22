<?php
interface IConnectionSource
{
    public function getConnection();
    public function getName();
    public function setAutoCommit($auto);
}
?>
