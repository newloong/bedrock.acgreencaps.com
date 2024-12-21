<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\databasepress
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\Schema\Compiler\MySQL as SchemaCompiler;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\Compiler\MySQL as MySQLCompiler;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\InsertStatement;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\Query as QueryCommand;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\Insert as InsertCommand;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\Update as UpdateCommand;
/**
 * WordPress database query builder based on wpdb.
 */
class Database
{
    use WPDBHelpers;
    /**
     * List of commands being run.
     *
     * @var array
     */
    protected $commands = [];
    /**
     * SQL Compiler instance.
     *
     * @var SQLCompiler
     */
    protected $compiler;
    /**
     * Schema Compiler instance.
     *
     * @var SchemaCompiler
     */
    protected $schemaCompiler;
    /**
     * Schema instance.
     *
     * @var Schema
     */
    protected $schema;
    /**
     * Compiler options.
     *
     * @var array
     */
    protected $compilerOptions = [];
    /**
     * Schema compiler options.
     *
     * @var array
     */
    protected $schemaCompilerOptions = [];
    /**
     * WPDB Instance.
     *
     * @var \wpdb
     */
    protected $wpdb;
    /**
     * Determines if debug mode is enabled.
     *
     * @var boolean
     */
    protected $debug = \false;
    /**
     * Log queries flag.
     *
     * @var boolean
     */
    protected $logQueries = \false;
    /**
     * Logged queries.
     *
     * @var array
     */
    protected $log = [];
    /**
     * Initialize a new instance of the Database class.
     */
    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    /**
     * Toggle debug mode.
     *
     * When debug mode is enabled SQL statements will be returned instead
     * of being triggered.
     *
     * Currently this is meant for internal usage only.
     *
     * @param boolean $debug
     * @return self
     */
    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
        return $this;
    }
    /**
     * Determines if debug mode is enabled.
     *
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }
    /**
     * Enable or disable query logging
     *
     * @param   bool $value (optional) Value
     * @return  self
     */
    public function logQueries(bool $value = \true) : self
    {
        $this->logQueries = $value;
        return $this;
    }
    /**
     * Returns the query log for this database.
     *
     * @return  array
     */
    public function getLog() : array
    {
        return $this->log;
    }
    /**
     * Returns the wpdb instance.
     *
     * @return \wpdb
     */
    public function getConnection()
    {
        return $this->wpdb;
    }
    /**
     * Get the compiler instance.
     *
     * @return MySQLCompiler
     */
    public function getCompiler()
    {
        if ($this->compiler === null) {
            $this->compiler = new MySQLCompiler();
        }
        return $this->compiler;
    }
    /**
     * Returns an instance of the schema compiler associated with this connection
     *
     * @throws \Exception
     * @return SchemaCompiler
     */
    public function schemaCompiler()
    {
        if ($this->schemaCompiler === null) {
            $this->schemaCompiler = new SchemaCompiler($this);
        }
        return $this->schemaCompiler;
    }
    /**
     * Prepares a query.
     *
     * @param string $query SQL query
     * @param array  $params Query parameters
     * @return array
     */
    protected function prepare(string $query, array $params) : array
    {
        $statement = $this->replaceParams($query, $params);
        return ['query' => $query, 'params' => $params, 'statement' => $statement];
    }
    /**
     * Replace placeholders with parameters.
     *
     * @param string $query SQL query
     * @param array  $params Query parameters
     * @return string
     */
    protected function replaceParams(string $query, array $params) : string
    {
        $compiler = $this->getCompiler();
        return \preg_replace_callback('/\\?/', function () use(&$params, $compiler) {
            $param = \array_shift($params);
            $param = \is_object($param) ? \get_class($param) : $param;
            if (\is_int($param) || \is_float($param)) {
                return $param;
            } elseif ($param === null) {
                return 'NULL';
            } elseif (\is_bool($param)) {
                return $param ? 'TRUE' : 'FALSE';
            } else {
                return $compiler->quote($param);
            }
        }, $query);
    }
    /**
     * Execute a non-query SQL command
     *
     * @param  string $sql SQL Command
     * @param  array  $params (optional) Command params
     * @return mixed   Command result
     */
    public function command(string $sql, array $params = [])
    {
        return $this->execute($this->prepare($sql, $params));
    }
    /**
     * Returns the prepared statement for a non-query sql command.
     *
     * @param array $prepared SQL command
     * @return string
     */
    protected function execute(array $prepared)
    {
        if ($this->logQueries) {
            $start = \microtime(\true);
            $log = ['query' => $this->replaceParams($prepared['query'], $prepared['params'])];
            $this->log[] =& $log;
        }
        $result = $prepared['statement'];
        if ($this->logQueries) {
            /** @noinspection PhpUndefinedVariableInspection */
            $log['time'] = \microtime(\true) - $start;
        }
        return $result;
    }
    /**
     * Execute a query
     *
     * @param string $sql SQL Query
     * @param array  $params (optional) Query params
     * @return Record
     */
    public function query(string $sql, array $params = [])
    {
        $prepared = $this->prepare($sql, $params);
        return new Record($prepared['statement']);
    }
    /**
     * Execute a query in order to fetch or to delete records.
     *
     * @param string|array $tables Table name or an array of tables
     * @return QueryCommand
     */
    public function from($tables) : QueryCommand
    {
        return new QueryCommand($this, $tables);
    }
    /**
     * Insert new records into a table.
     *
     * @param array $values An array of values.
     * @return InsertCommand|InsertStatement
     */
    public function insert(array $values) : InsertCommand
    {
        return (new InsertCommand($this))->insert($values);
    }
    /**
     * Update records.
     *
     * @param string $table Table name
     * @return UpdateCommand
     */
    public function update($table) : UpdateCommand
    {
        return new UpdateCommand($this, $table);
    }
    /**
     * Execute a query and fetch the first column.
     *
     * @param string $sql SQL Query
     * @param array  $params (optional) Query params
     * @return mixed
     */
    public function column(string $sql, array $params = [])
    {
        $prepared = $this->prepare($sql, $params);
        $result = $this->getWPDBColumn($prepared['statement']);
        return isset($result[0]) && \count($result) <= 1 ? $result[0] : $result;
    }
    /**
     * Returns the schema associated with this connection
     *
     * @return  Schema
     */
    public function getSchema() : Schema
    {
        if ($this->schema === null) {
            $this->schema = new Schema($this);
        }
        return $this->schema;
    }
    /**
     * The associated schema instance.
     *
     * @return  Schema
     */
    public function schema() : Schema
    {
        if ($this->schema === null) {
            $this->schema = $this->getSchema();
        }
        return $this->schema;
    }
    /**
     * Database transactions aren't currently being used.
     *
     * @param callable $callback
     * @param mixed    $that
     * @param mixed    $default
     * @return mixed
     */
    public function transaction(callable $callback, $that = null, $default = null)
    {
        return $callback($this);
    }
}
