<?php

declare(strict_types=1);

namespace Boronczyk\Alistair;

/**
 * Class DbAccess
 * @package Boronczyk\Alistair
 */
class DbAccess implements DbAccessInterface
{
    protected \PDO $db;

    /**
     * Constructor
     *
     * @param \PDO $db
     */
    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Prepare and execute a prepared statement.
     *
     * @param string $query
     * @param array $params (optional)
     * @return \PDOStatement
     * @throws \PDOException
     */
    protected function stmt(string $query, array $params = null): \PDOStatement
    {
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        return $stmt;
    }

    /**
     * Execute a query.
     *
     * @param string $query
     * @param array $params (optional)
     * @return void
     * @throws \PDOException
     */
    public function query(string $query, array $params = null): void
    {
        $this->stmt($query, $params);
    }

    /**
     * Execute a query and return the result rows.
     *
     * @param string $query
     * @param array $params (optional)
     * @param string $classname (optional)
     * @return array
     * @throws \PDOException
     */
    public function queryRows(string $query, array $params = null, string $classname = null): array
    {
        $stmt = $this->stmt($query, $params);

        if ($classname == null) {
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        } else {
            $stmt->setFetchMode(\PDO::FETCH_CLASS, $classname);
        }
        $rows = $stmt->fetchAll();
        $stmt->closeCursor();

        return ($rows === false) ? [] : $rows;
    }

    /**
     * Execute a query and return a single row.
     *
     * @param string $query
     * @param array $params (optional)
     * @param string $classname (optional)
     * @return array|object
     * @throws \PDOException
     */
    public function queryRow(string $query, array $params = null, string $classname = null) /*: array|object */
    {
        $stmt = $this->stmt($query, $params);

        if ($classname == null) {
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        } else {
            $stmt->setFetchMode(\PDO::FETCH_CLASS, $classname);
        }
        $row = $stmt->fetch();
        $stmt->closeCursor();

        return ($row === false) ? [] : $row;
    }

    /**
     * Execute a query and return the value of the first column of the first
     * row.
     *
     * @param string $query
     * @param array $params (optional)
     * @return mixed
     * @throws \PDOException
     */
    public function queryValue(string $query, array $params = null) /*: mixed */
    {
        $row = $this->queryRow($query, $params);
        $value = reset($row);

        return (count($row)) ? $value : null;
    }
}
