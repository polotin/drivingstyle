<?php
/**
 * Created by PhpStorm.
 * User: Polotin
 * Date: 2018/3/22
 * Time: 15:38
 */
header("Content-type: text/html; charset=utf-8");
$driver_id = $_POST["driver_id"];
$trip_id = $_POST["trip_id"];
$types = $_POST["types"];

$file_name = $driver_id . "_" . $trip_id . ".csv";
$file = fopen($file_name, "r");

$info_list = array();  //存放所有行车信息
$brake_time = 0;  //急刹车事件发生时间，之后的10秒发生的急刹车算同一次。用于判断。
$last_speed = 0;  //存放上一个时间点的速度
$s_s_num = 0;       //停启编号
$sud_brake_count = 0;   //急刹车次数

while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容
    $info_list[] = $data;
}

//便遍历每一行数据
$row_num = 0;   //当前遍历行数
$new_row = array();
$new_rows = array();
$event_type = -1; //事件类型: 0.急刹车 1.启动 2.停车...

$event_id = 1;  //事件编号，所有TRIP内的事件都唯一
$trip_event_id = 1; //每次行程事件编号

foreach ($info_list as $row) {
    if ($row_num == 0) {
        $row_num++;
        continue;
    }
    if ($row_num > count($info_list, 0)) {
        break;
    }
    $event_type = -1;

    //判断是否为急刹车事件
    if (in_array("hard_brake", $types)) {
        if ($row[9] != "" & $row[9] != " ") {
            if (((float)$row[9] < -0.108505) & ((float)$row[0] - $brake_time > 100)) {
                $brake_time = $row[0];
                $new_row[] = $row[0]; //时间
                $new_row[] = $row[2]; //速度
                $new_row[] = $row[9]; //加速度
                $new_row[] = "sud_brake"; //事件类型
                $event_type = 0;
                $new_row[] = $event_id; // event_id
                $new_row[] = ""; //seq 每次事件内部数据点编号
                $new_row[] = ""; //每对停车事件编号
                $new_row[] = $trip_event_id;
                $new_row[] = $driver_id;
                $new_row[] = $trip_id;
                $sud_brake_count += 1;
            }
        }

    }

    //判断是否为启停事件
    if (in_array("start_stop", $types)) {
        //判断是否为启停事件
        if (($row[2] != "") & ($row[2] != " ")) {
            //启动事件
            if (((float)$row[2] != 0) & ($last_speed == 0)) {
                $s_s_num += 1;
                if (empty($new_row)) {
                    $new_row[] = $row[0]; //时间
                    $new_row[] = $row[2]; //速度
                    $new_row[] = $row[9]; //加速度
                    $new_row[] = "start"; //事件类型
                    $event_type = 1;
                    $new_row[] = $event_id; // event_id
                    $new_row[] = 1; //seq 每次事件内部数据点编号
                    $new_row[] = $s_s_num; //每对停车事件编号
                    $new_row[] = $trip_event_id;
                    $new_row[] = $driver_id;
                    $new_row[] = $trip_id;
                } else {
                    $new_row[3] = $new_row[3] . ",start";
                }

            }
            //停车事件
            if (((float)$row[2] == 0) & ($last_speed != 0)) {
                if (empty($new_row)) {
                    $new_row[] = $row[0]; //时间
                    $new_row[] = $row[2]; //速度
                    $new_row[] = $row[9]; //加速度
                    $new_row[] = "stop"; //事件类型
                    $event_type = 2;
                    $new_row[] = $event_id; // event_id
                    $new_row[] = 50; //seq 每次事件内部数据点编号
                    $new_row[] = $s_s_num; //每对停车事件编号
                    $new_row[] = $trip_event_id;
                    $new_row[] = $driver_id;
                    $new_row[] = $trip_id;
                } else {
                    $new_row[3] = $new_row[3] . ",stop";
                }
            }
            $last_speed = (float)$row[2];
        }
    }

    //判断是否为转弯事件
    if (in_array("turn", $types)) {

    }
    //判断是否为高速行驶
    if (in_array("high_speed", $types)) {

    }
    //判断是否为初次启动
    if (in_array("ini_start", $types)) {

    }
    //判断是否为最后停车
    if (in_array("final_stop", $types)) {

    }

    if ($event_type == 1) {
        $i = 2;
        $new_rows[] = $new_row;
        $tmp_begin = $row_num + 1;
        for ($tmp_begin, $i; ($tmp_begin < $row_num + 50) && ($tmp_begin < count($info_list, 0)); $tmp_begin++, $i++) {
            $new_row_start = array();
            $new_row_start[] = $info_list[$tmp_begin][0];//时间
            $new_row_start[] = $info_list[$tmp_begin][2];//速度
            $new_row_start[] = $info_list[$tmp_begin][9];//加速度
            $new_row_start[] = "start"; //事件类型
            $new_row_start[] = $event_id; //event_id
            $new_row_start[] = $i;//seq 每次事件内部数据点编号
            $new_row_start[] = $s_s_num; //每对停车事件编号
            $new_row_start[] = $trip_event_id;
            $new_row_start[] = $driver_id;
            $new_row_start[] = $trip_id;
            $new_rows[] = $new_row_start;
            unset($new_row_start);
        }
        $event_id += 1;
        $trip_event_id += 1;
    } else if ($event_type == 2) {
        $i = 1;
        $tmp_begin = 1;
        if ($row_num - 50 >= 0) {
            $tmp_begin = $row_num - 50;
        }
        for ($tmp_begin, $i; $tmp_begin < $row_num - 1; $tmp_begin++, $i++) {
            $new_row_stop = array();
            $new_row_stop[] = $info_list[$tmp_begin + 1][0];//时间
            $new_row_stop[] = $info_list[$tmp_begin + 1][2];//速度
            $new_row_stop[] = $info_list[$tmp_begin + 1][9];//加速度
            $new_row_stop[] = "stop"; //事件类型
            $new_row_stop[] = $event_id; //event_id
            $new_row_stop[] = $i; //seq 每次事件内部数据点编号
            $new_row_stop[] = $s_s_num; //每对停车事件编号
            $new_row_stop[] = $trip_event_id;
            $new_row_stop[] = $driver_id;
            $new_row_stop[] = $trip_id;
            $new_rows[] = $new_row_stop;
            unset($new_row_stop);
        }
        $new_rows[] = $new_row;
        $event_id += 1;
        $trip_event_id += 1;
    } else if ($event_type == 0) {
        $new_rows[] = $new_row;
        $event_id += 1;
        $trip_event_id += 1;
    }


    $row_num += 1;
    array_splice($new_row, 0, count($new_row));
}
fclose($file);
$output = fopen('2_1_new.csv', 'w') or die("can not open");
$csv_header = ['time', 'speed', 'accel', 'event_type', 'event_id', 'seq', 's_s_num', 'trip_event_id', 'driver_id', 'trip_id'];
//$content = $head.$new_rows;
$header = implode(',', $csv_header) . PHP_EOL;
$content = '';
foreach ($new_rows as $line) {
    $content .= implode(',', $line) . PHP_EOL;
}
$csv = $header . $content;
fwrite($output, $csv);
fclose($output) or die("can not close");

header('Location:show_events.php?test=' . "x");
die;
