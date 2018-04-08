<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>首页</title>
    <link rel="stylesheet" href="js/bootstrap/css/bootstrap.min.css">
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/bootstrap/js/bootstrap.min.js"></script>
    <script src="js/pagination.js"></script>
    <script src="js/analyse.js"></script>
</head>

<body>
<?php
include 'process.php';
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="my_form">
    driver_id:
    <input type="text" name="driver_id">&nbsp;
    trip_id(optional):
    <input type="text" name="trip_id"><br/><br/>
    event_types: &nbsp;
    <input type="checkbox" name="types[]" value="start_stop">start_stop
    <input type="checkbox" name="types[]" value="hard_brake">hard_brake
    <input type="checkbox" name="types[]" value="trun">trun
    <input type="checkbox" name="types[]" value="high_speed">high_speed
    <input type="checkbox" name="types[]" value="ini_start">ini_start
    <input type="checkbox" name="types[]" value="final_stop">final_stop
    <br/><br/>
    config: <br/>
    threshold(hard_brake): <input type="text" name="threshold"><br/>
    csv_file_folder: <input type="text" name="csv_file_dir"><br/>
    video_file_folder: <input type="text" name="video_file_dir"><br/>

    output_file_folder: <input type="text" name="output_dir"><br/>

    video_play_pre: <input type="text" name="previous"><br/>
    video_play_fol: <input type="text" name="following"><br/>
    <input type="submit" name="fenxi" value="filter">
</form>


<?php
//处理
$driver_id = "";
$trip_id = "";
$types = "";
$threshold = 0;
$csv_file_dir = "";
$video_file_dir = "";
$output_dir = "";
$video_play_pre = 0;
$video_play_fol = 0;
if (isset($_POST["driver_id"])) {
    $driver_id = $_POST["driver_id"];
}
if (isset($_POST["trip_id"])) {
    $trip_id = $_POST["trip_id"];
}
if (isset($_POST["types"])) {
    $types = $_POST["types"];
}
if (isset($_POST["threshold"])) {
    $threshold = $_POST["threshold"];
}
if (isset($_POST["csv_file_dir"])) {
    $csv_file_dir = $_POST["csv_file_dir"];
}
if (isset($_POST["video_file_dir"])) {
    $video_file_dir = $_POST["video_file_dir"];
}
if (isset($_POST["output_dir"])) {
    $output_dir = $_POST["output_dir"];
}
if (isset($_POST["video_play_pre"])) {
    $video_play_pre = $_POST["video_play_pre"];
}
if (isset($_POST["video_play_fol"])) {
    $video_play_fol = $_POST["video_play_fol"];
}

$json_file_dir = '';
if (isset($_POST["fenxi"])) {
    if ($_POST["fenxi"] == "filter") {
        $json_file_dir = process($driver_id, $trip_id, $types, $threshold, $csv_file_dir, $video_file_dir, $output_dir, $video_play_pre, $video_play_fol);
    }
}

echo "<div class=\"box\"></div>
    <div class=\"M-box\"></div>";
echo "<script type=text/javascript>play_result('" . "./" . $json_file_dir . "')</script>";
?>
</body>

</html>