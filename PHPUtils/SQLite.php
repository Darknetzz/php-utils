<?php

declare(strict_types=1);

namespace PHPUtils;

/**
 * SQLite helpers for connections and queries.
 *
 * Errors throw RuntimeException / InvalidArgumentException (same style as SQL).
 *
 * @package PHPUtils
 */
class SQLite extends Base {

    public function __construct(?Debugger $debugger = null, ?Vars $vars = null, bool $verbose = true) {
        parent::__construct($debugger, $vars, $verbose);
    }

    public function clean(string $string): string {
        $find    = ["\n", "\r"];
        $replace = "";
        $string  = str_replace($find, $replace, $string);
        return htmlentities(trim($string), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Create a new database file and open it for reading/writing.
     *
     * @throws \RuntimeException If the directory is not writable or open fails
     */
    public function sqlite_create_db(string $dbname = 'database'): \SQLite3 {
        $path = $dbname . '.sqlite';
        if (!is_writable(dirname($path))) {
            throw new \RuntimeException('The directory is not writable.');
        }

        $db = new \SQLite3($path, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
        $db->enableExceptions(true);
        return $db;
    }

    /**
     * Open an existing database for reading/writing.
     *
     * @throws \RuntimeException If the database file does not exist
     */
    public function sqlite_select_db(string $dbname = 'database'): \SQLite3 {
        $path = $dbname . '.sqlite';
        if (!file_exists($path)) {
            throw new \RuntimeException('The database does not exist.');
        }

        $db = new \SQLite3($path, SQLITE3_OPEN_READWRITE);
        $db->enableExceptions(true);
        return $db;
    }

    /**
     * Delete the database file.
     *
     * @throws \RuntimeException If the file is missing or not writable
     */
    public function sqlite_drop_db(string $dbname = 'database'): bool {
        $path = $dbname . '.sqlite';
        if (!file_exists($path)) {
            throw new \RuntimeException('The database does not exist.');
        }
        if (!is_writable($path)) {
            throw new \RuntimeException('The database is not writable.');
        }

        return unlink($path);
    }

    /**
     * List database basenames (without .sqlite) in a directory.
     *
     * @return list<string>
     */
    public function sqlite_get_dbs(string $dir = '.'): array {
        $dbs = [];
        $files = scandir($dir);
        if ($files === false) {
            throw new \RuntimeException("Unable to read directory: $dir");
        }

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sqlite') {
                $dbs[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        return $dbs;
    }

    /**
     * @return list<string>
     * @throws \RuntimeException On query failure
     */
    public function sqlite_get_tables(\SQLite3 $db): array {
        try {
            $result = $db->query('SELECT name FROM sqlite_master WHERE type="table";');
            $tables = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $tables[] = $row['name'];
            }
            return $tables;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Create a table. An `id` INTEGER PRIMARY KEY AUTOINCREMENT column is always added.
     *
     * @param array<string, string> $columns Column name => SQL type
     * @throws \RuntimeException If the table already exists or the query fails
     */
    public function sqlite_create_table(
        \SQLite3 $db,
        string $table_name,
        array $columns = ['column_name' => 'column_type']
    ): \SQLite3Result|bool {
        try {
            if ($this->sqlite_table_exists($db, $table_name)) {
                throw new \RuntimeException("Table $table_name already exists.");
            }

            $query = 'CREATE TABLE IF NOT EXISTS "' . $table_name . '" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            ';
            foreach ($columns as $column_name => $column_type) {
                $query .= '"' . $column_name . '" ' . $column_type . ', ';
            }
            $query = rtrim($query, ', ');
            $query .= ');';

            return $db->query($query);
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * @throws \RuntimeException On query failure
     */
    public function sqlite_drop_table(\SQLite3 $db, string $table_name): \SQLite3Result|bool {
        try {
            return $db->query('DROP TABLE IF EXISTS "' . $table_name . '";');
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * @throws \RuntimeException On query failure
     */
    public function sqlite_empty_table(\SQLite3 $db, string $table_name): \SQLite3Result|bool {
        try {
            return $db->query('DELETE FROM "' . $table_name . '";');
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * @throws \RuntimeException On query failure
     */
    public function sqlite_table_exists(\SQLite3 $db, string $table_name): bool {
        try {
            $query = 'SELECT name FROM sqlite_master WHERE type="table" AND name="' . $table_name . '";';
            $result = $db->querySingle($query);
            return $result === $table_name;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * @return list<array{name: string, type: string}>
     * @throws \RuntimeException On query failure
     */
    public function sqlite_get_columns(\SQLite3 $db, string $table_name): array {
        try {
            $result = $db->query('PRAGMA table_info("' . $table_name . '");');
            $columns = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $columns[] = [
                    'name' => $row['name'],
                    'type' => $row['type'],
                ];
            }
            return $columns;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * @throws \InvalidArgumentException If $db is null
     * @throws \RuntimeException On query failure
     */
    public function sqlite_query(?\SQLite3 $db, string $query): \SQLite3Result {
        if ($db === null) {
            throw new \InvalidArgumentException('Database connection not found.');
        }

        try {
            $result = $db->query($query);
            if ($result === false) {
                throw new \RuntimeException($db->lastErrorMsg());
            }
            return $result;
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @throws \RuntimeException On insert failure
     */
    public function sqlite_insert(\SQLite3 $db, string $table, array $data): \SQLite3Result {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";

            $stmt = $db->prepare($query);
            $i = 1;
            foreach ($data as $value) {
                $stmt->bindValue($i, $value, is_int($value) ? SQLITE3_INTEGER : SQLITE3_TEXT);
                $i++;
            }

            $insert = $stmt->execute();
            if ($insert === false) {
                throw new \RuntimeException($db->lastErrorMsg());
            }
            return $insert;
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * @param list<string> $columns
     * @param list<mixed> $params
     * @throws \RuntimeException If the table is missing or the query fails
     */
    public function sqlite_select(
        \SQLite3 $db,
        string $table,
        array $columns = ['*'],
        string $where = '',
        array $params = [],
        string $order = '',
        string $limit = ''
    ): \SQLite3Result {
        try {
            if (!$this->sqlite_table_exists($db, $table)) {
                throw new \RuntimeException("Table $table does not exist.");
            }

            $columnList = implode(', ', $columns);
            $query = "SELECT $columnList FROM $table";
            if ($where !== '') {
                $query .= " WHERE $where";
            }
            if ($order !== '') {
                $query .= " ORDER BY $order";
            }
            if ($limit !== '') {
                $query .= " LIMIT $limit";
            }

            $stmt = $db->prepare($query);
            foreach ($params as $i => $value) {
                $stmt->bindValue($i + 1, $value, is_int($value) ? SQLITE3_INTEGER : SQLITE3_TEXT);
            }

            $result = $stmt->execute();
            if ($result === false) {
                throw new \RuntimeException($db->lastErrorMsg());
            }
            if ($result->numColumns() === 0) {
                throw new \RuntimeException('No columns returned.');
            }
            return $result;
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param list<mixed> $params
     * @return bool Whether any rows were changed
     * @throws \RuntimeException On update failure
     */
    public function sqlite_update(
        \SQLite3 $db,
        string $table,
        array $data,
        string $where,
        array $params
    ): bool {
        try {
            $set = '';
            foreach ($data as $column => $value) {
                $set .= "$column = ?, ";
            }
            $set = rtrim($set, ', ');

            $query = "UPDATE $table SET $set WHERE $where";

            $stmt = $db->prepare($query);
            $i = 1;
            foreach ($data as $value) {
                $stmt->bindValue($i, $value, is_int($value) ? SQLITE3_INTEGER : SQLITE3_TEXT);
                $i++;
            }
            foreach ($params as $value) {
                $stmt->bindValue($i, $value, is_int($value) ? SQLITE3_INTEGER : SQLITE3_TEXT);
                $i++;
            }

            $stmt->execute();
            return $db->changes() > 0;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * @param list<mixed> $params
     * @return bool Whether any rows were deleted
     * @throws \RuntimeException On delete failure
     */
    public function sqlite_delete(\SQLite3 $db, string $table, string $where, array $params): bool {
        try {
            $query = "DELETE FROM $table WHERE $where";

            $stmt = $db->prepare($query);
            $i = 1;
            foreach ($params as $value) {
                $stmt->bindValue($i, $value, is_int($value) ? SQLITE3_INTEGER : SQLITE3_TEXT);
                $i++;
            }

            $stmt->execute();
            return $db->changes() > 0;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * @return array<string, mixed>|string
     */
    public function sqlite_version(?string $filter = null): array|string {
        $version = \SQLite3::version();
        if ($filter !== null) {
            return $version[$filter] ?? 'Unknown';
        }
        return $version;
    }

    /**
     * Dispatch to a sqlite_* method on this instance.
     *
     * @throws \InvalidArgumentException If the method does not exist
     */
    public function sqlite_function(string $func_name, mixed ...$params): mixed {
        $method = 'sqlite_' . str_replace('sqlite_', '', $func_name);
        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException("Method $method does not exist.");
        }
        return $this->$method(...$params);
    }

}
