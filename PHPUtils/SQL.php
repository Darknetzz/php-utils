<?php
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
    function __construct() {
        # TODO: Create a __construct class with the following
        # (just make sure you don't break anything somewhere else):
            // $this->sqlcon = new mysqli($host, $user, $pass, $db);
            // if ($this->sqlcon->connect_error) {
            //     throw new Exception("Connection failed: " . $this->sqlcon->connect_error);
            // }
    }
    
    /**
     * connectHost
     * 
     * Connect to a host (without a database specified)
     *
     * @param  string $host The host to connect to
     * @param  string $user The username to use
     * @param  string $pass The password to use
     * @return mysqli The mysqli object
     */
    function connectHost(string $host, string $user, string $pass) {
        return new mysqli($host, $user, $pass);
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
     * @param  string $db The database to connect to, defaults to null
     * @return mysqli The mysqli object
     */
    function connectDB(string $host, string $user, string $pass, ?string $db = null) {
        return new mysqli($host, $user, $pass, $db);
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
     * @param  string $return The type of return to expect (id, array, object)
     * @return void
     */
    function executeQuery(string $statement, array $params = [], ?string $return = null) {
        global $sqlcon;
    
        # allow for the statement to contain constants directly (probably not such a good idea)
        # https://stackoverflow.com/questions/1563654/quoting-constants-in-php-this-is-a-my-constant
        // $statement = str_replace(array_keys(get_defined_constants(true)['user']), get_defined_constants(true)['user'], $statement);
    
        $query = $sqlcon->prepare($statement);
    
        $paramsCount = count($params);
        if ($paramsCount > 0) {
            $types = str_repeat('s', $paramsCount);
            $query->bind_param($types, ...$params);
        }
    
        $query->execute();
        $result = $query->get_result();

        if ($return == 'id') {
            $result = $sqlcon->insert_id;
        }
    
        if (!empty($sqlcon->error)) {
            die("<div class='alert alert-danger'>executeQuery() - Fatal error: $sqlcon->error</div>");
        }
    
        return $result;
    }
    /* ────────────────────────────────────────────────────────────────────────── */
        
    /**
     * save_result
     * 
     * Save the result of a query to an array
     *
     * @param  mysqli_result $query The query to save
     * @return array The result of the query
     */
    function save_result(mysqli_result $query) {
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
     * @return string The last error from the SQL connection
     */
    function error() {
        global $sqlcon;
        return $sqlcon->error;
    }
    
    
    // ------------------------[ setupDB ]------------------------ //
    function setupDB($sqlcon, $templateArray) {
        
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
         *                       - delimiter: The string to split the search string by
         *                       - limit: The maximum number of results to return (default 0 = no limit)
         *                       - casesensitive: Whether the search should be case sensitive (default False)
         *                       - strip_chars: Whether to strip special characters from the search string (default True)
         * 
         * @return mysqli_result The result of the search
         */
        function search(string $tablename, string $search, array $columns = ["name"], array $options = []) {
            global $sql;

            # Default options
            $delimiter      = (empty($options["delimiter"]) ? " " : $options["delimiter"]);
            $limit          = (empty($options['limit']) ? 0 : intval($options['limit']));
            $case_sensitive = (empty($options['casesensitive']) ? False : $options['casesensitive']);
            $strip_chars    = (empty($options['strip_chars']) ? True : $options['strip_chars']);

            $search = ($strip_chars ? preg_replace("/[^a-zA-Z0-9\s$delimiter]/", "", $search) : $search);
            $keywords = explode($delimiter, $search);
            $searchQuery = "SELECT *, (";
            $conditions = [];
            $searchParams = [];
            foreach ($keywords as $keyword) {
                $keyword_nospace = str_replace([" ", "-", "_"], "", $keyword);
                foreach ($columns as $column) {
                    if ($case_sensitive) {
                        $conditions[] = "(CASE WHEN `$column` LIKE ? THEN 3 ELSE 0 END)";
                        $conditions[] = "(CASE WHEN `$column` LIKE ? THEN 2 ELSE 0 END)";
                        $conditions[] = "(CASE WHEN REPLACE(REPLACE(REPLACE(`$column`, ' ', ''), '-', ''), '_', '') LIKE ? THEN 1 ELSE 0 END)";
                        $searchParams[] = $keyword;
                        $searchParams[] = "%".$keyword."%";
                        $searchParams[] = "%".$keyword_nospace."%";
                    } else {
                        $conditions[] = "(CASE WHEN LOWER(`$column`) LIKE ? THEN 3 ELSE 0 END)";
                        $conditions[] = "(CASE WHEN LOWER(`$column`) LIKE ? THEN 2 ELSE 0 END)";
                        $conditions[] = "(CASE WHEN REPLACE(REPLACE(REPLACE(LOWER(`$column`), ' ', ''), '-', ''), '_', '') LIKE ? THEN 1 ELSE 0 END)";
                        $searchParams[] = strtolower($keyword);
                        $searchParams[] = "%".strtolower($keyword)."%";
                        $searchParams[] = "%".strtolower($keyword_nospace)."%";
                    }
                }
            }
            $searchQuery .= implode(" + ", $conditions) . ") AS relevance";
            $searchQuery .= " FROM $tablename WHERE " . implode(" OR ", $conditions) . " HAVING relevance > 0";
            $searchQuery .= " ORDER BY relevance DESC";
            if ($limit > 0) {
                $searchQuery .= " LIMIT " . $limit;
            }
            $searchResult = $sql->executeQuery($searchQuery, array_merge($searchParams, $searchParams));
            return $searchResult;
        }

        /**
         * getUniqueRows
         * 
         * Get the unique rows from a table
         * 
         * @param  string $table The table to get the rows from
         * @param  string $column The column that should be unique
         * @return array The unique rows
         */
        function getUniqueRows(string $table, string $column) {
            global $sql;
            $getRows = $sql->executeQuery("SELECT DISTINCT $column FROM $table ORDER BY $column ASC");
            $rows = [];
            while ($row = $getRows->fetch_assoc()) {
                if (!empty($row[$column])) {
                    $rows[] = $row[$column];
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
         * @param  string $column The column to filter by
         * @param  string $value The value to filter by
         * @return int The number of rows in the table
         */
        function countRows(string $table, ?string $column = null, ?string $value = null) {
            global $sql;
            $query = "SELECT COUNT(*) FROM $table";
            if (!empty($column) && !empty($value)) {
                $query .= " WHERE $column = ?";
                $result = $sql->executeQuery($query, [$value]);
            } else {
                $result = $sql->executeQuery($query);
            }
            $count = $result->fetch_row()[0];
            return $count;
        }
        




    } # END CLASS
        ?>