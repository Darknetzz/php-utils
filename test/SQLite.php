<!DOCTYPE html>
<title>SQLite3 API</title>
<html lang="en" data-bs-theme="dark">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    body {
        background-color: black;
        color: white;
    }
    .output {
        background-color: #111;
        color: green;
        border: 1px solid white;
        padding: 10px;
        max-height: 200px;
        overflow-y: auto;
    }
    .debug {
        background-color: black;
        color: orange;
        border: 1px solid white;
        padding: 10px;
    }
    .action-form th {
        background-color: #111;
        color: white;
        padding: 5px;
    }
</style>

<?php
require_once("functions.php");

$addBtnClass     = "m-1 btn btn-sm btn-outline-dark text-success bg-success-subtle";
$delBtnClass     = "m-1 btn btn-sm btn-outline-dark text-danger bg-danger-subtle";
$selBtnClass     = "m-1 btn btn-sm btn-outline-dark text-primary bg-primary-subtle";
$neutralBtnClass = "m-1 btn btn-sm btn-outline-dark text-secondary bg-secondary-subtle";
$genBtnClass     = "m-1 btn btn-sm btn-outline-dark text-info bg-info-subtle";
$headerClass     = function($type = "primary") { return "bg-$type-subtle"; };

function icon($icon, $size = 1, $class = "mx-1") {
    return "<i class='bi bi-$icon $class' style='font-size:${size}rem'></i>";
}

$outputCards  = "";
$output       = "";
$dbname       = (!empty($_POST['database']) ? $_POST['database'] : "test_db");
$dbInput      = "<input type='text' name='database' class='form-control' value='$dbname'>";
$table        = (!empty($_POST['table']) ? $_POST['table'] : "test_table");
$tableInput   = "<input type='text' name='table' class='form-control' value='$table'>";
$columnsClass = "";
$dataClass    = "";
$customQuery  = (!empty($_POST['query']) ? $_POST['query'] : "SELECT * FROM $table;");
$do           = (!empty($_POST['do']) ? $_POST['do'] : Null);

$sqlite_version = sqlite_function("version", "versionString")["data"];
$create_db      = sqlite_function("create_db", $dbname);
$sqlitecon      = $create_db["data"];

$columns = (!empty($_POST['columns']) ? $_POST['columns'] : Null);
if (!empty($columns)) {
    $columns = str_replace("\r", "", $columns);
    $columns = str_replace("\n", "", $columns);
    $columns = trim($columns);
    $columns = json_decode($columns, true);
} else {
    $columns = [
        "column1" => "TEXT",
        "column2" => "INTEGER"
    ];
}

$data = (!empty($_POST['data']) ? $_POST['data'] : Null);
if (!empty($data)) {
    $data = str_replace("\r", "", $data);
    $data = str_replace("\n", "", $data);
    $data = trim($data);
    $data = json_decode($data, true);
} else {
    $data = [
        "column1" => "value1",
        "column2" => 2
    ];
}

/* ───────────────────────────── ACTIONS ───────────────────────────── */
if (!empty($do)) {
    do {
        if (empty($sqlitecon)) {
            $output .= "> Database '$dbname' not found or <code>\$sqlitecon</code> is empty.<br>";
            $output .= print_r($sqlitecon, true) . "<br>";
            break;
        }

        $output     .= "> Running action: $do";

        if ($do == "getdbs") {
            $run         = sqlite_function("get_dbs");
            if (is_array($run["data"]) && !empty($run["data"])) {
                $dbs = $run["data"];
                if (is_array($dbs)) {
                    $dbInput  = "<select name='database' class='form-select text-success'>";
                    foreach ($dbs as $db) {
                        $dbInput .= "<option value='$db'>$db</option>";
                    }
                    $dbInput .= "</select>";
                }
            }
        }

        if ($do == "gettables") {
            $run         = sqlite_function("get_tables", $sqlitecon);
            if (is_array($run["data"]) && !empty($run["data"])) {
                $tables = $run["data"];
                $tableInput  = "<select name='table' class='form-select text-success'>";
                foreach ($tables as $table) {
                    $tableInput .= "<option value='$table'>$table</option>";
                }
                $tableInput .= "</select>";
            }
        }

        if ($do == "getcolumns") {
            $run           = sqlite_function("get_columns", $sqlitecon, $table);
            $columns       = $run["data"];
            $columnsClass  = "text-danger";
            if (is_array($columns) && !empty($columns)) {
                $columns = array_column($columns, "name");
                $columns = array_combine($columns, array_fill(0, count($columns), "TEXT"));
                $columnsClass  = "text-success";
            }
        }

        if ($do == "getdata" || $do == "selectdata") {
            # Select data from the table.
            $run        = sqlite_function("select", $sqlitecon, $table);
            $data       = $run["data"];
            $dataClass  = "text-danger";
            if (is_array($data) && !empty($data)) {
                $dataClass = "text-success";
            }
        }

        if ($do == "createtable") {
            $run     = sqlite_function("create_table", $sqlitecon, $table, $columns);
        }

        if ($do == "insertdata") {
            $testData = [
                "column1" => "Some text",
                "column2" => mt_rand(0,100)
            ];
            $output .= json_encode($testData, JSON_PRETTY_PRINT) . "<br>";
            $run     = sqlite_function("insert", $sqlitecon, $table, $testData);
        }

        if ($do == "emptytable") {
            $run     = sqlite_function("empty_table", $sqlitecon, $table);
        }

        if ($do == "droptable" || $do == "deletetable") {
            $run     = sqlite_function("drop_table", $sqlitecon, $table);
        }

        if ($do == "dropdb" || $do == "deletedb") {
            $run     = sqlite_function("drop_db", $dbname);
        }

        if ($do == "deletesqlite") {
            $run     = unlink("$dbname.sqlite");
        }

        if ($do == "version") {
            $run     = sqlite_function("version");
        }

        if ($do == "customquery") {
            $output .= "<pre>$customQuery</pre><br>";
            $run     = sqlite_function("query", $sqlitecon, $customQuery);
        }
        if (empty($output)) {
            $output .= "No output.";
        }
        if (empty($run)) {
            $run = res("ERROR", "Invalid action");
        }
        $returnStatus  = $run["status"];
        $returnType    = gettype($run["data"]);
        $returnData    = json_encode($run["data"], JSON_PRETTY_PRINT);
        if ($returnStatus == "ERROR") {
            $output .= "<pre class='text-danger'>$returnData</pre>";
        }
        $output       .= "[$returnStatus] $returnType:<br>";
        $output       .= "<pre>$returnData</pre>";

    /* ───────────────────────────────────────────────────────────────────── */
    /*                              OUTPUT CARDS                             */
    /* ───────────────────────────────────────────────────────────────────── */
        if (!empty($do)) {
            $outputCards .= "<div class='card m-2'>
            <h4 class='card-header bg-info-subtle'>".icon("card-text", 1.5)." Output</h4>
            <div class='card-body'>
            <pre class='output'>$output</pre>
            </div>
            </div>";
    
            $debug = "
                do                         : $do
                Database                   : $dbname
                Table                      : $table
                [$returnStatus] $returnType: $returnData
            ";
            $outputCards .= "
            <div class='card m-2'>
            <h4 class='card-header bg-warning-subtle'>".icon("bug", 1.5)." Debug</h4>
            <div class='card-body'>
            <div class='accordion' id='debugAccordion'>
            <div class='accordion-item'>
            <h2 class='accordion-header' id='headingDebug'>
            <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapseDebug' aria-expanded='false' aria-controls='collapseDebug'>
            Show Debug Info
            </button>
            </h2>
            <div id='collapseDebug' class='accordion-collapse collapse' aria-labelledby='headingDebug' data-bs-parent='#debugAccordion'>
            <div class='accordion-body'>
            <pre class='output debug'>
            ".str_replace("  ", "", $debug)."
            </pre></div></div></div></div></div>";
            break;
        }
    } while (false);
}
/* ───────────────────────────────────────────────────────────────────── */

?>

<div class="container-fluid p-5">
    <h1><a href="index.php">SQLite3 API</a></h1>
    <p class="text-muted">SQLite version <?= $sqlite_version; ?></p>
    <div class="row">
        <div class="col">
            <div class="card border border-primary">
            <h2 class="card-header bg-primary-subtle"><?= icon("fire", 2) ?> Actions</h2>
            <div class="card-body p-3">
                <p>Choose an action:</p>
                <form action="" method="POST">
                <table class="table table-hover action-form">
                    <tr>
                        <th class="<?= $headerClass() ?>">Variable</th>
                        <th class="<?= $headerClass() ?>">Value</th>
                        <th class="<?= $headerClass() ?>">Action</th>
                    </tr>
                    <tr>
                        <th class="<?= $headerClass() ?>">
                            <?= icon("database") ?> Database
                        </th>
                        <td>
                            <div class="input-group">
                                <?= $dbInput ?>
                                <span class="input-group-text">.sqlite</span>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group border bg-dark-subtle p-1">
                                <button name="do" value="getdbs" class="<?= $selBtnClass ?>"><?= icon("eyeglasses") ?> Get</button>
                                <button name="do" value="createdb" class="<?= $addBtnClass ?>"><?= icon("database-add") ?> Create</button>
                                <button name="do" value="dropdb" class="<?= $delBtnClass ?>"><?= icon("trash3") ?> Delete</button>
                            </div>
                        </td>
                    </tr>
                    <br>
                    <tr>
                        <th class="<?= $headerClass() ?>">
                            <?= icon("layout-text-window-reverse") ?> Table
                        </th>
                        <td>
                            <div class="input-group">
                                <?= $tableInput ?>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group border bg-dark-subtle p-1">
                                <button name="do" value="gettables" class="<?= $selBtnClass ?>"><?= icon("eyeglasses") ?> Get</button>
                                <button name="do" value="deletetable" class="<?= $delBtnClass ?>"><?= icon("trash3") ?> Delete</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="<?= $headerClass() ?>">
                            <?= icon("layout-three-columns") ?> Columns
                        </th>
                        <td>
                            <textarea class="form-control bg-dark-subtle <?= $columnsClass ?>" rows="5" type="text" name="columns" placeholder="Column names"><?=
                                trim(json_encode($columns, JSON_PRETTY_PRINT))
                            ?></textarea>
                        </td>
                        <td>
                            <div class="btn-group border bg-dark-subtle p-1">
                                <button name="do" value="getcolumns" class="<?= $selBtnClass ?>"><?= icon("eyeglasses") ?> Get</button>
                                <button name="do" value="createtable" class="<?= $addBtnClass ?>"><?= icon("table") ?> Create</button>
                                <button name="do" value="emptytable" class="<?= $delBtnClass ?>"><?= icon("trash3") ?> Delete</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="<?= $headerClass() ?>">
                            <?= icon("card-text") ?> Data
                        </th>
                        <td>
                            <textarea class="form-control bg-dark-subtle <?= $dataClass ?>" rows="5" type="text" name="data" placeholder="Data"><?= 
                                trim(json_encode($data, JSON_PRETTY_PRINT))
                            ?></textarea>
                        </td>
                        <td>
                            <div class="btn-group border bg-dark-subtle p-1">
                                <button name="do" value="selectdata" class="<?= $selBtnClass ?>"><?= icon("list") ?> Get Rows</button>
                                <button name="do" value="getdata" class="<?= $selBtnClass ?>"><?= icon("card-text") ?> Get Data</button>
                                <button name="do" value="generatedata" class="<?= $genBtnClass ?>"><?= icon("shuffle") ?> Generate Data</button>
                                <button name="do" value="insertdata" class="<?= $addBtnClass ?>"><?= icon("clipboard-plus") ?> Insert Data</button>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="btn-group m-1">
                    <button name="do" value="deletesqlite" class="<?= $delBtnClass ?>">Delete .sqlite file</button>
                    <button name="do" value="version" class="<?= $neutralBtnClass ?>">SQLite Version</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="col">
    <div class="card">
        <h2 class="card-header bg-danger-subtle"><?= icon("code", 2) ?> Custom Query</h2>
        <div class="card-body">
            <p>Run a custom query:</p>
            <form action="" method="POST">
                <textarea name="query" class="form-control bg-dark-subtle" rows="10"><?= $customQuery ?></textarea>
                <button name="do" value="customquery" class="<?= $addBtnClass ?>">Run Query</button>
            </form>
        </div>
    </div>

<hr>

<?php
echo $outputCards;

?>

</div>
</div>
</div>

</html>