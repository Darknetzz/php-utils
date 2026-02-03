<?php

namespace PHPUtils;

/* ───────────────────────────────────────────────────────────────────── */
/*                                 SQLite                                */
/* ───────────────────────────────────────────────────────────────────── */
/**
 * Class SQLite
 * 
 * A class to handle SQLite connections and queries
 * 
 * @package PHPUtils
 */
class SQLite extends Base {

    public function __construct(?Debugger $debugger = null, ?Vars $vars = null, bool $verbose = true) {
        parent::__construct($debugger, $vars, $verbose);
    }

    # FUNCTION: clean
    public function clean($string) {
        $find    = ["\n", "\r"];
        $replace = "";
        $string  = str_replace($find, $replace, $string);
        return htmlentities(trim($string), ENT_QUOTES, 'UTF-8');
    }

    # FUNCTION: res
    public function res(string $status = "UNKNOWN", mixed $data = "No data."): array {
        return [
            "status"    => $status,
            "data"      => $data,
            "data_type" => gettype($data),
        ];
    }

    # FUNCTION: sqlite_create_db
    public function sqlite_create_db(string $dbname = 'database') : array {
        // Create a new database, if the file doesn't exist and open it for reading/writing.
        // The extension of the file is arbitrary.
        if (!is_writable(dirname($dbname.'.sqlite'))) {
            return $this->res("ERROR", "The directory is not writable.");
        }
        // if (file_exists($dbname.'.sqlite')) {
        //     return $this->res("ERROR", "The database already exists.");
        // }
        $db = new SQLite3($dbname.'.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
        $db->enableExceptions(true);
        return $this->res("SUCCESS", $db);
    }

    # FUNCTION: sqlite_select_db
    public function sqlite_select_db(string $dbname = 'database') : array {
        // Open an existing database for reading/writing.
        if (!file_exists($dbname.'.sqlite')) {
            return $this->res("ERROR", "The database does not exist.");
        }
        $db = new SQLite3($dbname.'.sqlite', SQLITE3_OPEN_READWRITE);
        // Errors are emitted as warnings by default, enable proper error handling.
        $db->enableExceptions(true);
        return $this->res("SUCCESS", $db);
    }

    # FUNCTION: sqlite_drop_db
    public function sqlite_drop_db(string $dbname = 'database') : array {
        // Delete the database file.
        if (!file_exists($dbname.'.sqlite')) {
            return $this->res("ERROR", "The database does not exist.");
        }
        if (!is_writable($dbname.'.sqlite')) {
            return $this->res("ERROR", "The database is not writable.");
        }
        $result = unlink($dbname.'.sqlite');
        return $this->res("SUCCESS", $result);
    }

    # FUNCTION: sqlite_get_dbs
    /**
    * Get a list of databases in the directory.
    * 
    * @param string $dir The directory to search.
    * 
    * @return array An array of database names.
    */
    public function sqlite_get_dbs(string $dir = '.') : array {
        $dbs = [];
        $files = scandir($dir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == 'sqlite') {
                $dbs[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }
        return $this->res("SUCCESS", $dbs);
    }

    # FUNCTION: sqlite_get_tables
    /**
    * Get a list of tables in the database.
    * 
    * @param SQLite3 $db The database connection.
    * 
    * @return array An array of table names.
    */
    public function sqlite_get_tables(SQLite3 $db) : array {
        try {
            $query = 'SELECT name FROM sqlite_master WHERE type="table";';
            $result = $db->query($query);
            $tables = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $tables[] = $row['name'];
            }
            return $this->res("SUCCESS", $tables);
        } catch (Exception $e) {
            return $this->res("ERROR", $e->getMessage());
        }
    }

    # FUNCTION: sqlite_create_table
    /**
    * Create a table in the database.
    * 
    * @param SQLite3 $db The database connection.
    * @param string $table_name The name of the table.
    * @param array $columns An associative array of column names and types. The key is the column name, the value is the column type.
    *              Example: ["column_name" => "column_type"]
    *              You don't need to specify the "id" column, it's automatically created as an INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL.
    */
    public function sqlite_create_table(SQLite3 $db, string $table_name, array $columns = ["column_name" => "column_type"]) : array {
        try {
            if (sqlite_table_exists($db, $table_name)) {
                return $this->res("ERROR", "Table $table_name already exists.");
            }
            $query = 'CREATE TABLE IF NOT EXISTS "'.$table_name.'" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            ';
            foreach ($columns as $column_name => $column_type) {
                $query .= '"'.$column_name.'" '.$column_type.', ';
            }
            $query = rtrim($query, ', ');
            $query .= ');';
            return $this->res("SUCCESS", $db->query($query));
        } catch (Exception $e) {
            return $this->res("ERROR", $e->getMessage());
        }
    }

    # FUNCTION: sqlite_drop_table
    /**
    * Drop a table from the database.
    * 
    * @param SQLite3 $db The database connection.
    * @param string $table_name The name of the table.
    */
    public function sqlite_drop_table(SQLite3 $db, string $table_name) : array {
        try {
            $query = 'DROP TABLE IF EXISTS "'.$table_name.'";';
            return $this->res("SUCCESS", $db->query($query));
        } catch (Exception $e) {
            return $this->res("ERROR", $e->getMessage());
        }
    }

    # FUNCTION: sqlite_empty_table
    /**
    * Empty a table in the database.
    * 
    * @param SQLite3 $db The database connection.
    * @param string $table_name The name of the table.
    */
    public function sqlite_empty_table(SQLite3 $db, string $table_name) : array {
        try {
            $query = 'DELETE FROM "'.$table_name.'";';
            return $this->res("SUCCESS", $db->query($query));
        } catch (Exception $e) {
            return $this->res("ERROR", $e->getMessage());
        }
    }

    # FUNCTION: sqlite_table_exists
    /**
    * Check if a table exists in the database.
    * 
    * @param SQLite3 $db The database connection.
    * @param string $table_name The name of the table.
    * 
    * @return bool TRUE if the table exists, FALSE if it doesn't.
    */
    public function sqlite_table_exists(SQLite3 $db, string $table_name) : array {
        try {
            $query = 'SELECT name FROM sqlite_master WHERE type="table" AND name="'.$table_name.'";';
            $result = $db->querySingle($query);
            return $this->res("SUCCESS", ($result === $table_name));
        } catch (Exception $e) {
            return $this->res("ERROR", $e->getMessage());
        }
    }

    # FUNCTION: sqlite_get_columns
    /**
    * Get a list of columns in a table.
    * 
    * @param SQLite3 $db The database connection.
    * @param string $table_name The name of the table.
    * 
    * @return array An array of column names.
    */
    public function sqlite_get_columns(SQLite3 $db, string $table_name) : array {
        try {
            $query = 'PRAGMA table_info("'.$table_name.'");';
            $result = $db->query($query);
            $columns = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $columns[] = [
                    "name" => $row['name'],
                    "type" => $row['type'],
                ];
            }
            return $this->res("SUCCESS", $columns);
        } catch (Exception $e) {
            return $this->res("ERROR", $e->getMessage());
        }
    }

    # FUNCTION: sqlite_query
    /**
    * Execute a query.
    * 
    * @param SQLite3 $db The database connection.
    * @param string $query The query to execute.
    */
    public function sqlite_query(?SQLite3 $db, $query) : array {
        try {
            if (!$db) {
                return $this->res("ERROR", "Database connection not found.");
            }
            // $start  = $db->exec('BEGIN');
            $query  = $db->query($query);
            // $commit = $db->exec('COMMIT');
            if ($query === false) {
                return $this->res("ERROR", $db->lastErrorMsg());
            }
            return $this->res("SUCCESS", $query);
        } catch (Exception $e) {
            return $this->res("ERROR", $e->getMessage());
        }
    }

    # FUNCTION: sqlite_insert
    /**
    * Insert data into a table.
    * 
    * @param SQLite3 $db The database connection.
    * @param string $table The name of the table.
    * @param array $data An associative array of column names and values. The key is the column name, the value is the value to insert.
    *              Example: ["column_name" => "value"]
    * 
    * @return bool TRUE on success, FALSE on failure.
    */
    public function sqlite_insert(SQLite3 $db, string $table, array $data) : array {
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
                return $this->res("ERROR", $db->lastErrorMsg());
            }
            return $this->res("SUCCESS", $insert);
        } catch (Exception $e) {
            return $this->res("ERROR", $e->getMessage());
        }
    }

    # FUNCTION: sqlite_select
    /**
    * Select data from a table.
    * 
    * @param SQLite3 $db The database connection.
    * @param string $table The name of the table.
    * @param array $columns An array of column names to select.
    * @param string $where The WHERE clause.
    * @param array $params An array of parameters to bind to the WHERE clause.
    * @param string $order The ORDER BY clause.
    * @param string $limit The LIMIT clause.
    */
    public function sqlite_select(SQLite3 $db, string $table, array $columns = ["*"], string $where = "", array $params = [], string $order = "", string $limit = "") : array {
        try {
            if (!sqlite_table_exists($db, $table)) {
                return $this->res("ERROR", "Table $table does not exist.");
            }
            $columns = implode(', ', $columns);
            $query = "SELECT $columns FROM $table";
            if (!empty($where)) {
                $query .= " WHERE $where";
            }
            if (!empty($order)) {
                $query .= " ORDER BY $order";
            }
            if (!empty($limit)) {
                $query .= " LIMIT $limit";
            }
            
            $stmt = $db->prepare($query);
            foreach ($params as $i => $value) {
                $stmt->bindValue($i+1, $value, is_int($value) ? SQLITE3_INTEGER : SQLITE3_TEXT);
            }
            
            $result = $stmt->execute();
            if ($result === false) {
                return $this->res("ERROR", $db->lastErrorMsg());
            }
            if ($result->numColumns() == 0) {
                return $this->res("ERROR", "No columns returned.");
            }
            return $this->res("SUCCESS", $result);
        } catch (Exception $e) {
            return $this->res("ERROR", $e->getMessage());
        }
    }

    # FUNCTION: sqlite_update
    /**
    * Update data in a table.
    * 
    * @param SQLite3 $db The database connection.
    * @param string $table The name of the table.
    * @param array $data An associative array of column names and values. The key is the column name, the value is the value to update.
    *              Example: ["column_name" => "value"]
    * @param string $where The WHERE clause.
    * @param array $params An array of parameters to bind to the WHERE clause.
    * 
    * @return bool TRUE on success, FALSE on failure.
    */
    public function sqlite_update(SQLite3 $db, string $table, array $data, string $where, array $params) : array {
        try {
            $set = "";
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
            return $this->res("SUCCESS", $stmt->changes > 0);
        } catch (Exception $e) {
            return $this->res("ERROR", $e->getMessage());
        }
    }

    # FUNCTION: sqlite_delete
    /**
    * Delete data from a table.
    * 
    * @param SQLite3 $db The database connection.
    * @param string $table The name of the table.
    * @param string $where The WHERE clause.
    * @param array $params An array of parameters to bind to the WHERE clause.
    * 
    * @return bool TRUE on success, FALSE on failure.
    */
    public function sqlite_delete(SQLite3 $db, string $table, string $where, array $params) : array {
        try {
            $query = "DELETE FROM $table WHERE $where";
        
            $stmt = $db->prepare($query);
            $i = 1;
            foreach ($params as $value) {
                $stmt->bindValue($i, $value, is_int($value) ? SQLITE3_INTEGER : SQLITE3_TEXT);
                $i++;
            }
            
            $stmt->execute();
            return $this->res("SUCCESS", $stmt->changes > 0);
        } catch (Exception $e) {
            return $this->res("ERROR", $e->getMessage());
        }
    }

    # FUNCTION: sqlite_version
    /**
    * Get the SQLite version.
    */
    public function sqlite_version($filter = Null) : array {
        if ($filter) {
            return $this->res("SUCCESS", !empty(SQLite3::version()[$filter]) ? SQLite3::version()[$filter] : "Unknown");
        }
        return $this->res("SUCCESS", json_encode(SQLite3::version()));
    }

    # FUNCTION: sqlite_function
    /**
    * SQLite function wrapper.
    * 
    * @param func_name The sqlite_* function to run.
    * @param params The parameters to pass to the function.
    */
    public function sqlite_function($func_name, ...$params) : array {
        try {
            $func_name = "sqlite_".str_replace("sqlite_", "", $func_name);
            if (!function_exists($func_name)) {
                return $this->res("ERROR", "Function $func_name does not exist.");
            }
            $call        = call_user_func($func_name, ...$params);
            return $this->res($call["status"], $call["data"]);
            $call_status = $call["status"];
            $call_data   = $call["data"];
            if (is_string($call_data)) {
                $call_data = clean($call_data);
            }
            if ($call_status == "ERROR") {
                return $this->res("ERROR", $call_data);
            }
            if ($call_data["status"] == "ERROR") {
                return $this->res("ERROR", $call_data);
            }
            return $this->res($call_status, $call_data);

        } catch (Exception $e) {
            return $this->res("ERROR", $e->getMessage());
        }
    }

}