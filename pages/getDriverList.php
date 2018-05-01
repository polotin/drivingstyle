<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/1
 * Time: 16:45
 */
$csv_dir = "";
if (isset($_GET["csv_dir"])) {
    $csv_dir = $_GET["csv_dir"];
} else echo "";
$files_folder = scandir($csv_file_dir);
$file_names = array();
foreach ($files_folder as $name) {
    if (startWith("CCHN_" . $driver_id, $name) && endWith(".csv", $name)) {
        echo "<script type=text/javascript>console.log('" . $name . "')</script>";
        $file_names[] = $name;
    }
    if (!empty($file_names)) {
        echo implode(',',$file_names);
    }
}
function startWith($needle, $name)
{
    return strpos($name, $needle) === 0;
}
function endWith($needle, $name)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }
    return (substr($name, -$length) === $needle);
}