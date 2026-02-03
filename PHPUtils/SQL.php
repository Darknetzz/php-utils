<?php

declare(strict_types=1);

namespace PHPUtils;

# ──────────────────────────────────────────────────────────────────────────────────────────────── #
#                                                SQL                                               #
# ──────────────────────────────────────────────────────────────────────────────────────────────── #
/**
 * Class SQL
 * 
 * A class to handle SQL connections and queries
 * 
 * @package PHPUtils
 */
class SQL extends Base {
    
    private const RETURN_RESULT = 'result';
    private const RETURN_ID = 'id';
    
    private ?\mysqli $connection = null;
    
    /**
     * __construct
     * 
     * @param Debugger|null $debugger Optional Debugger instance
     * @param Vars|null $vars Optional Vars instance
     * @param bool $verbose Whether to enable verbose debugging
     */
    public function __construct(?Debugger $debugger = null, ?Vars $vars = null, bool $verbose = true) {
        parent::__construct($debugger, $vars, $verbose);
    }
    
    /**
     * setConnection
     * 
     * Set the database connection (dependency injection)
     *
     * @param \mysqli $connection The mysqli connection object
     * @return void
     */
    public function setConnection(\mysqli $connection): void {
        $this->connection = $connection;
    }
    
    /**
     * getConnection
     * 
     * Get the current database connection
     *
     * @return \mysqli|null
     */
    public function getConnection(): ?\mysqli {
        return $this->connection;
    }
    
    /**
     * connectHost
     * 
     * Connect to a host (without a database specified)
     *
     * @param  string $host The host to connect to
     * @param  string $user The username to use
     * @param  string $pass The password to use
     * @return \mysqli The mysqli object
     * @throws \RuntimeException If connection fails
     */
    public function connectHost(string $host, string $user, string $pass): \mysqli {
        $connection = new \mysqli($host, $user, $pass);
        if ($connection->connect_error) {
            throw new \RuntimeException("Connection failed: " . $connection->connect_error);
        }
        return $connection;
    }
    
    /* ────────────────────────────────────────────────────────────────────────── */
    /*                                 Connect DB                                 */
    /* ────────────────────────────────────────────────────────────────────────── */    
    /**
     * connectDB
     * 
     * Connect to a database
     *
     * @param  string $host The host to connect to
     * @param  string $user The username to use
     * @param  string $pass The password to use
     * @param  string|null $db The database to connect to, defaults to null
     * @return \mysqli The mysqli object
     * @throws \RuntimeException If connection fails
     */
    public function connectDB(string $host, string $user, string $pass, ?string $db = null): \mysqli {
        $connection = new \mysqli($host, $user, $pass, $db);
        if ($connection->connect_error) {
            throw new \RuntimeException("Connection failed: " . $connection->connect_error);
        }
        return $connection;
    }
    
    /* ────────────────────────────────────────────────────────────────────────── */
    /*                 MAIN SQL QUERY WRAPPER [IMPORTANT FUNCTION]                */
    /* ────────────────────────────────────────────────────────────────────────── */    
    /**
     * executeQuery
     * 
     * Execute a query
     *
     * @param  string $statement The SQL statement to execute
     * @param  array $params The parameters to bind to the statement
     * @param  string|null $return The type of return to expect (self::RETURN_RESULT or self::RETURN_ID)
     * @return \mysqli_result|int Returns mysqli_result for SELECT queries, int for INSERT (insert_id), or false for non-result queries
     * @throws \RuntimeException If connection not set or query fails
     * @throws \InvalidArgumentException If invalid return type specified
     */
    public function executeQuery(string $statement, array $params = [], ?string $return = self::RETURN_RESULT): \mysqli_result|int|false {
        if ($this->connection === null) {
            // Fallback to global for backward compatibility, but log a warning
            global $sqlcon;
            if (isset($sqlcon) && $sqlcon instanceof \mysqli) {
                $this->connection = $sqlcon;
            } else {
                throw new \RuntimeException("Database connection not set. Use setConnection() or set global \$sqlcon for backward compatibility.");
            }
        }
    
        $query = $this->connection->prepare($statement);
        if ($query === false) {
            throw new \RuntimeException("Failed to prepare query: " . $this->connection->error);
        }
    
        $paramsCount = count($params);
        if ($paramsCount > 0) {
            // Infer types from parameter values
            $types = implode('', array_map(function($param) {
                if (is_int($param)) return 'i';
                if (is_float($param)) return 'd';
                if (is_string($param)) return 's';
                return 'b'; // blob for anything else
            }, $params));
            $query->bind_param($types, ...$params);
        }
    
        if (!$query->execute()) {
            throw new \RuntimeException("Query execution failed: " . $this->connection->error);
        }
        
        $result = $query->get_result();

        if ($return === self::RETURN_ID) {
            return $this->connection->insert_id;
        }
    
        if ($return === self::RETURN_RESULT) {
            // For statements that do not produce a result set (e.g. INSERT/UPDATE/DELETE),
            // mysqli_stmt::get_result() returns false. Callers must handle this case.
            return $result;
        }
        
        throw new \InvalidArgumentException("Invalid return type specified for `executeQuery`. Valid options are '" . self::RETURN_RESULT . "' or '" . self::RETURN_ID . "'.");
    }
    /* ────────────────────────────────────────────────────────────────────────── */
        
    /**
     * save_result
     * 
     * Save the result of a query to an array
     *
     * @param  \mysqli_result $query The query to save
     * @return array The result of the query
     */
    public function save_result(\mysqli_result $query): array {
        $result = [];
        while ($row = $query->fetch_assoc()) {

            # Save to array with key ID if it exists
            if (!empty($row['id'])) {
                $result[$row['id']] = $row;
                continue;
            }

            # If not, just append it to the array
            $result[] = $row;
        }
        return $result;
    }

    /* ───────────────────────────────────────────────────────────────────── */
    /*                                 Error                                 */
    /* ───────────────────────────────────────────────────────────────────── */    
    /**
     * error
     * 
     * Get the last error from the SQL connection
     *
     * @return string The last error from the SQL connection, or empty string if no connection
     */
    public function error(): string {
        if ($this->connection === null) {
            global $sqlcon;
            if (isset($sqlcon) && $sqlcon instanceof \mysqli) {
                return $sqlcon->error;
            }
            return "";
        }
        return $this->connection->error;
    }
    
    
    // ------------------------[ setupDB ]------------------------ //
    /**
     * setupDB
     * 
     * Setup database tables (placeholder method)
     *
     * @param \mysqli $sqlcon The database connection
     * @param array $templateArray Array of SQL statements to execute
     * @return void
     */
    public function setupDB(\mysqli $sqlcon, array $templateArray): void {
        
        /*
        
        $sqlcon example:
            $sqlcon = new mysqli('127.0.0.1', 'root', '');
            
            $templateArray example:
            
            $sql_template["artister"] = [
                "ALTER TABLE artister ADD `navn` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;",
                "ALTER TABLE artister ADD `description` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;",
                "ALTER TABLE artister ADD PRIMARY KEY (`id`);",
                "ALTER TABLE artister MODIFY `id` int NOT NULL AUTO_INCREMENT;",
            ];
            
            $sql_template["brukere"] = [
                "ALTER TABLE brukere ADD `navn` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;",
                "ALTER TABLE brukere ADD `description` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;",
                "ALTER TABLE brukere ADD PRIMARY KEY (`id`);",
                "ALTER TABLE brukere MODIFY `id` int NOT NULL AUTO_INCREMENT;",
            ];
            
            */
        }

        /**
         * search
         * 
         * Search a table for a string
         * 
         * @param  string $tablename The table to search
         * @param  string $search The string to search for
         * @param  array $columns The columns to search in
         * @param  array $options The options for the search
         *                      - delimiter: The string to split the search string by
         *                      - limit: The maximum number of results to return (default 0 = no limit)
         *                      - casesensitive: Whether the search should be case sensitive (default False)
         *                      - strip_chars: Whether to strip special characters from the search string (default True)
         *                      - search_min_len: The minimum length of the search string (default 0 - no minimum length)
         * 
         * @return \mysqli_result The result of the search
         */
        public function search(string $tablename, string $search, array $columns = ["name"], array $options = []): \mysqli_result {
            $validatedTable = $this->validateIdentifier($tablename, 'table');
            $validatedColumns = [];
            foreach ($columns as $column) {
                $validatedColumns[] = $this->validateIdentifier($column, 'column');
            }

            # Default options
            $delimiter      = (empty($options["delimiter"]) ? " " : $options["delimiter"]);
            $limit          = (empty($options['limit']) ? 0 : intval($options['limit']));
            $case_sensitive = (empty($options['casesensitive']) ? False : $options['casesensitive']);
            $strip_chars    = (empty($options['strip_chars']) ? True : $options['strip_chars']);
            $search_min_len = (empty($options['search_min_len']) ? 0 : $options['search_min_len']);
            $offset         = (empty($options['offset']) ? 0 : $options['offset']);

            $keywords = explode($delimiter, $search);
            $searchQuery = "SELECT *, (";
            $conditions = [];
            $searchParams = [];
            foreach ($keywords as $keyword) {
                if ($strip_chars === True) {
                    $keyword = preg_replace("/[^a-zA-Z0-9]/", "", $keyword);
                }
                foreach ($validatedColumns as $column) {
                    if ($case_sensitive === True) {
                        $conditions[] = "(CASE WHEN REGEXP_REPLACE(`$column`, '[^a-zA-Z0-9]', '') LIKE ? THEN 2 ELSE 0 END)";
                        $searchParams[] = "%".$keyword."%";
                    } else {
                        $conditions[] = "(CASE WHEN LOWER(REGEXP_REPLACE(`$column`, '[^a-zA-Z0-9]', '')) LIKE LOWER(?) THEN 1 ELSE 0 END)";
                        $searchParams[] = "%".strtolower($keyword)."%";
                    }
                }
            }
            $searchQuery .= implode(" + ", $conditions) . ") AS relevance";
            $searchQuery .= " FROM `$validatedTable` WHERE " . implode(" OR ", $conditions) . " HAVING relevance > 1";
            $searchQuery .= " ORDER BY relevance DESC";
            if ($limit > 0) {
                if ($offset > 0) {
                    $searchQuery .= " LIMIT " . $offset . ", " . $limit;
                } else {
                    $searchQuery .= " LIMIT " . $limit;
                }
            }
            $searchResult = $this->executeQuery($searchQuery, array_merge($searchParams, $searchParams));
            if ($searchResult instanceof \mysqli_result) {
                return $searchResult;
            }
            throw new \RuntimeException("Search query did not return a valid result set.");
        }

        /**
         * validateIdentifier
         * 
         * Validate a SQL identifier (table or column name) to prevent SQL injection.
         * Only allows alphanumeric characters and underscores.
         * Identifiers must start with a letter or underscore.
         * 
         * @param  string $identifier The identifier to validate
         * @param  string $type The type of identifier (e.g., 'table', 'column') for error messages
         * @return string The validated identifier
         * @throws \InvalidArgumentException If the identifier contains invalid characters
         */
        private function validateIdentifier(string $identifier, string $type = 'identifier'): string {
            // Check for empty identifier
            if ($identifier === '') {
                throw new \InvalidArgumentException(
                    "Invalid $type name: cannot be empty."
                );
            }
            
            // Ensure identifier starts with a letter or underscore, followed by alphanumeric characters or underscores
            // This matches standard SQL naming conventions and prevents SQL injection
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
                throw new \InvalidArgumentException(
                    "Invalid $type name: '$identifier'. Must start with a letter or underscore and contain only alphanumeric characters and underscores."
                );
            }
            
            return $identifier;
        }

        /**
         * getUniqueRows
         * 
         * Get the unique rows from a table
         * 
         * @param  string $table The table to get the rows from
         * @param  string $column The column that should be unique
         * @return array The unique rows
         * @throws \RuntimeException If query fails
         * @throws \InvalidArgumentException If table or column name is invalid
         */
        public function getUniqueRows(string $table, string $column): array {
            // Validate identifiers to prevent SQL injection
            $validatedTable = $this->validateIdentifier($table, 'table');
            $validatedColumn = $this->validateIdentifier($column, 'column');
            
            $getRows = $this->executeQuery("SELECT DISTINCT `$validatedColumn` FROM `$validatedTable` ORDER BY `$validatedColumn` ASC");
            if (!($getRows instanceof \mysqli_result)) {
                throw new \RuntimeException("getUniqueRows query did not return a valid result set.");
            }
            $rows = [];
            while ($row = $getRows->fetch_assoc()) {
                if (!empty($row[$validatedColumn])) {
                    $rows[] = $row[$validatedColumn];
                }
            }
            return $rows;
        }

        /**
         * countRows
         * 
         * Count the number of rows in a table
         * 
         * @param  string $table The table to count the rows in
         * @param  string|null $column The column to filter by
         * @param  string|null $value The value to filter by
         * @return int The number of rows in the table
         * @throws \RuntimeException If query fails
         * @throws \InvalidArgumentException If table or column name is invalid
         */
        public function countRows(string $table, ?string $column = null, ?string $value = null): int {
            // Validate table name to prevent SQL injection
            $validatedTable = $this->validateIdentifier($table, 'table');
            
            $query = "SELECT COUNT(*) FROM `$validatedTable`";
            if (!empty($column) && !empty($value)) {
                // Validate column name to prevent SQL injection
                $validatedColumn = $this->validateIdentifier($column, 'column');
                $query .= " WHERE `$validatedColumn` = ?";
                $result = $this->executeQuery($query, [$value]);
            } else {
                $result = $this->executeQuery($query);
            }
            if (!($result instanceof \mysqli_result)) {
                throw new \RuntimeException("countRows query did not return a valid result set.");
            }
            $count = $result->fetch_row()[0];
            return (int)$count;
        }
        




    } # END CLASS