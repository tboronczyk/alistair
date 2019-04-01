<?php
declare(strict_types=1);

namespace Boronczyk\Alistair;

/**
 * Class Model
 * @package Boronczyk\Alistair
 */
class Model
{
    protected $db;

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
     * @param array|null $params
     * @return \PDOStatement
     * @throws \PDOException
     */
    protected function stmt(string $query, ?array $params = null): \PDOStatement
    {
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Execute a query.
     *
     * @param string $query
     * @param array|null $params
     * @throws \PDOException
     */
    public function query(string $query, ?array $params = null)
    {
        $this->stmt($query, $params);
    }

    /**
     * Execute a query and return the result rows.
     *
     * @param string $query
     * @param array|null $params
     * @return array
     * @throws \PDOException
     */
    public function queryRows(string $query, ?array $params = null): array
    {
        $stmt = $this->stmt($query, $params);
        $rows = $stmt->fetchAll($this->db::FETCH_ASSOC);
        $stmt->closeCursor();
        return $rows;
    }

    /**
     * Execute a query and return a single row.
     *
     * @param string $query
     * @param array|null $params
     * @return array
     * @throws \PDOException
     */
    public function queryRow(string $query, ?array $params = null): array
    {
        $rows = $this->queryRows($query, $params);
        $row = reset($rows);
        if ($row !== false) {
            return $row;
        }
        return [];
    }

    /**
     * Execute a query and return the value of the first column of the first
     * row.
     *
     * @param string $query
     * @param array|null $params
     * @return mixed
     * @throws \PDOException
     */
    public function queryColumn(string $query, ?array $params = null) /*: mixed */
    {
        $row = $this->queryRow($query, $params);
        $value = reset($row);
        if ($value !== false) {
            return $value;
        }
        return null;
    }
}
