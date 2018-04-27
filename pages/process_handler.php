<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/10
 * Time: 13:24
 */

include "carFollowing.php";
include "configure.php";
include "lane_change_detection.php";

$config = new configure();

function find_trip($driver_id, $trip_id, $types, $csv_file_dir)
{
    $config_file = fopen("../Config.json", "r") or die("Unable to open file!");
    $json_str = fread($config_file, filesize("../Config.json"));
    fclose($config_file);
    $config = json_decode($json_str);
    $files_folder = scandir($csv_file_dir);
    $file_name = "";
    echo "<script type=text/javascript>console.log('" . $driver_id . "')</script>";
    echo "<script type=text/javascript>console.log('" . $trip_id . "')</script>";
    foreach ($files_folder as $name) {
        if (startWith("CCHN_" . $driver_id, $name) && endWith('' . $trip_id . ".csv", $name)) {
            echo "<script type=text/javascript>console.log('" . $name . "')</script>";
            $file_name = $name;
        }
    }

    if (substr($csv_file_dir, strlen($csv_file_dir) - 1, 1) == "/") {
        $file_dir = $csv_file_dir . $file_name;
    } else {
        $file_dir = $csv_file_dir . '/' . $file_name;
    }
    process_file($file_dir, $types, $driver_id, $trip_id, $file_name);
}

function find_trips($driver_id, $trip_id, $types, $csv_file_dir)
{
    $config_file = fopen("../Config.json", "r") or die("Unable to open file!");
    $json_str = fread($config_file, filesize("../Config.json"));
    fclose($config_file);
    $config = json_decode($json_str);
    $files_folder = scandir($csv_file_dir);
    foreach ($files_folder as $name) {
        if (startWith("CCHN_" . $driver_id, $name)) {
            echo "<script type=text/javascript>console.log('" . $name . "')</script>";
            $file_names[] = $name;
        }
    }
    if (!empty($file_names)) {
        foreach ($file_names as $file_name) {
            if (substr($csv_file_dir, strlen($csv_file_dir) - 1, 1) == "/") {
                $file_dir = $csv_file_dir . $file_name;
            } else {
                $file_dir = $csv_file_dir . '/' . $file_name;
            }

            process_file($file_dir, $types, $driver_id, $trip_id, $file_name);
        }
    } else return null;
}

$trip_seq = 0; //该driver下的第几个trip  用于lane_change
function process_file($file_dir, $types, $driver_id, $trip_id, $file_name)
{
    if ($trip_id == "") {
        $trip_id = substr($file_dir, sizeof($file_dir) - 10, 5);
    }
    global $new_rows_ss;
    global $new_rows_hb;
    global $new_rows_hs;
    global $new_rows_turn;
    global $new_rows_car_following;
    global $new_rows_lane_change;
    global $event_id;
    global $config;
    global $trip_seq;

    $brake_time = 0;  //急刹车事件发生时间，之后的10秒发生的急刹车算同一次。用于判断。
    $swerve_time = 0; //急转弯事件发生时间，之后的10秒发生的急刹车算同一次。用于判断。
    $last_speed = 0;  //存放上一个时间点的速度
    $s_s_num = 0;       //停启编号
    $sud_brake_count = 0;   //急刹车次数
    $trip_event_id = 1; //每次行程事件编号
    //便遍历每一行数据
    $row_num = 0;   //当前遍历行数
    $new_rows = array();
    $file = fopen($file_dir, "r");
    $info_list = array();  //存放该文件中所有行车信息
    while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容
        $info_list[] = $data;
    }


    //获取列名的位置
    $Time_Stamp = "System.Time_Stamp";
    $Accel = "IMU.Accel_X";
    $Accel_Y = "IMU.Accel_Y";
    $Speed = "FOT_Control.Speed";
    $SMS_Object_ID_T0 = "SMS.Object_ID_T0";
    $SMS_X_Velocity_T0 = "SMS.X_Velocity_T0";
    $SMS_X_Range_T0 = "SMS.X_Range_T0";
    $SMS_Y_Range_T0 = "SMS.Y_Range_T0";
    $index_time = 0;
    $index_accel = 0;
    $index_accel_y = 0;
    $index_speed = 0;
    $index_object = 0;
    $index_x_vel = 0;
    $index_x_range = 0;
    $index_y_range = 0;
    $emer_degree = "";

    $col_index = 0;
    foreach ($info_list[0] as $col) {
        switch (trim($col)) {
            case $Accel_Y:
                $index_accel_y = $col_index;
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
            case $SMS_Object_ID_T0:
                $index_object = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Velocity_T0:
                $index_x_vel = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Range_T0:
                $index_x_range = $col_index;
                $col_index += 1;
                break;
            case $SMS_Y_Range_T0:
                $index_y_range = $col_index;
                $col_index += 1;
                break;
            default:
                $col_index += 1;
                break;
        }
    }

    $is_ini_start = true;
    $ini_start_flag = false;
    global $tmp_events;
    $last_ss_id = 0;
    $ini_speed = -100;
    foreach ($info_list as $row) {
        $new_row = array();
        if ($row_num == 0) {
            $row_num++;
            continue;
        }
        if ($row_num > count($info_list, 0)) {
            break;
        }
        $event_type = -1;//事件类型: 0.急刹车 1.启动 2.停车...

        $tmp_event = new event();

        //判断是否为急刹车事件
        if (in_array("hard_brake", $types)) {
            if ($row[$index_speed] != "" & $row[$index_speed] != " ") {
                if (is_hard_brake((float)$row[$index_speed], (float)$row[$index_time], $brake_time)) {
                    $brake_time = $row[$index_time];
                    $new_row[] = $row[$index_time]; //时间
                    $new_row[] = $row[$index_speed]; //速度
                    $new_row[] = $row[$index_accel]; //加速度
                    $new_row[] = "hard_brake"; //事件类型
                    $event_type = 0;
                    $new_row[] = $event_id; // event_id
                    $new_row[] = ""; //seq 每次事件内部数据点编号
                    $new_row[] = ""; //每对停车事件编号
                    $new_row[] = $trip_event_id;
                    $new_row[] = $driver_id;
                    $new_row[] = $trip_id;
                    $sud_brake_count += 1;

                    $tmp_event->time = $row[$index_time];
                    $tmp_event->driver_id = $driver_id;
                    $tmp_event->trip_id = $trip_id;
                    $tmp_event->type = "hard_brake";
                    $tmp_event->event_id = $event_id;
                    $tmp_event->csv_file_name = $file_name;
                    $tmp_events[] = $tmp_event;

                    $trip_event_id += 1;
                }
            }
        }
        if ($ini_speed == -100) {
            if ($row[$index_speed] != "" & $row[$index_speed] != " " & $row[$index_speed] == 0) {
                $ini_speed = 0;
            }
            if ($row[$index_speed] != 0) {
                $ini_speed = -99;
                $is_ini_start = false;
                $ini_start_flag = true;
            }

        }
        if ($is_ini_start & in_array("ini_start", $types)) {
            if (($ini_speed != -100) & ($ini_speed != -99) & ($row[$index_speed] != "") & ($row[$index_speed] != " ")) {
                if (((float)$row[$index_speed] != 0) & ($last_speed == 0)) {
                    $new_row[] = $row[$index_time]; //时间
                    $new_row[] = $row[$index_speed]; //速度
                    $new_row[] = $row[$index_accel]; //加速度
                    $new_row[] = "ini_start"; //事件类型
                    $event_type = 1;
                    $new_row[] = $last_ss_id; // event_id
                    $new_row[] = 1; //seq 每次事件内部数据点编号
                    $new_row[] = $s_s_num; //每对停车事件编号
                    $new_row[] = $trip_event_id;
                    $new_row[] = $driver_id;
                    $new_row[] = $trip_id;

                    $tmp_event->time = $row[$index_time];
                    $tmp_event->driver_id = $driver_id;
                    $tmp_event->trip_id = $trip_id;
                    $tmp_event->event_id = $last_ss_id;
                    $tmp_event->type = "ini_start";
                    $tmp_event->csv_file_name = $file_name;
                    $tmp_events[] = $tmp_event;
                    $is_ini_start = false;
                    $ini_start_flag = true;
                }
            }
        } else {
            $is_ini_start = false;
        }
        if (in_array("start_stop", $types)) {
            //判断是否为启停事件
            if (($row[$index_speed] != "") & ($row[$index_speed] != " ")) {
                //启动事件
                if (!$is_ini_start & ((float)$row[$index_speed] != 0) & ($last_speed == 0)) {
                    if ($ini_start_flag) {
                        $ini_start_flag = false;
                    } else {
                        if (empty($new_row)) {
                            $new_row[] = $row[$index_time]; //时间
                            $new_row[] = $row[$index_speed]; //速度
                            $new_row[] = $row[$index_accel]; //加速度
                            $new_row[] = "go"; //事件类型
                            $event_type = 2;
//                            $new_row[] = $event_id; // event_id
                            $new_row[] = $last_ss_id;
                            $new_row[] = 1; //seq 每次事件内部数据点编号
                            $new_row[] = $s_s_num; //每对停车事件编号
                            $new_row[] = $trip_event_id;
                            $new_row[] = $driver_id;
                            $new_row[] = $trip_id;

                            $tmp_event->time = $row[$index_time];
                            $tmp_event->driver_id = $driver_id;
                            $tmp_event->trip_id = $trip_id;
                            $tmp_event->event_id = $event_id;
                            $tmp_event->type = "go";
                            $tmp_event->csv_file_name = $file_name;
                            $tmp_events[] = $tmp_event;
                            $s_s_num += 1;
                        } else {
                            $new_row[3] = $new_row[3] . ",go";
                        }
                    }
                }
                //停车事件
                if (((float)$row[$index_speed] == 0) & ($last_speed != 0)) {
                    if (empty($new_row)) {
                        $new_row[] = $row[$index_time]; //时间
                        $new_row[] = $row[$index_speed]; //速度
                        $new_row[] = $row[$index_accel]; //加速度
                        $new_row[] = "stop"; //事件类型
                        $event_type = 3;
                        $new_row[] = $event_id; // event_id
                        $new_row[] = 50; //seq 每次事件内部数据点编号
                        $new_row[] = $s_s_num; //每对停车事件编号
                        $new_row[] = $trip_event_id;
                        $new_row[] = $driver_id;
                        $new_row[] = $trip_id;
                        $last_ss_id = $event_id;

                        $tmp_event->time = $row[$index_time];
                        $tmp_event->driver_id = $driver_id;
                        $tmp_event->trip_id = $trip_id;
                        $tmp_event->type = "stop";
                        $tmp_event->event_id = $event_id;
                        $tmp_event->csv_file_name = $file_name;
                        $tmp_events[] = $tmp_event;
                    } else {
                        $new_row[3] = $new_row[3] . ",stop";
                    }
                }
                $last_speed = (float)$row[$index_speed];
            }
        }
        if (in_array("hard_swerve", $types)) {
            if ($row[$index_speed] != "" & $row[$index_speed] != " " & $row[$index_accel_y] != "" & $row[$index_accel_y] != " ") {
                $emer_degree = is_hard_swerve($row[$index_accel_y], $row[$index_speed], $row[$index_time], $swerve_time);
                if ($emer_degree != "") {
                    $swerve_time = $row[$index_time];
                    $new_row[] = $row[$index_time]; //时间
                    $new_row[] = $row[$index_speed]; //速度
                    $new_row[] = $row[$index_accel]; //加速度
                    $new_row[] = $emer_degree; //事件类型
                    $event_type = 4;
                    $new_row[] = $event_id; // event_id
                    $new_row[] = 1; //seq 每次事件内部数据点编号
                    $new_row[] = ""; //每对停车事件编号
                    $new_row[] = $trip_event_id;
                    $new_row[] = $driver_id;
                    $new_row[] = $trip_id;

                    $tmp_event->time = $row[$index_time];
                    $tmp_event->driver_id = $driver_id;
                    $tmp_event->trip_id = $trip_id;
                    $tmp_event->type = $emer_degree;
                    $tmp_event->event_id = $event_id;
                    $tmp_event->csv_file_name = $file_name;
                    $tmp_events[] = $tmp_event;
                }
            }
        }

        if (in_array("car_following", $types)) {
            carFollowing($row[$index_time], $row[$index_speed], $row[$index_object], $row[$index_x_vel], $row[$index_x_range], $row[$index_y_range]);
        }

        if ($event_type == 0) {
            $i = 2;
            $new_rows_hb[] = $new_row;
            $tmp_begin = $row_num + 1;
            for ($tmp_begin, $i; ($tmp_begin < $row_num + 10 * $config->forward_hard_brake) && ($tmp_begin < count($info_list, 0)); $tmp_begin++, $i++) {
                $new_row_brake = array();
                $new_row_brake[] = $info_list[$tmp_begin][$index_time];//时间
                $new_row_brake[] = $info_list[$tmp_begin][$index_speed];//速度
                $new_row_brake[] = $info_list[$tmp_begin][$index_accel];//加速度
                $new_row_brake[] = "hard_brake"; //事件类型
                $new_row_brake[] = $event_id; //event_id
                $new_row_brake[] = $i;//seq 每次事件内部数据点编号
                $new_row_brake[] = $s_s_num; //每对停车事件编号
                $new_row_brake[] = $trip_event_id;
                $new_row_brake[] = $driver_id;
                $new_row_brake[] = $trip_id;
                $new_rows_hb[] = $new_row_brake;
                unset($new_row_brake);
            }
            $event_id += 1;
            $trip_event_id += 1;
        } else if ($event_type == 1) {
            $i = 2;
            $new_rows_ss[] = $new_row;
            $tmp_begin = $row_num + 1;
            for ($tmp_begin, $i; ($tmp_begin < $row_num + 10 * $config->forward_go) && ($tmp_begin < count($info_list, 0)); $tmp_begin++, $i++) {
                $new_row_start = array();
                $new_row_start[] = $info_list[$tmp_begin][$index_time];//时间
                $new_row_start[] = $info_list[$tmp_begin][$index_speed];//速度
                $new_row_start[] = $info_list[$tmp_begin][$index_accel];//加速度
                $new_row_start[] = "ini_start"; //事件类型
//                $new_row_start[] = $event_id; //event_id
                $new_row_start[] = $event_id; //event_id
                $new_row_start[] = $i;//seq 每次事件内部数据点编号
                $new_row_start[] = $s_s_num; //每对停车事件编号
                $new_row_start[] = $trip_event_id;
                $new_row_start[] = $driver_id;
                $new_row_start[] = $trip_id;
                $new_rows_ss[] = $new_row_start;
                unset($new_row_start);
            }
            $event_id += 1;
            $trip_event_id += 1;
        } else if ($event_type == 2) {
            $i = 2;
            $new_rows_ss[] = $new_row;
            $tmp_begin = $row_num + 1;
            for ($tmp_begin, $i; ($tmp_begin < $row_num + 10 * $config->forward_go) && ($tmp_begin < count($info_list, 0)); $tmp_begin++, $i++) {
                $new_row_start = array();
                $new_row_start[] = $info_list[$tmp_begin][$index_time];//时间
                $new_row_start[] = $info_list[$tmp_begin][$index_speed];//速度
                $new_row_start[] = $info_list[$tmp_begin][$index_accel];//加速度
                $new_row_start[] = "go"; //事件类型
//                $new_row_start[] = $event_id; //event_id
                $new_row_start[] = $last_ss_id; //event_id
                $new_row_start[] = $i;//seq 每次事件内部数据点编号
                $new_row_start[] = $s_s_num; //每对停车事件编号
                $new_row_start[] = $trip_event_id;
                $new_row_start[] = $driver_id;
                $new_row_start[] = $trip_id;
                $new_rows_ss[] = $new_row_start;
                unset($new_row_start);
            }
            $event_id += 1;
            $trip_event_id += 1;
        } else if ($event_type == 3) {
            $i = 1;
            $tmp_begin = 1;
            if ($row_num - 10 * $config->backward_stop >= 0) {
                $tmp_begin = $row_num - 10 * $config->backward_stop;
            }
            for ($tmp_begin, $i; $tmp_begin < $row_num - 1; $tmp_begin++, $i++) {
                $new_row_stop = array();
                $new_row_stop[] = $info_list[$tmp_begin + 1][$index_time];//时间
                $new_row_stop[] = $info_list[$tmp_begin + 1][$index_speed];//速度
                $new_row_stop[] = $info_list[$tmp_begin + 1][$index_accel];//加速度
                $new_row_stop[] = "stop"; //事件类型
                $new_row_stop[] = $event_id; //event_id
                $new_row_stop[] = $i; //seq 每次事件内部数据点编号
                $new_row_stop[] = $s_s_num; //每对停车事件编号
                $new_row_stop[] = $trip_event_id;
                $new_row_stop[] = $driver_id;
                $new_row_stop[] = $trip_id;
                $new_rows_ss[] = $new_row_stop;
                unset($new_row_stop);
            }
            $new_rows_ss[] = $new_row;
            $trip_event_id += 1;
        } else if ($event_type == 4) {
            $i = 2;
            $new_rows_hs[] = $new_row;
            $tmp_begin = $row_num + 1;
            for ($tmp_begin, $i; ($tmp_begin < $row_num + 10 * $config->forward_hard_swerve) && ($tmp_begin < count($info_list, 0)); $tmp_begin++, $i++) {
                $new_row_swerve = array();
                $new_row_swerve[] = $info_list[$tmp_begin][$index_time];//时间
                $new_row_swerve[] = $info_list[$tmp_begin][$index_speed];//速度
                $new_row_swerve[] = $info_list[$tmp_begin][$index_accel];//加速度
                $new_row_swerve[] = $emer_degree; //事件类型
                $new_row_swerve[] = $event_id; //event_id
                $new_row_swerve[] = $i;//seq 每次事件内部数据点编号
                $new_row_swerve[] = ""; //每对停车事件编号
                $new_row_swerve[] = $trip_event_id;
                $new_row_swerve[] = $driver_id;
                $new_row_swerve[] = $trip_id;
                $new_rows_hs[] = $new_row_swerve;
                unset($new_row_swerve);
            }
            $event_id += 1;
            $trip_event_id += 1;
        }

        $row_num += 1;
        array_splice($new_row, 0, count($new_row));
    }
    foreach ($info_list as $row) {
        $new_row = array();
        if ($row_num == 0) {
            $row_num++;
            continue;
        }
        if ($row_num > count($info_list, 0)) {
            break;
        }
        $event_type = -1;//事件类型: 0.急刹车 1.启动 2.停车...

        $tmp_event = new event();

        //判断是否为急刹车事件
        if (in_array("hard_brake", $types)) {
            if ($row[$index_speed] != "" & $row[$index_speed] != " ") {
                if (is_hard_brake((float)$row[$index_speed], (float)$row[$index_time], $brake_time)) {
                    $brake_time = $row[$index_time];
                    $new_row[] = $row[$index_time]; //时间
                    $new_row[] = $row[$index_speed]; //速度
                    $new_row[] = $row[$index_accel]; //加速度
                    $new_row[] = "hard_brake"; //事件类型
                    $event_type = 0;
                    $new_row[] = $event_id; // event_id
                    $new_row[] = ""; //seq 每次事件内部数据点编号
                    $new_row[] = ""; //每对停车事件编号
                    $new_row[] = $trip_event_id;
                    $new_row[] = $driver_id;
                    $new_row[] = $trip_id;
                    $sud_brake_count += 1;

                    $tmp_event->time = $row[$index_time];
                    $tmp_event->driver_id = $driver_id;
                    $tmp_event->trip_id = $trip_id;
                    $tmp_event->type = "hard_brake";
                    $tmp_event->event_id = $event_id;
                    $tmp_event->csv_file_name = $file_name;
                    $tmp_events[] = $tmp_event;

                    $trip_event_id += 1;
                }
            }
        }
        if ($ini_speed == -100) {
            if ($row[$index_speed] != "" & $row[$index_speed] != " " & $row[$index_speed] == 0) {
                $ini_speed = 0;
            }
            if ($row[$index_speed] != 0) {
                echo "<script type=text/javascript>console.log('" . "speed:" . $row[$index_speed] . "')</script>";
                $ini_speed = -99;
                $is_ini_start = false;
                $ini_start_flag = true;
            }

        }
        if ($is_ini_start & in_array("ini_start", $types)) {
            if (($ini_speed != -100) & ($ini_speed != -99) & ($row[$index_speed] != "") & ($row[$index_speed] != " ")) {
                echo "<script type=text/javascript>console.log('" . "ini_speed:" . $ini_speed . "')</script>";
                if (((float)$row[$index_speed] != 0) & ($last_speed == 0)) {
                    $new_row[] = $row[$index_time]; //时间
                    $new_row[] = $row[$index_speed]; //速度
                    $new_row[] = $row[$index_accel]; //加速度
                    $new_row[] = "ini_start"; //事件类型
                    $event_type = 1;
                    $new_row[] = $last_ss_id; // event_id
                    $new_row[] = 1; //seq 每次事件内部数据点编号
                    $new_row[] = $s_s_num; //每对停车事件编号
                    $new_row[] = $trip_event_id;
                    $new_row[] = $driver_id;
                    $new_row[] = $trip_id;

                    $tmp_event->time = $row[$index_time];
                    $tmp_event->driver_id = $driver_id;
                    $tmp_event->trip_id = $trip_id;
                    $tmp_event->event_id = $last_ss_id;
                    $tmp_event->type = "ini_start";
                    $tmp_event->csv_file_name = $file_name;
                    $tmp_events[] = $tmp_event;
                    $is_ini_start = false;
                    $ini_start_flag = true;
                }
            }
        } else {
            $is_ini_start = false;
        }
        if (in_array("start_stop", $types)) {
            //判断是否为启停事件
            if (($row[$index_speed] != "") & ($row[$index_speed] != " ")) {
                //启动事件
                if (!$is_ini_start & ((float)$row[$index_speed] != 0) & ($last_speed == 0)) {
                    if ($ini_start_flag) {
                        $ini_start_flag = false;
                    } else {
                        if (empty($new_row)) {
                            $new_row[] = $row[$index_time]; //时间
                            $new_row[] = $row[$index_speed]; //速度
                            $new_row[] = $row[$index_accel]; //加速度
                            $new_row[] = "go"; //事件类型
                            $event_type = 2;
//                            $new_row[] = $event_id; // event_id
                            $new_row[] = $last_ss_id;
                            $new_row[] = 1; //seq 每次事件内部数据点编号
                            $new_row[] = $s_s_num; //每对停车事件编号
                            $new_row[] = $trip_event_id;
                            $new_row[] = $driver_id;
                            $new_row[] = $trip_id;

                            $tmp_event->time = $row[$index_time];
                            $tmp_event->driver_id = $driver_id;
                            $tmp_event->trip_id = $trip_id;
                            $tmp_event->event_id = $event_id;
                            $tmp_event->type = "go";
                            $tmp_event->csv_file_name = $file_name;
                            $tmp_events[] = $tmp_event;
                            $s_s_num += 1;
                        } else {
                            $new_row[3] = $new_row[3] . ",go";
                        }
                    }
                }
                //停车事件
                if (((float)$row[$index_speed] == 0) & ($last_speed != 0)) {
                    if (empty($new_row)) {
                        $new_row[] = $row[$index_time]; //时间
                        $new_row[] = $row[$index_speed]; //速度
                        $new_row[] = $row[$index_accel]; //加速度
                        $new_row[] = "stop"; //事件类型
                        $event_type = 3;
                        $new_row[] = $event_id; // event_id
                        $new_row[] = 50; //seq 每次事件内部数据点编号
                        $new_row[] = $s_s_num; //每对停车事件编号
                        $new_row[] = $trip_event_id;
                        $new_row[] = $driver_id;
                        $new_row[] = $trip_id;
                        $last_ss_id = $event_id;

                        $tmp_event->time = $row[$index_time];
                        $tmp_event->driver_id = $driver_id;
                        $tmp_event->trip_id = $trip_id;
                        $tmp_event->type = "stop";
                        $tmp_event->event_id = $event_id;
                        $tmp_event->csv_file_name = $file_name;
                        $tmp_events[] = $tmp_event;
                    } else {
                        $new_row[3] = $new_row[3] . ",stop";
                    }
                }
                $last_speed = (float)$row[$index_speed];
            }
        }
        if (in_array("hard_swerve", $types)) {
            if ($row[$index_speed] != "" & $row[$index_speed] != " " & $row[$index_accel_y] != "" & $row[$index_accel_y] != " ") {
                $emer_degree = is_hard_swerve($row[$index_accel_y], $row[$index_speed], $row[$index_time], $swerve_time);
                if ($emer_degree != "") {
                    $swerve_time = $row[$index_time];
                    $new_row[] = $row[$index_time]; //时间
                    $new_row[] = $row[$index_speed]; //速度
                    $new_row[] = $row[$index_accel]; //加速度
                    $new_row[] = $emer_degree; //事件类型
                    $event_type = 4;
                    $new_row[] = $event_id; // event_id
                    $new_row[] = 1; //seq 每次事件内部数据点编号
                    $new_row[] = ""; //每对停车事件编号
                    $new_row[] = $trip_event_id;
                    $new_row[] = $driver_id;
                    $new_row[] = $trip_id;

                    $tmp_event->time = $row[$index_time];
                    $tmp_event->driver_id = $driver_id;
                    $tmp_event->trip_id = $trip_id;
                    $tmp_event->type = $emer_degree;
                    $tmp_event->event_id = $event_id;
                    $tmp_event->csv_file_name = $file_name;
                    $tmp_events[] = $tmp_event;
                }
            }
        }

        if (in_array("car_following", $types)) {
            carFollowing($row[$index_time], $row[$index_speed], $row[$index_object], $row[$index_x_vel], $row[$index_x_range], $row[$index_y_range]);
        }

        if ($event_type == 0) {
            $i = 2;
            $new_rows_hb[] = $new_row;
            $tmp_begin = $row_num + 1;
            for ($tmp_begin, $i; ($tmp_begin < $row_num + 10 * $config->forward_hard_brake) && ($tmp_begin < count($info_list, 0)); $tmp_begin++, $i++) {
                $new_row_brake = array();
                $new_row_brake[] = $info_list[$tmp_begin][$index_time];//时间
                $new_row_brake[] = $info_list[$tmp_begin][$index_speed];//速度
                $new_row_brake[] = $info_list[$tmp_begin][$index_accel];//加速度
                $new_row_brake[] = "hard_brake"; //事件类型
                $new_row_brake[] = $event_id; //event_id
                $new_row_brake[] = $i;//seq 每次事件内部数据点编号
                $new_row_brake[] = $s_s_num; //每对停车事件编号
                $new_row_brake[] = $trip_event_id;
                $new_row_brake[] = $driver_id;
                $new_row_brake[] = $trip_id;
                $new_rows_hb[] = $new_row_brake;
                unset($new_row_brake);
            }
            $event_id += 1;
            $trip_event_id += 1;
        } else if ($event_type == 1) {
            $i = 2;
            $new_rows_ss[] = $new_row;
            $tmp_begin = $row_num + 1;
            for ($tmp_begin, $i; ($tmp_begin < $row_num + 10 * $config->forward_go) && ($tmp_begin < count($info_list, 0)); $tmp_begin++, $i++) {
                $new_row_start = array();
                $new_row_start[] = $info_list[$tmp_begin][$index_time];//时间
                $new_row_start[] = $info_list[$tmp_begin][$index_speed];//速度
                $new_row_start[] = $info_list[$tmp_begin][$index_accel];//加速度
                $new_row_start[] = "ini_start"; //事件类型
//                $new_row_start[] = $event_id; //event_id
                $new_row_start[] = $event_id; //event_id
                $new_row_start[] = $i;//seq 每次事件内部数据点编号
                $new_row_start[] = $s_s_num; //每对停车事件编号
                $new_row_start[] = $trip_event_id;
                $new_row_start[] = $driver_id;
                $new_row_start[] = $trip_id;
                $new_rows_ss[] = $new_row_start;
                unset($new_row_start);
            }
            $event_id += 1;
            $trip_event_id += 1;
        } else if ($event_type == 2) {
            $i = 2;
            $new_rows_ss[] = $new_row;
            $tmp_begin = $row_num + 1;
            for ($tmp_begin, $i; ($tmp_begin < $row_num + 10 * $config->forward_go) && ($tmp_begin < count($info_list, 0)); $tmp_begin++, $i++) {
                $new_row_start = array();
                $new_row_start[] = $info_list[$tmp_begin][$index_time];//时间
                $new_row_start[] = $info_list[$tmp_begin][$index_speed];//速度
                $new_row_start[] = $info_list[$tmp_begin][$index_accel];//加速度
                $new_row_start[] = "go"; //事件类型
//                $new_row_start[] = $event_id; //event_id
                $new_row_start[] = $last_ss_id; //event_id
                $new_row_start[] = $i;//seq 每次事件内部数据点编号
                $new_row_start[] = $s_s_num; //每对停车事件编号
                $new_row_start[] = $trip_event_id;
                $new_row_start[] = $driver_id;
                $new_row_start[] = $trip_id;
                $new_rows_ss[] = $new_row_start;
                unset($new_row_start);
            }
            $event_id += 1;
            $trip_event_id += 1;
        } else if ($event_type == 3) {
            $i = 1;
            $tmp_begin = 1;
            if ($row_num - 10 * $config->backward_stop >= 0) {
                $tmp_begin = $row_num - 10 * $config->backward_stop;
            }
            for ($tmp_begin, $i; $tmp_begin < $row_num - 1; $tmp_begin++, $i++) {
                $new_row_stop = array();
                $new_row_stop[] = $info_list[$tmp_begin + 1][$index_time];//时间
                $new_row_stop[] = $info_list[$tmp_begin + 1][$index_speed];//速度
                $new_row_stop[] = $info_list[$tmp_begin + 1][$index_accel];//加速度
                $new_row_stop[] = "stop"; //事件类型
                $new_row_stop[] = $event_id; //event_id
                $new_row_stop[] = $i; //seq 每次事件内部数据点编号
                $new_row_stop[] = $s_s_num; //每对停车事件编号
                $new_row_stop[] = $trip_event_id;
                $new_row_stop[] = $driver_id;
                $new_row_stop[] = $trip_id;
                $new_rows_ss[] = $new_row_stop;
                unset($new_row_stop);
            }
            $new_rows_ss[] = $new_row;
            $trip_event_id += 1;
        } else if ($event_type == 4) {
            $i = 2;
            $new_rows_hs[] = $new_row;
            $tmp_begin = $row_num + 1;
            for ($tmp_begin, $i; ($tmp_begin < $row_num + 10 * $config->forward_hard_swerve) && ($tmp_begin < count($info_list, 0)); $tmp_begin++, $i++) {
                $new_row_swerve = array();
                $new_row_swerve[] = $info_list[$tmp_begin][$index_time];//时间
                $new_row_swerve[] = $info_list[$tmp_begin][$index_speed];//速度
                $new_row_swerve[] = $info_list[$tmp_begin][$index_accel];//加速度
                $new_row_swerve[] = $emer_degree; //事件类型
                $new_row_swerve[] = $event_id; //event_id
                $new_row_swerve[] = $i;//seq 每次事件内部数据点编号
                $new_row_swerve[] = ""; //每对停车事件编号
                $new_row_swerve[] = $trip_event_id;
                $new_row_swerve[] = $driver_id;
                $new_row_swerve[] = $trip_id;
                $new_rows_hs[] = $new_row_swerve;
                unset($new_row_swerve);
            }
            $event_id += 1;
            $trip_event_id += 1;
        }

        $row_num += 1;
        array_splice($new_row, 0, count($new_row));
    }

    if (count($tmp_events) != 0 && $tmp_events[count($tmp_events) - 1]->type == "stop") {
        if (in_array("final_stop", $types)) {
            $tmp_events[count($tmp_events) - 1]->type = "final_stop";
        } else {
            array_splice($tmp_events, count($tmp_events) - 1, 1);
            $event_id -= 1;
        }
    }

    global $followingEvent;
    foreach ($followingEvent as $follow) {
        $tmp_event_follow = new event();
        $tmp_event_follow->driver_id = $driver_id;
        $tmp_event_follow->trip_id = $trip_id;
        $tmp_event_follow->event_id = $event_id;
        $tmp_event_follow->type = "car_following";
        $tmp_event_follow->time = $follow[1];
        $tmp_event_follow->duration = $follow[2];
        $tmp_event_follow->csv_file_name = $file_name;
        $tmp_events[] = $tmp_event_follow;

        $tmp_begin = $follow[1];
        $duration = $follow[2];
        for ($i = 0; $i < $duration; $i++) {
            $new_row_following = array();
            $new_row_following[] = $info_list[$tmp_begin + $i][$index_time];//时间
            $new_row_following[] = $info_list[$tmp_begin + $i][$index_speed];//速度
            $new_row_following[] = $info_list[$tmp_begin + $i][$index_accel];//加速度
            $new_row_following[] = "car_following"; //事件类型
            $new_row_following[] = $event_id; //event_id
            $new_row_following[] = $i + 1;//seq 每次事件内部数据点编号
            $new_row_following[] = ""; //每对停车事件编号
            $new_row_following[] = $trip_event_id;
            $new_row_following[] = $driver_id;
            $new_row_following[] = $trip_id;
            $new_rows_car_following[] = $new_row_following;
            unset($new_row_following);
        }
        $trip_event_id += 1;
        $event_id += 1;
    }

    array_splice($followingEvent, 0, count($followingEvent));

    if (in_array("lane_change", $types)) {
        global $laneChangeEvent;
        global $laneChangeEvents;
        $lane_change_file_name = '../lane_change/'.'lane_change_list_'.$driver_id.'_'.$trip_id;
        if(file_exists($lane_change_file_name)){
            $lane_change_list_file = fopen($lane_change_file_name, "r") or die("Unable to open file!");
            $laneChangeEvents = fread($lane_change_file_name, filesize($lane_change_file_name));
            fclose($lane_change_list_file);
            $tmp_arr = explode('&', $laneChangeEvents);
            $laneChangeEvent = $tmp_arr[$trip_seq];
            $trip_seq +=1;
        }else{
            $video_file_path = '../video/' . substr($file_name, 0, strlen($file_name) - 4) . '_Front.mp4';
            lane_change_detection($video_file_path, $file_dir, 1);
            if($laneChangeEvents == ""){
                $laneChangeEvents = $laneChangeEvent;
            }else{
                $laneChangeEvents = $laneChangeEvents."&".$laneChangeEvent;
            }
        }
        $lane_change_str = str_replace("]","",trim($laneChangeEvent, '[ ]'));
        $lane_change_str = str_replace("[", "", $lane_change_str);
        $lane_change_arr = explode(',', $lane_change_str);
        foreach ($lane_change_arr as $time) {
            $tmp_event_lane_change = new event();
            $tmp_event_lane_change->driver_id = $driver_id;
            $tmp_event_lane_change->trip_id = $trip_id;
            $tmp_event_lane_change->event_id = $event_id;
            $tmp_event_lane_change->type = "lane_change";
            $tmp_event_lane_change->time =(int)trim($time);
            $tmp_event_lane_change->duration = 0;
            $tmp_event_lane_change->csv_file_name = $file_name;
            $tmp_events[] = $tmp_event_lane_change;
            $event_id += 1;
            $trip_event_id += 1;
        }
        $laneChangeEvent = "";
    }
    fclose($file);
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