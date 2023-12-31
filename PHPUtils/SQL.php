<?php
# ──────────────────────────────────────────────────────────────────────────────────────────────── #
#                                                SQL                                               #
# ──────────────────────────────────────────────────────────────────────────────────────────────── #
class SQL extends Base {
    function __construct() {

    }

    function connectHost(string $host, string $user, string $pass) {
        return new mysqli($host, $user, $pass);
    }
    
    /* ────────────────────────────────────────────────────────────────────────── */
    /*                                 Connect DB                                 */
    /* ────────────────────────────────────────────────────────────────────────── */
    function connectDB(string $host, string $user, string $pass, string $db = null) {
        return new mysqli($host, $user, $pass, $db);
    }
    
    /* ────────────────────────────────────────────────────────────────────────── */
    /*                 MAIN SQL QUERY WRAPPER [IMPORTANT FUNCTION]                */
    /* ────────────────────────────────────────────────────────────────────────── */
    function executeQuery(string $statement, array $params = []) {
        global $sqlcon;
    
        # allow for the statement to contain constants directly (probably not such a good idea)
        # https://stackoverflow.com/questions/1563654/quoting-constants-in-php-this-is-a-my-constant
        // $statement = str_replace(array_keys(get_defined_constants(true)['user']), get_defined_constants(true)['user'], $statement);
    
        $query = $sqlcon->prepare($statement);
    
        $paramsCount = count($params);
        $paramscs = "No parameters";
        if ($paramsCount > 0) {
            $types = '';
            foreach ($params as $n => $val) { # &$val ?
                $types .= 's';
                # Hey, I know this looks kinda weird, BUT: 
                # https://stackoverflow.com/questions/36777813/using-bind-param-with-arrays-and-loops
            }
            $query->bind_param($types, ...$params);
            $paramscs = implode(", ", $params);
        }
    
        $query->execute();
        $result = $query->get_result();
    
        if ($sqlcon->error) {
            die("<div class='alert alert-danger'>Fatal error: $sqlcon->error</div>");
        }
    
        // if ($result->num_rows < 1) {
        //     return $result; # we still want to return the object (even if it's empty)
        //     # ok? so why do an if check then??
        // }
    
        return $result;
    }
    /* ────────────────────────────────────────────────────────────────────────── */
    
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
        




    } # END CLASS
        ?>