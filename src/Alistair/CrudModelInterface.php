<?php
declare(strict_types=1);

namespace Boronczyk\Alistair;

/**
 * Interface CrudModelInterface
 * @package Boronczyk\Alistair
 */
interface CrudModelInterface
{
    /**
     * Return whether the given data contains all necessary columns to
     * create/update a record.
     *
     * @param array $data
     * @return bool
     */
    public function hasRequiredFields(array $data): bool;

    /**
     * Return records from the database.
     *
     * $sort, $count, and $offset are used for pagination.
     *
     * $sort is an array of column names by which the records are ordered. The
     * sort direction may be specified by appending :ASC (default) or :DESC,
     * for example: ["colA:ASC", "colB:DESC"].
     *
     * @param array $sort (optional, required if $count and $offset given)
     * @param int $count (optional, required if $offset given)
     * @param int $offset (optional)
     * @return array
     * @throws \PDOException
     */
    public function get(array $sort = [], int $count = null, int $offset = null): array;

    /**
     * Return a record from the database by ID.
     *
     * @param int $id
     * @return array
     * @throws \PDOException
     */
    public function getById(int $id): array;

    /**
     * Create a new record in the database and return the new record's ID.
     *
     * @param array $data
     * @return int
     * @throws \PDOException
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
     * @throws \PDOException
     */
    public function update(int $id, array $data);
}
