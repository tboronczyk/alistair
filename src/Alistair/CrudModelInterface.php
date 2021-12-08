<?php

declare(strict_types=1);

namespace Boronczyk\Alistair;

/**
 * Interface CrudModel
 * @package Boronczyk\Alistair
 */
interface CrudModelInterface
{
    /**
     * Return the list of available column names
     *
     * @return array<string>
     */
    public function columns(): array;

    /**
     * Return the table name
     *
     * @return string
     */
    public function table(): string;

    /**
     * Return the number of records in the table
     *
     * @return int
     * @throws \PDOException
     */
    public function count(): int;

    /**
     * Create a new record in the database and return the new record's ID
     *
     * @param array<string,mixed> $data
     * @return int
     * @throws \InvalidArgumentException|\PDOException
     */
    public function create(array $data): int;

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
    ): array;

    /**
     * Return a record from the database by ID
     *
     * @param int $id
     * @param ?array<string> $columns (optional)
     * @return array<string,string>
     * @throws \PDOException
     */
    public function getById(int $id, ?array $columns = null): array;

    /**
     * Update a record in the database
     *
     * @param int $id
     * @param array<string,mixed> $data
     * @return void
     * @throws \InvalidArgumentException|\PDOException
     */
    public function update(int $id, array $data): void;

    /**
     * Delete a record from the database
     *
     * @param int $id
     * @return void
     * @throws \PDOException
     */
    public function delete(int $id): void;
}
