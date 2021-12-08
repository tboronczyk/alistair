# Alistair

Alistair is a simple SQL database wrapper written for pedagogical purposes.
It demonstrates how one can encapsulate common database operations and organize
raw queries into a basis for MVC models/repositories.

## Classes

### DbAccess

The `DbAccess` class provides methods for executing queries against an SQL
database.

Methods defined by `DbAccessInterface`:

  * `query` - execute a query
  * `queryRows` - execute a query and return result rows
  * `queryRow` - execute a query and return a single result row
  * `queryValue` - execute a query and return a single value
  * `getPdo` - get the underlying PDO connection object

#### Example Usage
    <?php
    use Boronczyk\Alistair\DbAccess;

    class Users
    {
        public function __construct(
            protected DbAccess $db
        ) {
        }

        public function getById(int $id): array
        {
            return $this->db->queryRow(
                'SELECT id, username, email FROM users WHERE id = ?',
                [$id]
            );
        }
    }

    $db = new DbAccess(new \PDO(...));

    $users = new Users($db);
    $user = $users->getById(42);

### CrudModel

`CrudModel` is an abstract class that provides basic methods for CRUD
operations (create, retrieve, update, delete). Implementations must implement
the `columns` method returning an array of known column names for the table.

Implementations may override the `table` method returning the name of the
table (the base implementation derives the table name from the class's name).
Implementations may also override the `requiredColumns` method returning an
array of column names required to perform create and update operations if the
list is different from that returned by `column`.

Methods defined by `CrudModelInterface`:

  * `table` - return the table name
  * `columns` - return a list of known column names
  * `count` - return the number of records in the table
  * `get` - return records, supports column filtering and pagination
  * `getById` - return a record by ID, supports column filtering
  * `create` - create a new record
  * `update` - update an existing record
  * `delete` - delete an existing record

#### Example Usage

    <?php
    use Boronczyk\Alistair\DbAccess;
    use Boronczyk\Alistair\CrudModel;

    class Users extends CrudModel
    {
        // implementation required by abstract class
        public function columns(): array {
            return [
                'username',
                'email'
            ];
        }
    }

    $db = new DbAccess(new \PDO(...));

    $users = new Users($db);
    $user = $users->getById(42);

