<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chart</title>
    <script type="text/javascript" src="../js/echarts.min.js"></script>
    <script>
        function initSpeedChart(xAxis, yAxis) {
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
                        name: "time"
                    }
                ],
                yAxis: {
                    name: 'Speed(m/s)'
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
                    text: 'Accel_X-Time'
                },
                tooltip: {},
                legend: {
                    data: ['time']
                },
                xAxis: [
                    {
                        data: arr1,
                        name: "Time"
                    }
                ],
                yAxis: {
                    name: 'Accel_X(g)'
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

        function initAccelYChart(xAxis, yAxis) {
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
                    text: 'Accel_Y-Time'
                },
                tooltip: {},
                legend: {
                    data: ['time']
                },
                xAxis: [
                    {
                        data: arr1,
                        name: "Time"
                    }
                ],
                yAxis: {
                    name: 'Accel_Y(g)'
                },
                series: [{
                    name: 'accely',
                    type: 'line',
                    data: arr
                }]
            };
            //初始化echarts实例
            var myChart = echarts.init(document.getElementById('chart_accel_y'));
            document.getElementById('chart_accel_y').style.display = "block";
            //使用制定的配置项和数据显示图表
            myChart.setOption(option);
        }

        function initAccelYWithStdChart(xAxis, yAxis, yAxisStd) {
            var tmp = new Array();
            tmp = yAxis.split(",");
            var arr = new Array();
            for (var i = 0; i < tmp.length; i++) {
                if (i > 0 & i < tmp.length - 1 & parseFloat(tmp[i]) == 0 & parseFloat(tmp[i - 1]) != 0 & parseFloat(tmp[i + 1]) != 0) {
                    arr.push(Math.abs(parseFloat(tmp[i - 1]) * 9.81 + parseFloat(tmp[i + 1]) * 9.81) / 2);
                } else {
                    arr.push(Math.abs(parseFloat(tmp[i]) * 9.81));
                }
            }
            var tmp1 = new Array();
            tmp1 = xAxis.split(",");
            var arr1 = new Array();
            for (var i = 0; i < tmp1.length; i++) {
                arr1.push((tmp1[i]));
            }

            var tmp_std = new Array();
            tmp_std = yAxisStd.split(",");
            var arr_std = new Array();
            for (var i = 0; i < tmp_std.length; i++) {
                if (i > 0 & i < tmp_std.length - 1 & parseFloat(tmp_std[i]) == 0 & parseFloat(tmp_std[i - 1]) != 0 & parseFloat(tmp_std[i + 1]) != 0) {
                    arr_std.push((parseFloat(tmp_std[i - 1]) + parseFloat(tmp_std[i + 1])) / 2);
                } else {
                    arr_std.push((parseFloat(tmp_std[i])));
                }
            }
            var option = {
                title: {
                    text: 'Accel_Y-Time'
                },
                tooltip: {},
                legend: {
                    data: ['time']
                },
                xAxis: [
                    {
                        data: arr1,
                        name: "Time"
                    }
                ],
                yAxis: {
                    name: 'Accel_Y(m/s^2)'
                },
                series: [{
                    name: 'accely',
                    type: 'line',
                    data: arr
                },
                    {
                        name: 'accelstd',
                        type: 'line',
                        data: arr_std
                    }
                ]
            };
            //初始化echarts实例
            var myChart = echarts.init(document.getElementById('chart_accel_y_with_std'));
            document.getElementById('chart_accel_y_with_std').style.display = "block";
            //使用制定的配置项和数据显示图表
            myChart.setOption(option);
        }

        function initLaneDisChart(xAxis, yAxis) {
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
                    text: 'Lane_Distance_Product-Time'
                },
                tooltip: {},
                legend: {
                    data: ['time']
                },
                xAxis: [
                    {
                        data: arr1,
                        name: "Time"
                    }
                ],
                yAxis: {
                    name: 'Dis-Product'
                },
                series: [{
                    name: 'Dis-Product',
                    type: 'line',
                    data: arr
                }]
            };
            //初始化echarts实例
            var myChart = echarts.init(document.getElementById('chart_lane_dis'));
            document.getElementById('chart_lane_dis').style.display = "block";
            //使用制定的配置项和数据显示图表
            myChart.setOption(option);
        }

        function initFrontDisChart(xAxis, yAxis) {
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
                    text: 'Distance-Time'
                },
                tooltip: {},
                legend: {
                    data: ['time']
                },
                xAxis: [
                    {
                        data: arr1,
                        name: "Time"
                    }
                ],
                yAxis: {
                    name: 'Front_Distance(m)'
                },
                series: [{
                    name: 'distance',
                    type: 'line',
                    data: arr
                }]
            };
            //初始化echarts实例
            var myChart = echarts.init(document.getElementById('chart_front_distance'));
            document.getElementById('chart_front_distance').style.display = "block";
            //使用制定的配置项和数据显示图表
            myChart.setOption(option);
        }
    </script>
</head>
<body>

<div id="chart_speed" style="width:1000px; height: 700px;"></div>
<div id="chart_accel" style="width:1000px; height: 700px;"></div>
<div id="chart_accel_y" style="width:1000px; height: 700px; display: none;"></div>
<div id="chart_accel_y_with_std" style="width:1000px; height: 700px; display: none;"></div>
<div id="chart_lane_dis" style="width:1000px; height: 700px; display: none;"></div>
<div id="chart_front_distance" style="width:1000px; height: 700px; display: none;"></div>
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
$Accel_Y = "IMU.Accel_Y";
$Speed = "FOT_Control.Speed";
$Event_Type = "Event_Type";
$Event_Id = "Event_Id";
$SMS_X_Range_T0 = "SMS.X_Range_T0";
$Left_Lane_Distance_To_Right_Side = "Road Scout.Left_Lane_Distance_To_Right_Side";
$Right_Lane_Distance_To_Left_Side = "Road Scout.Right_Lane_Distance_To_Left_Side";
$index_left_to_right = 0;
$index_right_to_left = 0;
$index_time = 0;
$index_accel = 0;
$index_accel_y = 0;
$index_speed = 0;
$index_type = 0;
$index_eventid = 0;
$index_front_dis = 0;
$col_index = 0;
foreach ($info_list[0] as $col) {
    switch (trim($col)) {
        case $SMS_X_Range_T0:
            $index_front_dis = $col_index;
            $col_index += 1;
            break;
        case $Accel_Y:
            $index_accel_y = $col_index;
            $col_index += 1;
            break;
        case $Left_Lane_Distance_To_Right_Side:
            $index_left_to_right = $col_index;
            $col_index += 1;
            break;
        case $Right_Lane_Distance_To_Left_Side:
            $index_right_to_left = $col_index;
            $col_index += 1;
            break;
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
$yAxis_accel_y = array();
$yAxis_lane_distance = array();
$yAxis_front_dis = array();
$index = 0;
foreach ($info_list as $row) {
    if ($row[$index_eventid] == $event_id && $row[$index_type] == $event_type) {
        $xAxis[] = $index;
        $index += 1;
        $yAxis_speed[] = $row[$index_speed];
        $yAxis_accel[] = $row[$index_accel];

        if ($event_type == "lane_change") {
            $yAxis_accel_y[] = $row[$index_accel_y];
            $yAxis_lane_distance[] = (float)$row[$index_left_to_right] * (float)$row[$index_right_to_left];
        } else if (startWith("hard_swerve", $event_type)) {
            $yAxis_accel_y[] = $row[$index_accel_y];
        }else if($event_type == "car_following"){
            $yAxis_front_dis [] = $row[$index_front_dis];
        }
    }
}
$y_values_speed_tmp = array();
$y_values_speed = array();
$y_values_accel = array();
$y_values_accel_y = array();
$y_values_lane_distance = array();
$y_values_swerve_std = array();
$y_values_front_dis = array();
foreach ($yAxis_speed as $y) {
    $y_values_speed_tmp[] = (float)$y;
}
for($i = 0 ; $i<count($y_values_speed_tmp);$i++ ){
    if($i=0){
        if($y_values_speed_tmp[i] == 0 & $y_values_speed_tmp[i+1]!=0)
            $y_values_speed[] = $y_values_speed_tmp[i+1];
        else
            $y_values_speed[] = $y_values_speed_tmp[i];
    }else if($i = count($y_values_speed_tmp) -1){
        if($y_values_speed_tmp[i] == 0 & $y_values_speed_tmp[i-1]!=0)
            $y_values_speed[] = $y_values_speed_tmp[i-1];
        else $y_values_speed[] = $y_values_speed_tmp[i];
    }else if($y_values_speed_tmp[i]==0&$y_values_speed_tmp[i-1]!=0& $y_values_speed_tmp[i+1]!=0){
        $y_values_speed[] = ($y_values_speed_tmp[i-1] + $y_values_speed_tmp[i+1])/2;
    }else {
        $y_values_speed[] = $y_values_speed_tmp[i];
    }
}
foreach ($yAxis_accel as $y) {
    $y_values_accel[] = (float)$y;
}
if (!empty($yAxis_accel_y)) {
    foreach ($yAxis_accel_y as $y) {
        $y_values_accel_y[] = (float)$y;
    }
}
if (!empty($yAxis_lane_distance)) {
    foreach ($yAxis_lane_distance as $y) {
        $y_values_lane_distance[] = (float)$y;
    }
}
if(!empty($yAxis_front_dis)){
    foreach ($yAxis_front_dis as $y){
        $y_values_front_dis[] = (float)$y;
    }
}
switch ($event_type) {
    case "hard_swerve_1":
        foreach ($y_values_speed as $speed) {
            if (0 <= $speed * 3.6 & $speed * 3.6 <= 40) {
                $y_values_swerve_std[] = 0.075 * $speed * 3.6 + 1;
            } else if ($speed * 3.6 > 40 & $speed * 3.6 < 80) {
                $y_values_swerve_std[] = 4;
            } else if ($speed * 3.6 > 80 & $speed * 3.6 < 100) {
                $y_values_swerve_std[] = -0.1 * $speed * 3.6 + 12;
            }
        }
        break;
    case "hard_swerve_2":
        foreach ($y_values_speed as $speed) {
            if (0 <= $speed * 3.6 & $speed * 3.6 <= 40) {
                $y_values_swerve_std[] = 0.075 * $speed * 3.6 + 2;
            } else if ($speed * 3.6 > 40 & $speed * 3.6 < 80) {
                $y_values_swerve_std[] = 5;
            } else if ($speed * 3.6 > 80 & $speed * 3.6 < 100) {
                $y_values_swerve_std[] = 0.1 * $speed * 3.6 + 13;
            }
        }
        break;
    case "hard_swerve_3":
        foreach ($y_values_speed as $speed) {
            if (0 <= $speed * 3.6 & $speed * 3.6 <= 40) {
                $y_values_swerve_std[] = 6;
            } else if ($speed * 3.6 > 40 & $speed * 3.6 < 80) {
                $y_values_swerve_std[] = 6;
            } else if ($speed * 3.6 > 80 & $speed * 3.6 < 100) {
                $y_values_swerve_std[] = 6;
            }
        }
        break;
    default:
        break;
}

echo "<script type=text/javascript>initSpeedChart('" . implode(",", $xAxis) . "','" . implode(",", $y_values_speed) . "')</script>";
echo "<script type=text/javascript>initAccelChart('" . implode(",", $xAxis) . "','" . implode(",", $y_values_accel) . "')</script>";
if ($event_type == "lane_change") {
    echo "<script type=text/javascript>initAccelYChart('" . implode(",", $xAxis) . "','" . implode(",", $y_values_accel_y) . "')</script>";
    echo "<script type=text/javascript>initLaneDisChart('" . implode(",", $xAxis) . "','" . implode(",", $y_values_lane_distance) . "')</script>";
}else if (startWith("hard_swerve", $event_type)) {
    echo "<script type=text/javascript>initAccelYWithStdChart('" . implode(",", $xAxis) . "','" . implode(",", $y_values_accel_y) . "','" . implode(",", $y_values_swerve_std) . "')</script>";
}else if($event_type == "car_following"){
    echo "<script type=text/javascript>initFrontDisChart('" . implode(",", $xAxis) . "','" . implode(",", $y_values_front_dis) . "')</script>";
}

function startWith($needle, $name)
{
    return strpos($name, $needle) === 0;
}

?>

</body>
</html>
