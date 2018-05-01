<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chart</title>
    <script type="text/javascript" src="../js/echarts.min.js"></script>
    <script>
        function initChart(xAxis, yAxis) {
            console.log((yAxis.split(",")).length);
            var tmp = new Array();
            tmp = yAxis.split(",");
            var arr = new Array();
            for(var i = 0; i<tmp.length;i++){
                arr.push((parseFloat(tmp[i])));
            }
            console.log(arr);

            var tmp1 = new Array();
            tmp1 = xAxis.split(",");
            var arr1 = new Array();
            for(var i = 0; i<tmp1.length;i++){
                arr1.push((tmp1[i]));
            }

            var option = {
                title: {
                    text: 'Speed'
                },
                tooltip: {},
                legend: {
                    data: ['time']
                },
                xAxis: {
                    // data:["Android","IOS","PC","Ohter"]
                    data: arr1
                },
                yAxis: {

                },
                series: [{
                    name: 'speed',
                    type: 'line',
                    // data:[500,200,360,100]
                    // data: yAxis
                    data:arr
                }]
            };
            //初始化echarts实例
            var myChart = echarts.init(document.getElementById('chartmain'));

            //使用制定的配置项和数据显示图表
            myChart.setOption(option);
        }

    </script>
</head>
<body>

<div id="chartmain" style="width:1000px; height: 700px;"></div>
<?php
include "event_chart.php";
$file_dir;
$driver_id;
$trip_id;
$event_id;
$event_type;
if (isset($_GET["file_dir"])) {
    $file_dir = $_GET["file_dir"];
}
if (isset($_GET["driver_id"])) {
    $driver_id = $_GET["driver_id"];
}
if (isset($_GET["trip_id"])) {
    $trip_id = $_GET["trip_id"];
}
if (isset($_GET["event_id"])) {
    $event_id = $_GET["event_id"];
}
if (isset($_GET["event_type"])) {
    $event_type = $_GET["event_type"];
}

$rows = array();
$file = fopen($file_dir, "r") or die("can not open");
$info_list = array();  //存放该文件中所有行车信息
while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容
    $info_list[] = $data;
}
//获取列名的位置
$Time_Stamp = "time";
$Accel = "accel";
$Speed = "speed";
$Event_Type = "event_type";
$Event_Id = "event_id";
$index_time = 0;
$index_accel = 0;
$index_speed = 0;
$index_type = 0;
$index_eventid = 0;
$col_index = 0;
foreach ($info_list[0] as $col) {
    switch (trim($col)) {
        case $Time_Stamp:
            $index_time = $col_index;
            $col_index += 1;
            break;
        case $Accel:
            $index_accel = $col_index;
            $col_index += 1;
            break;
        case $Speed:
            $index_speed = $col_index;
            $col_index += 1;
            break;
        case $Event_Type:
            $index_type = $col_index;
            $col_index += 1;
            break;
        case $Event_Id:
            $index_eventid = $col_index;
            $col_index += 1;
            break;
        default:
            $col_index += 1;
            break;
    }
}

$xAxis = array();
$yAxis = array();
$index = 0;
foreach ($info_list as $row) {
    if ($row[$index_eventid] == $event_id && $row[$index_type] == $event_type) {
        $chart = new event_chart();
        $chart->event_id = $row[$index_eventid];
        $chart->trip_id = $trip_id;
        $chart->speed = $row[$index_speed];
        $chart->driver_id = $driver_id;
        $chart->time = $row[$index_time];
        $rows[] = $chart;

//        $xAxis[] = floatval($row[$index_time]);
        $xAxis[] = $index;
        $index+=1;
        $yAxis[] = $row[$index_speed];
    }
}
$y_values =array();
foreach ($yAxis as $y){
    $y_values[] = (float)$y;
}
echo "<script type=text/javascript>initChart('" . implode(",", $xAxis) . "','" . implode(",",$y_values) . "')</script>";
?>

</body>
</html>
