<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chart</title>
    <script type="text/javascript" src="../js/echarts.min.js"></script>
    <script>
        function initSpeedChart(xAxis, yAxis) {
            console.log(xAxis);
            console.log(yAxis);
            var tmp = new Array();
            tmp = yAxis.split(",");
            var arr = new Array();
            for (var i = 0; i < tmp.length; i++) {
                if (i > 0 & i < tmp.length - 1 & parseFloat(tmp[i]) == 0 & parseFloat(tmp[i - 1]) != 0 & parseFloat(tmp[i + 1]) != 0) {
                    arr.push((parseFloat(tmp[i - 1]) + parseFloat(tmp[i + 1])) / 2);
                } else {
                    arr.push((parseFloat(tmp[i])));
                }
            }
            var tmp1 = new Array();
            tmp1 = xAxis.split(",");
            var arr1 = new Array();
            for (var i = 0; i < tmp1.length; i++) {
                arr1.push((tmp1[i]));
            }
            var option = {
                title: {
                    text: 'Speed-Time'
                },
                tooltip: {},
                legend: {
                    data: ['time']
                },
                xAxis: [
                    {
                        data: arr1,
                        name:"time"
                    }
                ],
                yAxis: {
                    name:'speed'
                },
                series: [{
                    name: 'speed',
                    type: 'line',
                    data: arr
                }]
            };
            //初始化echarts实例
            var myChart = echarts.init(document.getElementById('chart_speed'));

            //使用制定的配置项和数据显示图表
            myChart.setOption(option);
        }

        function initAccelChart(xAxis, yAxis) {
            var tmp = new Array();
            tmp = yAxis.split(",");
            var arr = new Array();
            for (var i = 0; i < tmp.length; i++) {
                if (i > 0 & i < tmp.length - 1 & parseFloat(tmp[i]) == 0 & parseFloat(tmp[i - 1]) != 0 & parseFloat(tmp[i + 1]) != 0) {
                    arr.push((parseFloat(tmp[i - 1]) + parseFloat(tmp[i + 1])) / 2);
                } else {
                    arr.push((parseFloat(tmp[i])));
                }

            }
            var tmp1 = new Array();
            tmp1 = xAxis.split(",");
            var arr1 = new Array();
            for (var i = 0; i < tmp1.length; i++) {
                arr1.push((tmp1[i]));
            }

            var option = {
                title: {
                    text: 'Accel-Time'
                },
                tooltip: {},
                legend: {
                    data: ['time']
                },
                xAxis: [
                    {
                        data: arr1,
                        name:"time"
                    }
                ],
                yAxis: {
                    name:'accel'
                },
                series: [{
                    name: 'accel',
                    type: 'line',
                    data: arr
                }]
            };
            //初始化echarts实例
            var myChart = echarts.init(document.getElementById('chart_accel'));

            //使用制定的配置项和数据显示图表
            myChart.setOption(option);
        }
    </script>
</head>
<body>

<div id="chart_speed" style="width:1000px; height: 700px;"></div>
<div id="chart_accel" style="width:1000px; height: 700px;"></div>
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
fclose($file) or die("can not close");
//获取列名的位置
$Time_Stamp = "System.Time_Stamp";
$Accel = "IMU.Accel_X";
$Speed = "FOT_Control.Speed";
$Event_Type = "Event_Type";
$Event_Id = "Event_Id";
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
$yAxis_speed = array();
$yAxis_accel = array();
$index = 0;
foreach ($info_list as $row) {
    if ($row[$index_eventid] == $event_id && $row[$index_type] == $event_type) {
        $xAxis[] = $index;
        $index += 1;
        $yAxis_speed[] = $row[$index_speed];
        $yAxis_accel[] = $row[$index_accel];
    }
}
$y_values_speed = array();
$y_values_accel = array();
foreach ($yAxis_speed as $y) {
    $y_values_speed[] = (float)$y;
}
foreach ($yAxis_accel as $y) {
    $y_values_accel[] = (float)$y;
}
echo "<script type=text/javascript>initSpeedChart('" . implode(",", $xAxis) . "','" . implode(",", $y_values_speed) . "')</script>";
echo "<script type=text/javascript>initAccelChart('" . implode(",", $xAxis) . "','" . implode(",", $y_values_accel) . "')</script>";
?>

</body>
</html>
