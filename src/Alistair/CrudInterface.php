<?php
declare(strict_types=1);

namespace Boronczyk\Alistair;

/**
 * Interface CrudInterface
 * @package Boronczyk\Alistair
 */
interface CrudInterface
{
    /**
     * Return a record from the database by ID.
     *
     * @param int $id
     * @return array
     * @throws \PDOException
     */
    public function byId(int $id): array;

    /**
     * Create a new record in the database and return the new record's ID.
     *
     * @param array $data
     * @return int
     * @throws \PDOException
     */
    public function create(array $data): int;

    /**
     * Update a record in the database.
     *
     * @param int $id
     * @param array $data
     * @throws \PDOException
     */
    public function update(int $id, array $data);

    /**
     * Delete a record from the database.
     *
     * @param int $id
     * @throws \PDOException
     */
    public function delete(int $id);

}
