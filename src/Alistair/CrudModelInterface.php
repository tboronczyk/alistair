<?php
declare(strict_types=1);

namespace Boronczyk\Alistair;

/**
 * Class CrudModelInterface
 * @package Boronczyk\Alistair
 */
interface CrudModelInterface
{
    /**
     * Return the list of available column names.
     *
     * @return string[]
     */
    public function columns(): array;

    /**
     * Return the list of columns required to create or update a record.
     *
     * @return string[]
     */
    public function requiredColumns(): array;

    /**
     * Return the table name.
     *
     * This implementation derives the table name by formatting the class
     * name in snake_case. It is provided for convenience and can be
     * overridden by child classes as necessary.
     *
     * @return string
     */
    public function table();

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
     */
    public function get(array $columns = null, array $sort = null, int $count = null, int $offset = null): array;

    /**
     * Return a record from the database by ID.
     *
     * @param int $id
     * @param array $columns (optional)
     * @return array
     */
    public function getById(int $id, array $columns = null): array;

    /**
     * Create a new record in the database and return the new record's ID.
     *
     * @param array $data
     * @return int
     */
    public function create(array $data): int;

    /**
     * Delete a record from the database.
     *
     * @param int $id
     * @throws \PDOException
     */
    public function delete(int $id);

    /**
     * Update a record in the database.
     *
     * @param int $id
     * @param array $data
     */
    public function update(int $id, array $data);
}
