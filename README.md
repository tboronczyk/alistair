# Alistair

Alistair is a simple SQL database wrapper for pedagogical purposes. It
demonstrates how one can encapsulate common database operations and organize
raw queries into MVC models.

## Classes

### DbAccess

The `DbAccess` class provides methods for executing queries against an SQL
database.

Methods defined by `DbAccessInterface`:

  * `query` - execute a query
  * `queryRows` - execute a query and return result rows
  * `queryRow` - execute a query and return a single result row
  * `queryValue` - execute a query and return a single value

#### Example Usage
    <?php
    use Boronczyk\Alistair\DbAccess;

    class Users extends DbAccess
    {
        public function getById(int $id): array {
            return $this->queryRow(
                'SELECT id, first_name, last_name, email FROM users WHERE id = ?',
                [$id]
            );
        }
    }

    $pdo = new \PDO(...);
    $users = new Users($pdo);
    $user = $users->getById(42);

### CrudModel

`CrudModel` is an abstract class that extends `DbAccess` to provide methods for CRUD
operations (create, retrieve, update, delete). Implementations must provide `columns`
 method returning an array of valid column names.

Methods defined by `CrudModelInterface`:

  * `hasRequiredFields` - whether the data contains all necessary columns
  * `get` - return records, supports pagination
  * `getById` - return a record by ID
  * `create` - create a record
  * `update` - update a record
  * `delete` - delete a record

#### Example Usage

    <?php
    use Boronczyk\Alistair\CrudModel;

    class Users extends CrudModel
    {
        // implementation required by abstract class
        public function columns(): array {
            return [
                'first_name',
                'last_name',
                'email'
            ];
        }
    }

    $pdo = new \PDO(...);
    $users = new Users($pdo);
    $user = $users->getById(42);

