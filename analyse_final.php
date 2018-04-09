<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>分析结果</title>
    <link rel="stylesheet" href="js/bootstrap/css/bootstrap.min.css">
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/bootstrap/js/bootstrap.min.js"></script>
    <script src="js/pagination.js"></script>
    <script src="js/analyse.js"></script>
</head>
<body>
<?php
include 'ana_result.php';

$file_name = "2_1_new.csv";
$file = fopen($file_name, "r");
$info_list = array();  //存放行车信息
while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容
    $info_list[] = $data;
}

$results = array();//分析结果
$row_num = 0;
foreach ($info_list as $row) {
    if ($row_num == 0) {
        $row_num += 1;
        continue;
    }
    $result = new ana_result();
    $result->time = $row[0];
    $result->driver_id = $row[8];
    $result->trip_id = $row[3];
    $result->speed = $row[1];
    $result->accel = $row[82];
    $result->event_id = $row[4];
    $result->trip_event_id = $row[7];
    $result->type = $row[3];
    $results[] = $result;
}


$json_str = json_encode($results);
date_default_timezone_set("Asia/Shanghai");
//echo date("Y-m-d H:i:s");

$cur_time = date("Y-m-d-H-i-s");
$file_name = $cur_time . '.json';
$file_path = "data/" . $file_name;
$new_file = fopen($file_path, "w") or die("Unable to open file1!");
fwrite($new_file, $json_str);
fclose($new_file);
echo "<div class=\"box\"></div>
    <div class=\"M-box\"></div>";
echo "<script type=text/javascript>play_result('" . "./" . $file_path . "')</script>";

?>
</body>

</html>






