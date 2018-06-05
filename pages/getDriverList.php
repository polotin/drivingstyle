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

if(is_dir($csv_dir)){
    $files_folder = scandir($csv_dir);
}else{
    echo "";
    return;
}

$file_names = array();

foreach ($files_folder as $name) {
    if (startWith("CCHN", $name) && endWith(".csv", $name)) {
        $file_names[] = $name;
    }
}
if (!empty($file_names)) {
    echo implode(',',$file_names);
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