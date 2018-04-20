<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/10
 * Time: 13:05
 */

include 'event.php';
include 'event_handler.php';
include 'process_handler.php';
$json_file_names = array();
$new_rows_ss = array();
$new_rows_hb = array();
$new_rows_turn = array();
$event_id = 1;  //事件编号，所有TRIP内的事件都唯一
$tmp_events = array();  //所有事件 存为JSON
$followingEvent = array();//承装完成判定的跟车事件的编号，起始时间，持续时间

function process($driver_id, $trip_id, $types, $csv_file_dir)
{

    $file_name = "";
    $file_names = array();
    $files_folder = array();
    $info_list = array();  //存放所有行车信息

    //driver的单次trip
    if (trim($trip_id) != "") {
        find_trip($driver_id, $trip_id, $types, $csv_file_dir);
    }
    else if ($driver_id != "") {      //driver的所有trip
        find_trips($driver_id, $trip_id, $types, $csv_file_dir);
    } else {
        return null;
    }
    //输出JSON
    global $tmp_events;
    array_pop($tmp_events);
    $json_str = json_encode($tmp_events);
    //输出为CSV文件
    date_default_timezone_set("Asia/Shanghai");
    $cur_time = date("Y-m-d-H-i-s");
    $csv_header = ['time', 'speed', 'accel', 'event_type', 'event_id', 'seq', 's_s_num', 'trip_event_id', 'driver_id', 'trip_id'];
    //$content = $head.$new_rows;
    $header = implode(',', $csv_header) . PHP_EOL;

    global $new_rows_ss;
    global $new_rows_hb;
    global $new_rows_turn;

    if (in_array("start_stop", $types)) {
        $content = '';
        array_pop($new_rows_ss);
        foreach ($new_rows_ss as $line) {
            $content .= implode(',', $line) . PHP_EOL;
        }
        $csv_file_name = $driver_id . "_" . $trip_id . "_" . "start_stop" . $cur_time . ".csv";
        $csv_file_dir = "../public/csv/" . $csv_file_name;
        $output = fopen($csv_file_dir, 'w') or die("can not open csv file");
        $csv = $header . $content;
        fwrite($output, $csv);
        fclose($output) or die("can not close");
    }
    if (in_array("hard_brake", $types)) {
        $content = '';
        foreach ($new_rows_hb as $line) {
            $content .= implode(',', $line) . PHP_EOL;
        }
        $csv_file_name = $driver_id . "_" . $trip_id . "_" . "hard_brake" . $cur_time . ".csv";
        $csv_file_dir = "../public/csv/" . $csv_file_name;
        $output1 = fopen($csv_file_dir, 'w') or die("can not open");
        $csv = $header . $content;
        fwrite($output1, $csv);
        fclose($output1) or die("can not close");
    }
    if (in_array("turn", $types)) {
        $content = '';
        foreach ($new_rows_turn as $line) {
            $content .= implode(',', $line) . PHP_EOL;
        }
        $csv_file_name = $driver_id . "_" . $trip_id . "_" . "turn" . $cur_time . ".csv";
        $csv_file_dir = "../public/csv/" . $csv_file_name;
        $output = fopen($csv_file_dir, 'w') or die("can not open");
        $csv = $header . $content;
        fwrite($output, $csv);
        fclose($output) or die("can not close");
    }
    return $json_str;
}


