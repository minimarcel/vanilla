<?php

import('vanilla.sql.ResultSet');

/**
 * 
 */
interface Connection
{
    /**
     * Close the connection.
     */
    public function close();
    
    /**
     * Determines whether the connection is closed.
     */
    public function isClosed();
    
    /**
     * Executes the given SQL statement, which returns a single ResultSet object.
     * Returns a ResultSet object that contains the data produced by the given query; never null.
     */
    public function executeQuery($query);
    
    /**
     * Executes the given SQL statement, which may be an INSERT, UPDATE, or DELETE statement 
     * or an SQL statement that returns nothing, such as an SQL DDL statement.
     * Returns Either the row count for INSERT, UPDATE  or DELETE statements, 
     * or 0 for SQL statements that return nothing.
     */
    public function executeUpdate($query);
    
    /**
     * Retunrs the last inserted id.
     */
    public function getLastId();

    /**
     * Starts a new transaction
     */
    public function startTransaction();

    /**
     * Stop a started transaction by a commit
     */
    public function commit();

    /**
     * Stop a started transaction by a rollback
     */
    public function rollback();

    public function setAutoCommit($auto);

    public function setCharset($charset);
}
?>
