<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\databasepress
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress;

trait WPDBHelpers
{
    /**
     * Executes a SQL query and returns the entire SQL result.
     *
     * @see https://developer.wordpress.org/reference/classes/wpdb/get_results/
     * @param string $statement
     * @return array|object|null
     */
    public function getWPDBResults(string $statement)
    {
        global $wpdb;
        return $wpdb->get_results($statement);
        //phpcs:ignore
    }
    /**
     * Executes a SQL query and returns the row from the SQL result.
     *
     * @param string $statement
     * @param int    $row Row to return. Indexed from 0.
     * @see https://developer.wordpress.org/reference/classes/wpdb/get_row/
     * @return array|object|null|void Database query result in format specified by $output or null on failure
     */
    public function getWPDBRow(string $statement, $row = 0)
    {
        global $wpdb;
        return $wpdb->get_row($statement, \OBJECT, $row);
        //phpcs:ignore
    }
    /**
     * Executes a SQL query and returns the column from the SQL result.
     * If the SQL result contains more than one column, the column specified is returned.
     * If $query is null, the specified column from the previous SQL result is returned.
     *
     * @see https://developer.wordpress.org/reference/classes/wpdb/get_col/
     * @param string  $statement
     * @param integer $col
     * @return array|object|null|void Database query result in format specified by $output or null on failure.
     */
    public function getWPDBColumn(string $statement, $col = 0)
    {
        global $wpdb;
        return $wpdb->get_col($statement, $col);
        //phpcs:ignore
    }
    /**
     * Performs a database query, using current database connection.
     *
     * @see https://developer.wordpress.org/reference/classes/wpdb/query/
     * @param string $statement
     * @return int|bool Boolean true for CREATE, ALTER, TRUNCATE and DROP queries. Number of rows affected/selected for all other queries. Boolean false on error.
     */
    public function getWPDBQuery(string $statement)
    {
        global $wpdb;
        return $wpdb->query($statement);
        //phpcs:ignore
    }
    /**
     * Returns the most recent error text generated by MySQL.
     *
     * @return string|bool false when no error.
     */
    public function getLatestWPDBError()
    {
        global $wpdb;
        $errors = $wpdb->last_error;
        if (!empty($errors)) {
            return $wpdb->last_error;
        }
        return \false;
    }
    /**
     * Get the ID of the last insert.
     *
     * @return string|int
     */
    public function getLastInsertId()
    {
        global $wpdb;
        return $wpdb->insert_id;
    }
}