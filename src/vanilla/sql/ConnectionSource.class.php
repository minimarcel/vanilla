<?php

import('vanilla.sql.IConnectionSource');

/**
 * 
 */
class ConnectionSource implements IConnectionSource
{
    private $name;
    private $driver;
    private $host;
    private $database;
    private $user;
    private $pwd;
    private $permanent;

    private $autoCommit;

    // TODO pool de connection
    private $connection;

//----------------------------------------------->
    
    function __construct($name, Driver $driver, $host, $database, $user, $pwd, $permanent)
    {
	$this->name	    = $name;
	$this->driver	    = $driver;
	$this->host	    = $host;
	$this->database	    = $database;
	$this->user	    = $user;
	$this->pwd	    = $pwd;
	$this->permanent    = $permanent;
	$this->autoCommit   = true;
    }

    function __destruct()
    {
        if ( !empty($this->connection) )
        {
	    try
	    {
		// FIXME on doit laisser le soin à l'utilisateur de commiter ou rollbacker ses modifications
		// si ça plante il y a de fortes chance qu'il ne veuille pas un commit foireux
		// TODO voir les conséquences de cette modification
		//$this->connection->commit();
		$this->connection->close();
	    }
	    catch(Exception $e)
	    {}

            $this->connection = null;
        }
    }
    
//----------------------------------------------->

    public function getConnection()
    {
	if ( !empty($this->connection) )
	{
	    return $this->connection;
	}

	$this->connection = $this->driver->connect($this->host, $this->database, $this->user, $this->pwd, $this->permanent);
	$this->connection->setAutoCommit($this->autoCommit);
	$this->connection->setCharset('utf8');

	return $this->connection;
    }
    
    public function getName()
    {
	return $this->name;
    }

    /**
     * Défini le mode de commit pour toutes les connections crées
     * TODO transaction isolation
     */
    public function setAutoCommit($auto)
    {
	$this->autoCommit = $auto;
    }
}

?>
