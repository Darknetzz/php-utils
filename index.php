<!DOCTYPE html>
<html data-bs-theme="dark">
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
require_once("PHPUtils/_All.php");
// $gui = new Resources;
// $gui->Bootstrap();
?>

<link rel="stylesheet" href="/_assets/css/bootstrap.min.css">
<link rel="stylesheet" href="/_assets/css/bootstrap-table.min.css">
<script src="/_assets/js/jquery.min.js"></script>
<script src="/_assets/js/bootstrap.min.js"></script>
<script src="/_assets/js/marked.min.js"></script>

<div class="container m-3 p-3">

    <div class='card border border-primary'>
    <h1 class='card-header text-bg-primary'>Classes</h1>
        <div class='card-body'>
            <a href="Docs">Docs</a>
            <table class='table table-hover'>
                <thead class='table-dark'>
                    <tr><th>Class</th><th>Documentation</th><th>Test</th></tr>
                </thead>
            <tbody>
            <?php
                $classFolder = "PHPUtils";
                $docsFolder  = "Docs/classes";
                $testFolder  = "tests";
                $classes = glob($classFolder."/*.php");
                foreach($classes as $class){
                    $classPath  = explode("/", str_replace("\\", "/", $class));
                    $className  = end($classPath);
                    $classTitle = str_replace(".php","",$className);
                    if ($classTitle === "_All") { continue; }

                    $thisDoc  = $docsFolder."/".$classTitle.".html";
                    $testFile = $testFolder."/".$classTitle."Test.php";

                    $btnClass = (file_exists($thisDoc) ? "btn btn-sm btn-outline-success" : "btn btn-sm btn-outline-danger");
                    $docLink  = "<a href='$thisDoc' class='$btnClass' target='_blank'>Doc</a>";

                    $btnClass = (file_exists($testFile) ? "btn btn-sm btn-outline-success" : "btn btn-sm btn-outline-secondary");
                    $testLink = "<a href='$testFolder/' class='$btnClass' target='_blank'>Tests</a>";
            
                    echo "
                    <tr>
                        <td><a href='$class'>$classTitle</a></td>
                        <td>$docLink</td>
                        <td>$testLink</td>
                    </tr>";
                }
            ?>
            </tbody>
            </table>
        </div>
    </div>

</div>

</html>