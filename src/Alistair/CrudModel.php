<?php
declare(strict_types=1);

namespace Boronczyk\Alistair;

/**
 * Class CrudModel
 * @package Boronczyk\Alistair
 */
abstract class CrudModel extends Model implements CrudInterface
{
    /**
     * Return the list of column names.
     *
     * @return string[]
     */
    abstract public function columns(): array;

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

        return preg_replace_callback(
            '/([A-Z]+)/',
            function ($matches) {
                return '_' . strtolower($matches[0]);
            },
            lcfirst($classname)
        );
    }

    /**
     * Return the column names as a comma-separated list.
     *
     * @return string
     */
    protected function columnsAsList(): string
    {
        return join(', ', $this->columns());
    }

    /**
     * Return the column names as a comma-separated list of assignment pairs.
     *
     * @return string
     */
    protected function columnsAsAssign(): string
    {
        $assigns = array_map(
            function ($column) {
                return "$column = :$column";
            },
            $this->columns()
        );

        return join(', ', $assigns);
    }

    /**
     * Return the column names as a comma-separated list of placeholders.
     *
     * @return string
     */
    protected function columnsAsPlaceholders(): string
    {
        $columns = array_map(function ($column) {
            return ":$column";
        }, $this->columns());

        return join(', ', $columns);
    }

    /**
     * Remove unknown columns and return the specified sorting as a
     * comma-separated list.
     *
     * Sort direction is normalized to "ASC" and "DESC".
     *
     * @param array $sort
     * @return string
     */
    protected function filterSortAsList(array $sort): string
    {
        $columns = $this->columns();

        foreach ($sort as $key => $column) {
            [$column, $direction] = explode(':', $column, 2);
            if (!in_array($column, $columns)) {
                unset($sort[$key]);
                continue;
            }

            $direction = strtoupper($direction);
            if ($direction != 'DESC') {
                $direction = 'ASC';
            }

            $sort[$key] = "$column $direction";
        }

        return join(', ', $sort);
    }

    /**
     * Remove unknown values (keys) from the provided data.
     *
     * @return array
     */
    protected function filterData(array $data): array
    {
        $columns = $this->columns();
        return array_filter(
            $data,
            function ($key) use ($columns) {
                return in_array($key, $columns);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Return whether the given data contains all necessary columns to
     * create/update a record.
     *
     * @param array $data
     * @return bool
     */
    public function hasRequiredFields(array $data): bool
    {
        $diff = array_diff($this->columns(), array_keys($data));
        return (count($diff) == 0);
    }

    /**
     * Return paginated records from the database.
     *
     * $sort is an array of column names by which the records are ordered. The
     * sort direction may be specified by appending :ASC (default) or :DESC,
     * for example: ["colA:ASC", "colB:DESC"].
     *
     * @param array $sort
     * @param ?int $count
     * @param ?int $offset
     * @return array
     * @throws \PDOException
     */
    public function page(array $sort, ?int $count = null, ?int $offset = null): array
    {
        if (is_null($count) && !is_null($offset)) {
          throw new \InvalidArgumentException("count must be given when offset is present");
        }

        $table = $this->table();
        $columns = $this->columnsAsList();
        $order = $this->filterSortAsList($sort);

        $query = "SELECT id, $columns FROM $table ORDER BY $order";

        if (!is_null($count)) {
            if (is_null($offset)) {
                $query .= " LIMIT $count";
            } else {
                $query .= " LIMIT $offset, $count";
            }
        }

        return $this->queryRows($query);
    }

    /**
     * Return a record from the database by ID.
     *
     * @param int $id
     * @return array
     * @throws \PDOException
     */
    public function byId(int $id): array
    {
        $table = $this->table();
        $columns = $this->columnsAsList();

        $query = "SELECT id, $columns FROM $table WHERE id = ?";
        return $this->queryRow($query, [$id]);
    }

    /**
     * Create a new record in the database and return the new record's ID.
     *
     * @param array $data
     * @return int
     * @throws \PDOException
     */
    public function create(array $data): int
    {
        $table = $this->table();
        $columns = $this->columnsAsList();
        $placeholders = $this->columnsAsPlaceholders();

        $query = "INSERT INTO $table (id, $columns) VALUES (NULL, $placeholders)";
        $this->query($query, $this->filterData($data));

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

        $query = "DELETE FROM $table WHERE id = ?";
        $this->query($query, [$id]);
    }

    /**
     * Update a record in the database.
     *
     * @param int $id
     * @param array $data
     * @throws \PDOException
     */
    public function update(int $id, array $data)
    {
        $table = $this->table();
        $assign = $this->columnsAsAssign();

        $query = "UPDATE $table SET $assign WHERE id = :id";
        $data = array_merge($this->filterData($data), ['id' => $id]);
        $this->query($query, $data);
    }
}
