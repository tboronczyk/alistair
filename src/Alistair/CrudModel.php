<?php

declare(strict_types=1);

namespace Boronczyk\Alistair;

/**
 * Class CrudModel
 * @package Boronczyk\Alistair
 */
abstract class CrudModel implements CrudModelInterface
{
    /**
     * Constructor
     *
     * @param DbAccess $db
     */
    public function __construct(
        protected DbAccess $db
    ) {
    }

    /**
     * Return the list of available column names
     *
     * @return array<string>
     */
    abstract public function columns(): array;

    /**
     * Return the table name
     *
     * This implementation derives the table name by formatting the class
     * name in snake_case.
     *
     * @return string
     */
    public function table(): string
    {
        $classname = (new \ReflectionClass($this))->getShortName();

        $classname = lcfirst($classname);

        return (string)preg_replace_callback(
            '/([A-Z]+)/',
            function ($matches) {
                return '_' . strtolower($matches[0]);
            },
            $classname
        );
    }

    /**
     * Format column names as a comma-separated list
     *
     * @param array<string> $columns
     * @return string
     */
    protected function columnsAsList(array $columns): string
    {
        $cols = [];
        foreach ($columns as $name) {
            if ($name == '*') {
                return '*';
            }
            $cols[] = "`$name`";
        }

        return join(', ', $cols);
    }

    /**
     * Format column names as a comma-separated list of assignment pairs
     *
     * @param array<string> $columns
     * @return string
     */
    protected function columnsAsAssign(array $columns): string
    {
        $assignments = array_map(function ($column) {
            return "`$column` = :$column";
        }, $columns);

        return join(', ', $assignments);
    }

    /**
     * Format column names as a comma-separated list of placeholders
     *
     * @param array<string> $columns
     * @return string
     */
    protected function columnsAsPlaceholders(array $columns): string
    {
        $placeholders = array_map(function ($column) {
            return ":$column";
        }, $columns);

        return join(', ', $placeholders);
    }

    /**
     * Format the specified sorting as a comma-separated list
     *
     * Sort direction is normalized to "ASC" and "DESC".
     *
     * @param array<string> $columns
     * @return string
     */
    protected function sortAsList(array $columns): string
    {
        $sort = [];
        foreach ($columns as $column) {
            [$col, $direction] = explode(':', $column, 2);

            $direction = strtoupper($direction);
            if ($direction != 'DESC') {
                $direction = 'ASC';
            }

            $sort[] = "`$col` $direction";
        }

        return join(', ', $sort);
    }

    /**
     * Remove unknown columns/keys from the provided data
     *
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    protected function filterData(array $data): array
    {
        $columns = $this->columns();

        return array_filter($data, function ($key) use ($columns) {
                return in_array($key, $columns);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Return the number of records in the table
     *
     * @return int
     * @throws \PDOException
     */
    public function count(): int
    {
        $table = $this->table();
        return (int)$this->db->queryValue("SELECT COUNT(`id`) FROM `$table`");
    }

    /**
     * Create a new record in the database and return the new record's ID
     *
     * @param array<string,mixed> $data
     * @return int
     * @throws \InvalidArgumentException|\PDOException
     */
    public function create(array $data): int
    {
        // extract known columns
        $data = $this->filterData($data);

        // id should not be set
        if (array_key_exists('id', $data)) {
            unset($data['id']);
        }

        $table = $this->table();
        $columns = array_keys($data);
        $columnlist = $this->columnsAsList($columns);
        $placeholders = $this->columnsAsPlaceholders($columns);

        $query = "INSERT INTO `$table` (id, $columnlist) VALUES (NULL, $placeholders)";

        $this->db->query($query, $data);

        return (int)$this->db->getPdo()->lastInsertId();
    }

    /**
     * Return records from the database
     *
     * $columns is an array of column names limiting the returned data.
     *
     * $sort is an array of column names by which the data is ordered. The
     * sort direction may be specified by appending :ASC (default) or :DESC,
     * for example: ["columnA:ASC", "columnB:DESC"].
     *
     * $count and $offset are used for pagination.
     *
     * @param ?array<string> $columns (optional)
     * @param ?array<string> $sort (optional, required if $count and $offset given)
     * @param ?int $count (optional, required if $offset given)
     * @param ?int $offset (optional)
     * @return array<array<string,mixed>>
     * @throws \InvalidArgumentException|\PDOException
     */
    public function get(
        ?array $columns = null,
        ?array $sort = null,
        ?int $count = null,
        ?int $offset = null,
    ): array {
        // ensure necessary arguments were provided
        if (is_null($count) && !is_null($offset)) {
            throw new \InvalidArgumentException('count must be provided when offset is given');
        }

        if (is_null($sort) && !is_null($count)) {
            throw new \InvalidArgumentException('sort must be provided when count is given');
        }

        if (empty($columns)) {
            $columns = $this->columns();
        }
        $columns[] = 'id'; // always include id
        $columns = array_unique($columns);

        $table = $this->table();
        $columns = $this->columnsAsList($columns);
        $query = "SELECT $columns FROM `$table`";

        if (!empty($sort)) {
            $query .= ' ORDER BY ' . $this->sortAsList($sort);
        }

        if (!is_null($count)) {
            $query .= " LIMIT $count";
        }

        if (!is_null($offset)) {
            $query .= " OFFSET $offset";
        }

        return $this->db->queryRows($query);
    }

    /**
     * Return a record from the database by ID
     *
     * @param int $id
     * @param ?array<string> $columns (optional)
     * @return array<string,string>
     * @throws \PDOException
     */
    public function getById(int $id, ?array $columns = null): array {
        if (empty($columns)) {
            $columns = $this->columns();
        }
        $columns[] = 'id'; // always include id
        $columns = array_unique($columns);

        $table = $this->table();
        $columns = $this->columnsAsList($columns);

        $query = "SELECT $columns FROM `$table` WHERE `id` = ?";

        return (array)$this->db->queryRow($query, [$id]);
    }

    /**
     * Update a record in the database
     *
     * @param int $id
     * @param array<string,mixed> $data
     * @return void
     * @throws \InvalidArgumentException|\PDOException
     */
    public function update(int $id, array $data): void
    {
        // extract known columns
        $data = $this->filterData($data);

        // id should not be set
        if (array_key_exists('id', $data)) {
            unset($data['id']);
        }

        $table = $this->table();
        $assign = $this->columnsAsAssign(array_keys($data));

        $query = "UPDATE `$table` SET $assign WHERE id = :id";
        $data['id'] = $id;

        $this->db->query($query, $data);
    }

    /**
     * Delete a record from the database
     *
     * @param int $id
     * @return void
     * @throws \PDOException
     */
    public function delete(int $id): void
    {
        $table = $this->table();

        $query = "DELETE FROM `$table` WHERE `id` = ?";

        $this->db->query($query, [$id]);
    }
}
