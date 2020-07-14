<?php
declare(strict_types=1);

namespace Boronczyk\Alistair;

/**
 * Class CrudModel
 * @package Boronczyk\Alistair
 */
abstract class CrudModel extends DbAccess implements CrudModelInterface
{
    /**
     * Return the list of available column names.
     *
     * @return string[]
     */
    abstract public function columns(): array;

    /**
     * Return the list of columns required to create or update a record.
     *
     * @return string[]
     */
    public function requiredColumns(): array
    {
        return $this->columns();
    }

    /**
     * Return whether the given data contains all necessary columns to
     * create/update a record.
     *
     * @param array $data
     * @return bool
     */
    protected function hasRequiredFields(array $data): bool
    {
        $diff = array_diff($this->requiredColumns(), array_keys($data));
        return (count($diff) == 0);
    }

    /**
     * Return the table name.
     *
     * This implementation derives the table name by formatting the class
     * name in snake_case. It is provided for convenience and can be
     * overridden by child classes as necessary.
     *
     * @return string
     */
    public function table(): string
    {
        $classname = (new \ReflectionClass($this))->getShortName();

        $classname = lcfirst($classname);

        return preg_replace_callback('/([A-Z]+)/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, $classname);
    }

    /**
     * Format column names as a comma-separated list.
     *
     * @param array $columns
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
     * Format column names as a comma-separated list of assignment pairs.
     *
     * @param array $columns
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
     * Format column names as a comma-separated list of placeholders.
     *
     * @param array $columns
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
     * Format the specified sorting as a comma-separated list.
     *
     * Sort direction is normalized to "ASC" and "DESC".
     *
     * @param array $columns
     * @return string
     */
    protected function sortAsList(array $columns): string
    {
        $sort = [];
        foreach ($columns as $column) {
            [$col, $direction] = explode(':', $column, 2);

            $direction = strtoupper($direction ?? '');
            if ($direction != 'DESC') {
                $direction = 'ASC';
            }

            $sort[] = "`$col` $direction";
        }

        return join(', ', $sort);
    }

    /**
     * Remove unknown columns/keys from the provided data.
     *
     * @return array
     */
    protected function filterData(array $data): array
    {
        $columns = $this->columns();

        return array_filter($data, function ($key) use ($columns) {
                return in_array($key, $columns);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Return records from the database.
     *
     * $columns is an array of column names limiting the returned data.
     *
     * $sort is an array of column names by which the data is ordered. The
     * sort direction may be specified by appending :ASC (default) or :DESC,
     * for example: ["columnA:ASC", "columnB:DESC"].
     *
     * $count and $offset are used for pagination.
     *
     * @param array $columns (optional)
     * @param array $sort (optional, required if $count and $offset given)
     * @param int $count (optional, required if $offset given)
     * @param int $offset (optional)
     * @return array
     * @throws \InvalidArgumentException|\PDOException
     */
    public function get(array $columns = null, array $sort = null, int $count = null, int $offset = null): array
    {
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

        return $this->queryRows($query);
    }

    /**
     * Return a record from the database by ID.
     *
     * @param int $id
     * @param array $columns (optional)
     * @return array
     * @throws \PDOException
     */
    public function getById(int $id, array $columns = null): array
    {
        if (empty($columns)) {
            $columns = $this->columns();
        }
        $columns[] = 'id'; // always include id
        $columns = array_unique($columns);

        $table = $this->table();
        $columns = $this->columnsAsList($columns);

        $query = "SELECT $columns FROM `$table` WHERE `id` = ?";

        return $this->queryRow($query, [$id]);
    }

    /**
     * Create a new record in the database and return the new record's ID.
     *
     * @param array $data
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

        // ensure necessary fields are provided
        if (!$this->hasRequiredFields($data)) {
            throw new \InvalidArgumentException('missing required fields');
        }

        $table = $this->table();
        $columns = array_keys($data);
        $columnlist = $this->columnsAsList($columns);
        $placeholders = $this->columnsAsPlaceholders($columns);

        $query = "INSERT INTO `$table` (id, $columnlist) VALUES (NULL, $placeholders)";

        $this->query($query, $data);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Delete a record from the database.
     *
     * @param int $id
     * @throws \PDOException
     */
    public function delete(int $id)
    {
        $table = $this->table();

        $query = "DELETE FROM `$table` WHERE `id` = ?";

        $this->query($query, [$id]);
    }

    /**
     * Update a record in the database.
     *
     * @param int $id
     * @param array $data
     * @throws \InvalidArgumentException|\PDOException
     */
    public function update(int $id, array $data)
    {
        // extract known columns
        $data = $this->filterData($data);
        
        // id should not be set
        if (array_key_exists('id', $data)) {
            unset($data['id']);
        }
        
        // ensure necessary fields are provided
        if (!$this->hasRequiredFields($data)) {
            throw new \InvalidArgumentException('missing required fields');
        }

        $table = $this->table();
        $assign = $this->columnsAsAssign(array_keys($data));

        $query = "UPDATE `$table` SET $assign WHERE id = :id";
        $data['id'] = $id;

        $this->query($query, $data);
    }
}
