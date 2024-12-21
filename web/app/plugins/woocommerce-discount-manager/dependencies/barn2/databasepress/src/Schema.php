<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\Databasepress
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\Schema\CreateTable;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\Schema\AlterTable;
class Schema
{
    use WPDBHelpers;
    /**
     * Connection instnace.
     *
     * @var Connection
     */
    protected $connection;
    /**
     * Tables list.
     *
     * @var array
     */
    protected $tableList;
    /**
     * Database name.
     *
     * @var string
     */
    protected $currentDatabase;
    /**
     * List of columns.
     *
     * @var array
     */
    protected $columns = [];
    /**
     * Constructor
     *
     * @param Database $connection Connection.
     */
    public function __construct(Database $connection)
    {
        $this->connection = $connection;
    }
    /**
     * Get the name of the currently used database
     *
     * @return  string
     * @throws \Exception
     */
    public function getCurrentDatabase()
    {
        if ($this->currentDatabase === null) {
            $this->currentDatabase = $this->connection->getConnection()->dbname;
        }
        return $this->currentDatabase;
    }
    /**
     * Check if the specified table exists
     *
     * @param   string  $table Table name
     * @param   boolean $clear (optional) Refresh table list
     *
     * @return  boolean
     * @throws \Exception
     */
    public function hasTable(string $table, bool $clear = \false) : bool
    {
        $list = $this->getTables($clear);
        return \in_array($table, $list, \true);
    }
    /**
     * Get a list with all tables that belong to the currently used database
     *
     * @param   boolean $clear (optional) Refresh table list
     *
     * @return  string[]
     * @throws \Exception
     */
    public function getTables(bool $clear = \false) : array
    {
        if ($clear) {
            $this->tableList = null;
        }
        if ($this->tableList === null) {
            $compiler = $this->connection->schemaCompiler();
            $database = $this->getCurrentDatabase();
            $sql = $compiler->getTables($database);
            $results = $this->connection->query($sql['sql'], $sql['params'])->all();
            $this->tableList = [];
            foreach ($results as $result) {
                $name = isset($result->TABLE_NAME) ? $result->TABLE_NAME : $result->table_name;
                $this->tableList[] = $name;
            }
        }
        return $this->tableList;
    }
    /**
     * Get a list with all columns that belong to the specified table
     *
     * @param string  $table
     * @param boolean $clear (optional) Refresh column list
     * @param boolean $names (optional) Return only the column names
     *
     * @return false|string[]
     * @throws \Exception
     */
    public function getColumns(string $table, bool $clear = \false, bool $names = \true)
    {
        if ($clear) {
            unset($this->columns[$table]);
        }
        if (!$this->hasTable($table, $clear)) {
            return \false;
        }
        if (!isset($this->columns[$table])) {
            $compiler = $this->connection->schemaCompiler();
            $database = $this->getCurrentDatabase();
            $sql = $compiler->getColumns($database, $table);
            $results = $this->connection->query($sql['sql'], $sql['params'])->all();
            $columns = [];
            foreach ($results as $ord => &$col) {
                $columns[$col->name] = ['name' => $col->name, 'type' => $col->type];
            }
            $this->columns[$table] = $columns;
        }
        return $names ? \array_keys($this->columns[$table]) : $this->columns[$table];
    }
    /**
     * Creates a new table
     *
     * @param   string   $table Table name
     * @param   callable $callback A callback that will define table's fields and indexes
     * @throws  WPDBException
     */
    public function create(string $table, callable $callback)
    {
        $compiler = $this->connection->schemaCompiler();
        $schema = new CreateTable($table);
        $callback($schema);
        $response = \false;
        foreach ($compiler->create($schema) as $result) {
            $query = $this->connection->command($result['sql'], $result['params']);
            $response = $this->getWPDBQuery($query);
            if (!$response && !empty($this->getLatestWPDBError())) {
                throw new WPDBException($this->getLatestWPDBError());
            }
        }
        // clear table list
        $this->tableList = null;
        return $response;
    }
    /**
     * Alters a table's definition
     *
     * @param   string   $table Table name
     * @param   callable $callback A callback that will add or remove fields or indexes
     * @throws  WPDBException
     */
    public function alter(string $table, callable $callback)
    {
        $compiler = $this->connection->schemaCompiler();
        $schema = new AlterTable($table);
        $callback($schema);
        $response = \false;
        unset($this->columns[\strtolower($table)]);
        foreach ($compiler->alter($schema) as $result) {
            $query = $this->connection->command($result['sql'], $result['params']);
            $response = $this->getWPDBQuery($query);
            if (!$response && !empty($this->getLatestWPDBError())) {
                throw new WPDBException($this->getLatestWPDBError());
            }
        }
        return $response;
    }
    /**
     * Change a table's name
     *
     * @param   string $table The table
     * @param   string $name The new name of the table
     * @throws  WPDBException When renaming a table fails.
     */
    public function renameTable(string $table, string $name)
    {
        $result = $this->connection->schemaCompiler()->renameTable($table, $name);
        $query = $this->connection->command($result['sql'], $result['params']);
        $response = $this->getWPDBQuery($query);
        if (!$response && !empty($this->getLatestWPDBError())) {
            throw new WPDBException($this->getLatestWPDBError());
        }
        $this->tableList = null;
        unset($this->columns[\strtolower($table)]);
        return $response;
    }
    /**
     * Deletes a table
     *
     * @param  string $table Table name
     * @throws WPDBException When dropping a table fails.
     */
    public function drop(string $table)
    {
        $compiler = $this->connection->schemaCompiler();
        $result = $compiler->drop($table);
        $query = $this->connection->command($result['sql'], $result['params']);
        $response = $this->getWPDBQuery($query);
        if (!$response && !empty($this->getLatestWPDBError())) {
            throw new WPDBException($this->getLatestWPDBError());
        }
        // clear table list
        $this->tableList = null;
        unset($this->columns[\strtolower($table)]);
        return $response;
    }
    /**
     * Deletes all records from a table
     *
     * @param   string $table Table name
     * @throws  WPDBException When truncating a table fails.
     */
    public function truncate(string $table)
    {
        $compiler = $this->connection->schemaCompiler();
        $result = $compiler->truncate($table);
        $query = $this->connection->command($result['sql'], $result['params']);
        $response = $this->getWPDBQuery($query);
        if (!$response && !empty($this->getLatestWPDBError())) {
            throw new WPDBException($this->getLatestWPDBError());
        }
        return $response;
    }
}
