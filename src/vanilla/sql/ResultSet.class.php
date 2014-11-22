<?php

/**
 * 
 */
interface ResultSet
{
    /**
     * Close the ResultSet.
     */
    public function close();
    
    /**
     * Determines whether the ResultSet is closed.
     */
    public function isClosed();
    
//-------------------------------------------------------------------------->

    /**
     * Fetch the next row.
     *
     * @return	return true if has more rows; false otherwise.
     */
    public function next();
    
    /**
     * Returns the number of rows.
     */
    public function nbRows();
    
    /**
     * Returns the name of the column i.
     *
     * @param	i	the index of the column.
     */
    public function getName($i);
    
//-------------------------------------------------------------------------->

    /**
     * Returns the value of the row i.
     *
     * @param	i	the index of the column, or the column name.
     */
    public function get($i);
    
    /**
     * Returns the string value of the row i.
     *
     * @param	i	the index of the column, or the column name.
     */
    public function getString($i);
    
    
    /**
     * Returns the int value of the row i.
     *
     * @param	i	the index of the column, or the column name.
     */
    public function getInt($i);
    
    /**
     * Returns the float value of the row i.
     *
     * @param	i	the index of the column, or the column name.
     */
    public function getFloat($i);
    
    /**
     * Returns the double value of the row i.
     *
     * @param	i	the index of the column, or the column name.
     */
    public function getDouble($i);
    
    /**
     * Returns the float value of the row i.
     *
     * @param	i	the index of the column, or the column name.
     */
    public function getBoolean($i);

    /**
     * Returns the date value of the row i.
     *
     * @param	i	the index of the column, or the column name.
     */
    public function getDate($i);

    /**
     * Returns the timestamp value of the row i.
     *
     * @param	i	the index of the column, or the column name.
     */
    public function getTimestamp($i);
}

?>
