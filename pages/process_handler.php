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
include "getColumnName.php";
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
    $emer_degree = "";
    //便遍历每一行数据
    $row_num = 0;   //当前遍历行数
    $new_rows = array();
    $file = fopen($file_dir, "r");
    $info_list = array();  //存放该文件中所有行车信息
    while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容
        $info_list[] = $data;
    }
    //获取列名的位置
    $Time_Stamp = "System.Time_Stamp";$Accel = "IMU.Accel_X";$Accel_Y = "IMU.Accel_Y";$Accel_Z = "IMU.Accel_Z";$Speed = "FOT_Control.Speed";$Latitude = "Head_Unit.Latitude";$Longitude = "Head_Unit.Longitude";$BrakePedalPosition = "VehicleCAN_1.BrakePedalPosition";$VehicleCanSpeed = "VehicleCAN_1.Speed";$EngineSpeed = "VehicleNetworkBox.EngineSpeed";$SMS_Object_ID_T0 = "SMS.Object_ID_T0";$SMS_Object_ID_T1 = "SMS.Object_ID_T1";$SMS_Object_ID_T2 = "SMS.Object_ID_T2";$SMS_Object_ID_T3 = "SMS.Object_ID_T3";$SMS_Object_ID_T4 = "SMS.Object_ID_T4";$SMS_Object_ID_T5 = "SMS.Object_ID_T5";$SMS_Object_ID_T6 = "SMS.Object_ID_T6";$SMS_Object_ID_T7 = "SMS.Object_ID_T7";$SMS_X_Range_T0 = "SMS.X_Range_T0";$SMS_X_Range_T1 = "SMS.X_Range_T1";$SMS_X_Range_T2 = "SMS.X_Range_T2";$SMS_X_Range_T3 = "SMS.X_Range_T3";$SMS_X_Range_T4 = "SMS.X_Range_T4";$SMS_X_Range_T5 = "SMS.X_Range_T5";$SMS_X_Range_T6 = "SMS.X_Range_T6";$SMS_X_Range_T7 = "SMS.X_Range_T7";$SMS_X_Velocity_T0 = "SMS.X_Velocity_T0";$SMS_X_Velocity_T1 = "SMS.X_Velocity_T1";$SMS_X_Velocity_T2 = "SMS.X_Velocity_T2";$SMS_X_Velocity_T3 = "SMS.X_Velocity_T3";$SMS_X_Velocity_T4 = "SMS.X_Velocity_T4";$SMS_X_Velocity_T5 = "SMS.X_Velocity_T5";$SMS_X_Velocity_T6 = "SMS.X_Velocity_T6";$SMS_X_Velocity_T7 = "SMS.X_Velocity_T7";$SMS_Y_Range_T0 = "SMS.Y_Range_T0";$SMS_Y_Range_T1 = "SMS.Y_Range_T1";$SMS_Y_Range_T2 = "SMS.Y_Range_T2";$SMS_Y_Range_T3 = "SMS.Y_Range_T3";$SMS_Y_Range_T4 = "SMS.Y_Range_T4";$SMS_Y_Range_T5 = "SMS.Y_Range_T5";$SMS_Y_Range_T6 = "SMS.Y_Range_T6";$SMS_Y_Range_T7 = "SMS.Y_Range_T7";$SMS_Y_Velocity_T0 = "SMS.Y_Velocity_T0";$SMS_Y_Velocity_T1 = "SMS.Y_Velocity_T1";$SMS_Y_Velocity_T2 = "SMS.Y_Velocity_T2";$SMS_Y_Velocity_T3 = "SMS.Y_Velocity_T3";$SMS_Y_Velocity_T4 = "SMS.Y_Velocity_T4";$SMS_Y_Velocity_T5 = "SMS.Y_Velocity_T5";$SMS_Y_Velocity_T6 = "SMS.Y_Velocity_T6";$SMS_Y_Velocity_T7 = "SMS.Y_Velocity_T7";
    $index_time = 0;$index_accel = 0;$index_accel_y = 0;$index_accel_z = 0;$index_speed = 0;$index_lati = 0;$index_lonti = 0;$index_pedal = 0;$index_can_speed = 0;$index_engine_speed = 0;$index_object0 = 0;$index_object1 = 0;$index_object2 = 0;$index_object3 = 0;$index_object4 = 0;$index_object5 = 0;$index_object6 = 0;$index_object7 = 0;$index_x_vel0 = 0;$index_x_vel1 = 0;$index_x_vel2 = 0;$index_x_vel3 = 0;$index_x_vel4 = 0;$index_x_vel5 = 0;$index_x_vel6 = 0;$index_x_vel7 = 0;$index_y_vel0 = 0;$index_y_vel1 = 0;$index_y_vel2 = 0;$index_y_vel3 = 0;$index_y_vel4 = 0;$index_y_vel5 = 0;$index_y_vel6 = 0;$index_y_vel7 = 0;$index_x_range0 = 0;$index_x_range1 = 0;$index_x_range2 = 0;$index_x_range3 = 0;$index_x_range4 = 0;$index_x_range5 = 0;$index_x_range6 = 0;$index_x_range7 = 0;$index_y_range0 = 0;$index_y_range1 = 0;$index_y_range2 = 0;$index_y_range3 = 0;$index_y_range4 = 0;$index_y_range5 = 0; $index_y_range6 = 0;$index_y_range7 = 0;

    $col_index = 0;
    foreach ($info_list[0] as $col) {
        switch (trim($col)) {
            case $EngineSpeed:
                $index_engine_speed = $col_index;
                $col_index+=1;
                break;
            case $VehicleCanSpeed:
                $index_can_speed = $col_index;
                $col_index+=1;
                break;
            case $BrakePedalPosition:
                $index_pedal = $col_index;
                $col_index+=1;
                break;
            case $Longitude:
                $index_lonti = $col_index;
                $col_index+=1;
                break;
            case $Latitude:
                $index_lati = $col_index;
                $col_index+=1;
                break;
            case $Accel_Y:
                $index_accel_y = $col_index;
                $col_index += 1;
                break;
            case $Accel_Z:
                $index_accel_z = $col_index;
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
                $index_object0 = $col_index;
                $col_index += 1;
                break;
            case $SMS_Object_ID_T1:
                $index_object1 = $col_index;
                $col_index += 1;
                break;
            case $SMS_Object_ID_T2:
                $index_object2 = $col_index;
                $col_index += 1;
                break;
            case $SMS_Object_ID_T3:
                $index_object3 = $col_index;
                $col_index += 1;
                break;
            case $SMS_Object_ID_T4:
                $index_object4 = $col_index;
                $col_index += 1;
                break;
            case $SMS_Object_ID_T5:
                $index_object5 = $col_index;
                $col_index += 1;
                break;
            case $SMS_Object_ID_T6:
                $index_object6 = $col_index;
                $col_index += 1;
                break;
            case $SMS_Object_ID_T7:
                $index_object7 = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Velocity_T0:
                $index_x_vel0 = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Velocity_T1:
                $index_x_vel1 = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Velocity_T2:
                $index_x_vel2 = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Velocity_T3:
                $index_x_vel3 = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Velocity_T4:
                $index_x_vel4 = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Velocity_T5:
                $index_x_vel5 = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Velocity_T6:
                $index_x_vel6 = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Velocity_T7:
                $index_x_vel7 = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Range_T0:
                $index_x_range0 = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Range_T1:
                $index_x_range1 = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Range_T2:
                $index_x_range2 = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Range_T3:
                $index_x_range3 = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Range_T4:
                $index_x_range4 = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Range_T5:
                $index_x_range5 = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Range_T6:
                $index_x_range6 = $col_index;
                $col_index += 1;
                break;
            case $SMS_X_Range_T7:
                $index_x_range7 = $col_index;
                $col_index += 1;
                break;
            case $SMS_Y_Range_T0:
                $index_y_range0 = $col_index;
                $col_index += 1;
                break;
            case $SMS_Y_Range_T1:
                $index_y_range1 = $col_index;
                $col_index += 1;
                break;
            case $SMS_Y_Range_T2:
                $index_y_range2 = $col_index;
                $col_index += 1;
                break;
            case $SMS_Y_Range_T3:
                $index_y_range3 = $col_index;
                $col_index += 1;
                break;
            case $SMS_Y_Range_T4:
                $index_y_range4 = $col_index;
                $col_index += 1;
                break;
            case $SMS_Y_Range_T5:
                $index_y_range5 = $col_index;
                $col_index += 1;
                break;
            case $SMS_Y_Range_T6:
                $index_y_range6 = $col_index;
                $col_index += 1;
                break;
            case $SMS_Y_Range_T7:
                $index_y_range7 = $col_index;
                $col_index += 1;
                break;
            case $SMS_Y_Velocity_T0:
                $index_y_vel0 = $col_index;
                $col_index +=1;
                break;
            case $SMS_Y_Velocity_T1:
                $index_y_vel1 = $col_index;
                $col_index +=1;
                break;
            case $SMS_Y_Velocity_T2:
                $index_y_vel2 = $col_index;
                $col_index +=1;
                break;
            case $SMS_Y_Velocity_T3:
                $index_y_vel3 = $col_index;
                $col_index +=1;
                break;
            case $SMS_Y_Velocity_T4:
                $index_y_vel4 = $col_index;
                $col_index +=1;
                break;
            case $SMS_Y_Velocity_T5:
                $index_y_vel5 = $col_index;
                $col_index +=1;
                break;
            case $SMS_Y_Velocity_T6:
                $index_y_vel6 = $col_index;
                $col_index +=1;
                break;
            case $SMS_Y_Velocity_T7:
                $index_y_vel7 = $col_index;
                $col_index +=1;
                break;
            default:
                $col_index += 1;
                break;
        }
    }

    $is_ini_start = true;
    $ini_start_flag = false;
    global $tmp_events;
    $last_ss_id = $event_id;
    $ini_speed = -100;
    $stop_count = 0;
    $go_count = 0;
    $hard_brake_count = 0;
    $turn_count = 0;
    $hard_swerve_count = 0;
    $lane_change_count = 0;
    $car_following_count = 0;

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
                    $new_row[] = "hard_brake"; //事件类型
                    $new_row[] = $event_id; // event_id
                    $new_row[] = $trip_event_id;
                    $new_row[] = $driver_id;
                    $new_row[] = $trip_id;
                    $new_row[] = $row[$index_speed]; //速度
                    $new_row[] = $row[$index_accel]; //加速度X,Y,Z
                    $new_row[] = $row[$index_accel_y];
                    $new_row[] = $row[$index_accel_z];
                    $new_row[] = $row[$index_lati];
                    $new_row[] = $row[$index_lonti];
                    $new_row[] = $row[$index_pedal];
                    $new_row[] = $row[$index_can_speed];
                    $new_row[] = $row[$index_engine_speed];
                    $new_row[] = $row[$index_object0];
                    $new_row[] = $row[$index_object1];
                    $new_row[] = $row[$index_object2];
                    $new_row[] = $row[$index_object3];
                    $new_row[] = $row[$index_object4];
                    $new_row[] = $row[$index_object5];
                    $new_row[] = $row[$index_object6];
                    $new_row[] = $row[$index_object7];
                    $new_row[] = $row[$index_x_range0];
                    $new_row[] = $row[$index_x_range1];
                    $new_row[] = $row[$index_x_range2];
                    $new_row[] = $row[$index_x_range3];
                    $new_row[] = $row[$index_x_range4];
                    $new_row[] = $row[$index_x_range5];
                    $new_row[] = $row[$index_x_range6];
                    $new_row[] = $row[$index_x_range7];
                    $new_row[] = $row[$index_x_vel0];
                    $new_row[] = $row[$index_x_vel1];
                    $new_row[] = $row[$index_x_vel2];
                    $new_row[] = $row[$index_x_vel3];
                    $new_row[] = $row[$index_x_vel4];
                    $new_row[] = $row[$index_x_vel5];
                    $new_row[] = $row[$index_x_vel6];
                    $new_row[] = $row[$index_x_vel7];
                    $new_row[] = $row[$index_y_range0];
                    $new_row[] = $row[$index_y_range1];
                    $new_row[] = $row[$index_y_range2];
                    $new_row[] = $row[$index_y_range3];
                    $new_row[] = $row[$index_y_range4];
                    $new_row[] = $row[$index_y_range5];
                    $new_row[] = $row[$index_y_range6];
                    $new_row[] = $row[$index_y_range7];
                    $new_row[] = $row[$index_y_vel0];
                    $new_row[] = $row[$index_y_vel1];
                    $new_row[] = $row[$index_y_vel2];
                    $new_row[] = $row[$index_y_vel3];
                    $new_row[] = $row[$index_y_vel4];
                    $new_row[] = $row[$index_y_vel5];
                    $new_row[] = $row[$index_y_vel6];
                    $new_row[] = $row[$index_y_vel7];
                    $new_row[] = $row[$index_lati];
                    $new_row[] = $row[$index_lonti];
                    $new_row[] = $row[$index_pedal];
                    $new_row[] = $row[$index_can_speed];
                    $new_row[] = $row[$index_engine_speed];
                    $new_row[] = $row[$index_object0];
                    $new_row[] = $row[$index_object1];
                    $new_row[] = $row[$index_object2];
                    $new_row[] = $row[$index_object3];
                    $new_row[] = $row[$index_object4];
                    $new_row[] = $row[$index_object5];
                    $new_row[] = $row[$index_object6];
                    $new_row[] = $row[$index_object7];
                    $new_row[] = $row[$index_x_range0];
                    $new_row[] = $row[$index_x_range1];
                    $new_row[] = $row[$index_x_range2];
                    $new_row[] = $row[$index_x_range3];
                    $new_row[] = $row[$index_x_range4];
                    $new_row[] = $row[$index_x_range5];
                    $new_row[] = $row[$index_x_range6];
                    $new_row[] = $row[$index_x_range7];
                    $new_row[] = $row[$index_x_vel0];
                    $new_row[] = $row[$index_x_vel1];
                    $new_row[] = $row[$index_x_vel2];
                    $new_row[] = $row[$index_x_vel3];
                    $new_row[] = $row[$index_x_vel4];
                    $new_row[] = $row[$index_x_vel5];
                    $new_row[] = $row[$index_x_vel6];
                    $new_row[] = $row[$index_x_vel7];
                    $new_row[] = $row[$index_y_range0];
                    $new_row[] = $row[$index_y_range1];
                    $new_row[] = $row[$index_y_range2];
                    $new_row[] = $row[$index_y_range3];
                    $new_row[] = $row[$index_y_range4];
                    $new_row[] = $row[$index_y_range5];
                    $new_row[] = $row[$index_y_range6];
                    $new_row[] = $row[$index_y_range7];
                    $new_row[] = $row[$index_y_vel0];
                    $new_row[] = $row[$index_y_vel1];
                    $new_row[] = $row[$index_y_vel2];
                    $new_row[] = $row[$index_y_vel3];
                    $new_row[] = $row[$index_y_vel4];
                    $new_row[] = $row[$index_y_vel5];
                    $new_row[] = $row[$index_y_vel6];
                    $new_row[] = $row[$index_y_vel7];
//                    $new_row[] = ""; //seq 每次事件内部数据点编号
//                    $new_row[] = ""; //每对停车事件编号
                    $event_type = 0;
                    $sud_brake_count += 1;
                    $tmp_event->time = $row[$index_time];
                    $tmp_event->driver_id = $driver_id;
                    $tmp_event->trip_id = $trip_id;
                    $tmp_event->type = "hard_brake";
                    $tmp_event->event_id = $event_id;
                    $tmp_event->csv_file_name = $file_name;
                    $tmp_events[] = $tmp_event;

                    $trip_event_id += 1;
                    $hard_brake_count += 1;
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
                    $new_row[] = "ini_start"; //事件类型
//                    $new_row[] = $last_ss_id; // event_id
                    $new_row[] = $event_id; // event_id
                    $new_row[] = $trip_event_id;
                    $new_row[] = $driver_id;
                    $new_row[] = $trip_id;
                    $new_row[] = $row[$index_speed]; //速度
                    $new_row[] = $row[$index_accel]; //加速度
                    $new_row[] = $row[$index_accel_y];
                    $new_row[] = $row[$index_accel_z];
                    $new_row[] = $row[$index_lati];
                    $new_row[] = $row[$index_lonti];
                    $new_row[] = $row[$index_pedal];
                    $new_row[] = $row[$index_can_speed];
                    $new_row[] = $row[$index_engine_speed];
                    $new_row[] = $row[$index_object0];
                    $new_row[] = $row[$index_object1];
                    $new_row[] = $row[$index_object2];
                    $new_row[] = $row[$index_object3];
                    $new_row[] = $row[$index_object4];
                    $new_row[] = $row[$index_object5];
                    $new_row[] = $row[$index_object6];
                    $new_row[] = $row[$index_object7];
                    $new_row[] = $row[$index_x_range0];
                    $new_row[] = $row[$index_x_range1];
                    $new_row[] = $row[$index_x_range2];
                    $new_row[] = $row[$index_x_range3];
                    $new_row[] = $row[$index_x_range4];
                    $new_row[] = $row[$index_x_range5];
                    $new_row[] = $row[$index_x_range6];
                    $new_row[] = $row[$index_x_range7];
                    $new_row[] = $row[$index_x_vel0];
                    $new_row[] = $row[$index_x_vel1];
                    $new_row[] = $row[$index_x_vel2];
                    $new_row[] = $row[$index_x_vel3];
                    $new_row[] = $row[$index_x_vel4];
                    $new_row[] = $row[$index_x_vel5];
                    $new_row[] = $row[$index_x_vel6];
                    $new_row[] = $row[$index_x_vel7];
                    $new_row[] = $row[$index_y_range0];
                    $new_row[] = $row[$index_y_range1];
                    $new_row[] = $row[$index_y_range2];
                    $new_row[] = $row[$index_y_range3];
                    $new_row[] = $row[$index_y_range4];
                    $new_row[] = $row[$index_y_range5];
                    $new_row[] = $row[$index_y_range6];
                    $new_row[] = $row[$index_y_range7];
                    $new_row[] = $row[$index_y_vel0];
                    $new_row[] = $row[$index_y_vel1];
                    $new_row[] = $row[$index_y_vel2];
                    $new_row[] = $row[$index_y_vel3];
                    $new_row[] = $row[$index_y_vel4];
                    $new_row[] = $row[$index_y_vel5];
                    $new_row[] = $row[$index_y_vel6];
                    $new_row[] = $row[$index_y_vel7];
//                    $new_row[] = 1; //seq 每次事件内部数据点编号
//                    $new_row[] = $s_s_num; //每对停车事件编号
                    $event_type = 1;
                    $tmp_event->time = $row[$index_time];
                    $tmp_event->driver_id = $driver_id;
                    $tmp_event->trip_id = $trip_id;
                    $tmp_event->event_id = $event_id;
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
                            $new_row[] = "go"; //事件类型
                            $new_row[] = $last_ss_id;
                            $new_row[] = $trip_event_id;
                            $new_row[] = $driver_id;
                            $new_row[] = $trip_id;
                            $new_row[] = $row[$index_speed]; //速度
                            $new_row[] = $row[$index_accel]; //加速度
                            $new_row[] = $row[$index_accel_y];
                            $new_row[] = $row[$index_accel_z];
                            $new_row[] = $row[$index_lati];
                            $new_row[] = $row[$index_lonti];
                            $new_row[] = $row[$index_pedal];
                            $new_row[] = $row[$index_can_speed];
                            $new_row[] = $row[$index_engine_speed];
                            $new_row[] = $row[$index_object0];
                            $new_row[] = $row[$index_object1];
                            $new_row[] = $row[$index_object2];
                            $new_row[] = $row[$index_object3];
                            $new_row[] = $row[$index_object4];
                            $new_row[] = $row[$index_object5];
                            $new_row[] = $row[$index_object6];
                            $new_row[] = $row[$index_object7];
                            $new_row[] = $row[$index_x_range0];
                            $new_row[] = $row[$index_x_range1];
                            $new_row[] = $row[$index_x_range2];
                            $new_row[] = $row[$index_x_range3];
                            $new_row[] = $row[$index_x_range4];
                            $new_row[] = $row[$index_x_range5];
                            $new_row[] = $row[$index_x_range6];
                            $new_row[] = $row[$index_x_range7];
                            $new_row[] = $row[$index_x_vel0];
                            $new_row[] = $row[$index_x_vel1];
                            $new_row[] = $row[$index_x_vel2];
                            $new_row[] = $row[$index_x_vel3];
                            $new_row[] = $row[$index_x_vel4];
                            $new_row[] = $row[$index_x_vel5];
                            $new_row[] = $row[$index_x_vel6];
                            $new_row[] = $row[$index_x_vel7];
                            $new_row[] = $row[$index_y_range0];
                            $new_row[] = $row[$index_y_range1];
                            $new_row[] = $row[$index_y_range2];
                            $new_row[] = $row[$index_y_range3];
                            $new_row[] = $row[$index_y_range4];
                            $new_row[] = $row[$index_y_range5];
                            $new_row[] = $row[$index_y_range6];
                            $new_row[] = $row[$index_y_range7];
                            $new_row[] = $row[$index_y_vel0];
                            $new_row[] = $row[$index_y_vel1];
                            $new_row[] = $row[$index_y_vel2];
                            $new_row[] = $row[$index_y_vel3];
                            $new_row[] = $row[$index_y_vel4];
                            $new_row[] = $row[$index_y_vel5];
                            $new_row[] = $row[$index_y_vel6];
                            $new_row[] = $row[$index_y_vel7];
//                            $new_row[] = 1; //seq 每次事件内部数据点编号
//                            $new_row[] = $s_s_num; //每对停车事件编号
                            $event_type = 2;
                            $tmp_event->time = $row[$index_time];
                            $tmp_event->driver_id = $driver_id;
                            $tmp_event->trip_id = $trip_id;
                            $tmp_event->event_id = $event_id;
                            $tmp_event->type = "go";
                            $tmp_event->csv_file_name = $file_name;
                            $tmp_events[] = $tmp_event;
                            $s_s_num += 1;
                            $go_count += 1;
                        } else {
                            $new_row[3] = $new_row[3] . ",go";
                        }
                    }
                }
                //停车事件
                if (((float)$row[$index_speed] == 0) & ($last_speed != 0)) {
                    if (empty($new_row)) {
                        $new_row[] = $row[$index_time]; //时间
                        $new_row[] = "stop"; //事件类型
                        $new_row[] = $event_id; // event_id
                        $new_row[] = $trip_event_id;
                        $new_row[] = $driver_id;
                        $new_row[] = $trip_id;
                        $new_row[] = $row[$index_speed]; //速度
                        $new_row[] = $row[$index_accel]; //加速度
                        $new_row[] = $row[$index_accel_y];
                        $new_row[] = $row[$index_accel_z];
                        $new_row[] = $row[$index_lati];
                        $new_row[] = $row[$index_lonti];
                        $new_row[] = $row[$index_pedal];
                        $new_row[] = $row[$index_can_speed];
                        $new_row[] = $row[$index_engine_speed];
                        $new_row[] = $row[$index_object0];
                        $new_row[] = $row[$index_object1];
                        $new_row[] = $row[$index_object2];
                        $new_row[] = $row[$index_object3];
                        $new_row[] = $row[$index_object4];
                        $new_row[] = $row[$index_object5];
                        $new_row[] = $row[$index_object6];
                        $new_row[] = $row[$index_object7];
                        $new_row[] = $row[$index_x_range0];
                        $new_row[] = $row[$index_x_range1];
                        $new_row[] = $row[$index_x_range2];
                        $new_row[] = $row[$index_x_range3];
                        $new_row[] = $row[$index_x_range4];
                        $new_row[] = $row[$index_x_range5];
                        $new_row[] = $row[$index_x_range6];
                        $new_row[] = $row[$index_x_range7];
                        $new_row[] = $row[$index_x_vel0];
                        $new_row[] = $row[$index_x_vel1];
                        $new_row[] = $row[$index_x_vel2];
                        $new_row[] = $row[$index_x_vel3];
                        $new_row[] = $row[$index_x_vel4];
                        $new_row[] = $row[$index_x_vel5];
                        $new_row[] = $row[$index_x_vel6];
                        $new_row[] = $row[$index_x_vel7];
                        $new_row[] = $row[$index_y_range0];
                        $new_row[] = $row[$index_y_range1];
                        $new_row[] = $row[$index_y_range2];
                        $new_row[] = $row[$index_y_range3];
                        $new_row[] = $row[$index_y_range4];
                        $new_row[] = $row[$index_y_range5];
                        $new_row[] = $row[$index_y_range6];
                        $new_row[] = $row[$index_y_range7];
                        $new_row[] = $row[$index_y_vel0];
                        $new_row[] = $row[$index_y_vel1];
                        $new_row[] = $row[$index_y_vel2];
                        $new_row[] = $row[$index_y_vel3];
                        $new_row[] = $row[$index_y_vel4];
                        $new_row[] = $row[$index_y_vel5];
                        $new_row[] = $row[$index_y_vel6];
                        $new_row[] = $row[$index_y_vel7];
//                        $new_row[] = 50; //seq 每次事件内部数据点编号
//                        $new_row[] = $s_s_num; //每对停车事件编号
                        $last_ss_id = $event_id;
                        $event_type = 3;
                        $tmp_event->time = $row[$index_time];
                        $tmp_event->driver_id = $driver_id;
                        $tmp_event->trip_id = $trip_id;
                        $tmp_event->type = "stop";
                        $tmp_event->event_id = $event_id;
                        $tmp_event->csv_file_name = $file_name;
                        $tmp_events[] = $tmp_event;
                        $stop_count += 1;
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
                    $new_row[] = $emer_degree; //事件类型
                    $new_row[] = $event_id; // event_id
                    $new_row[] = $trip_event_id;
                    $new_row[] = $driver_id;
                    $new_row[] = $trip_id;
                    $new_row[] = $row[$index_speed]; //速度
                    $new_row[] = $row[$index_accel]; //加速度
                    $new_row[] = $row[$index_accel_y];
                    $new_row[] = $row[$index_accel_z];
                    $new_row[] = $row[$index_lati];
                    $new_row[] = $row[$index_lonti];
                    $new_row[] = $row[$index_pedal];
                    $new_row[] = $row[$index_can_speed];
                    $new_row[] = $row[$index_engine_speed];
                    $new_row[] = $row[$index_object0];
                    $new_row[] = $row[$index_object1];
                    $new_row[] = $row[$index_object2];
                    $new_row[] = $row[$index_object3];
                    $new_row[] = $row[$index_object4];
                    $new_row[] = $row[$index_object5];
                    $new_row[] = $row[$index_object6];
                    $new_row[] = $row[$index_object7];
                    $new_row[] = $row[$index_x_range0];
                    $new_row[] = $row[$index_x_range1];
                    $new_row[] = $row[$index_x_range2];
                    $new_row[] = $row[$index_x_range3];
                    $new_row[] = $row[$index_x_range4];
                    $new_row[] = $row[$index_x_range5];
                    $new_row[] = $row[$index_x_range6];
                    $new_row[] = $row[$index_x_range7];
                    $new_row[] = $row[$index_x_vel0];
                    $new_row[] = $row[$index_x_vel1];
                    $new_row[] = $row[$index_x_vel2];
                    $new_row[] = $row[$index_x_vel3];
                    $new_row[] = $row[$index_x_vel4];
                    $new_row[] = $row[$index_x_vel5];
                    $new_row[] = $row[$index_x_vel6];
                    $new_row[] = $row[$index_x_vel7];
                    $new_row[] = $row[$index_y_range0];
                    $new_row[] = $row[$index_y_range1];
                    $new_row[] = $row[$index_y_range2];
                    $new_row[] = $row[$index_y_range3];
                    $new_row[] = $row[$index_y_range4];
                    $new_row[] = $row[$index_y_range5];
                    $new_row[] = $row[$index_y_range6];
                    $new_row[] = $row[$index_y_range7];
                    $new_row[] = $row[$index_y_vel0];
                    $new_row[] = $row[$index_y_vel1];
                    $new_row[] = $row[$index_y_vel2];
                    $new_row[] = $row[$index_y_vel3];
                    $new_row[] = $row[$index_y_vel4];
                    $new_row[] = $row[$index_y_vel5];
                    $new_row[] = $row[$index_y_vel6];
                    $new_row[] = $row[$index_y_vel7];
//                    $new_row[] = 1; //seq 每次事件内部数据点编号
//                    $new_row[] = ""; //每对停车事件编号
                    $event_type = 4;
                    $tmp_event->time = $row[$index_time];
                    $tmp_event->driver_id = $driver_id;
                    $tmp_event->trip_id = $trip_id;
                    $tmp_event->type = $emer_degree;
                    $tmp_event->event_id = $event_id;
                    $tmp_event->csv_file_name = $file_name;
                    $tmp_events[] = $tmp_event;

                    $hard_swerve_count += 1;
                }
            }
        }
        if (in_array("car_following", $types)) {
            carFollowing($row[$index_time], $row[$index_speed], $row[$index_object0], $row[$index_x_vel0], $row[$index_x_range0], $row[$index_y_range0]);
        }
        if ($event_type == 0) {
            $i = 2;
            $new_rows_hb[] = $new_row;
            $tmp_begin = $row_num + 1;
            for ($tmp_begin, $i; ($tmp_begin < $row_num + 10 * $config->forward_hard_brake) && ($tmp_begin < count($info_list, 0)); $tmp_begin++, $i++) {
                $new_row_brake = array();
                $new_row_brake[] = $info_list[$tmp_begin][$index_time];//时间
                $new_row_brake[] = "hard_brake"; //事件类型
                $new_row_brake[] = $event_id; //event_id
                $new_row_brake[] = $trip_event_id;
                $new_row_brake[] = $driver_id;
                $new_row_brake[] = $trip_id;
                $new_row_brake[] = $info_list[$tmp_begin][$index_speed];//速度
                $new_row_brake[] = $info_list[$tmp_begin][$index_accel];//加速度
                $new_row_brake[] = $info_list[$tmp_begin][$index_accel_y];
                $new_row_brake[] = $info_list[$tmp_begin][$index_accel_z];
                $new_row_brake[] = $info_list[$tmp_begin][$index_lati];
                $new_row_brake[] = $info_list[$tmp_begin][$index_lonti];
                $new_row_brake[] = $info_list[$tmp_begin][$index_pedal];
                $new_row_brake[] = $info_list[$tmp_begin][$index_can_speed];
                $new_row_brake[] = $info_list[$tmp_begin][$index_engine_speed];
                $new_row_brake[] = $info_list[$tmp_begin][$index_object0];
                $new_row_brake[] = $info_list[$tmp_begin][$index_object1];
                $new_row_brake[] = $info_list[$tmp_begin][$index_object2];
                $new_row_brake[] = $info_list[$tmp_begin][$index_object3];
                $new_row_brake[] = $info_list[$tmp_begin][$index_object4];
                $new_row_brake[] = $info_list[$tmp_begin][$index_object5];
                $new_row_brake[] = $info_list[$tmp_begin][$index_object6];
                $new_row_brake[] = $info_list[$tmp_begin][$index_object7];
                $new_row_brake[] = $info_list[$tmp_begin][$index_x_range0];
                $new_row_brake[] = $info_list[$tmp_begin][$index_x_range1];
                $new_row_brake[] = $info_list[$tmp_begin][$index_x_range2];
                $new_row_brake[] = $info_list[$tmp_begin][$index_x_range3];
                $new_row_brake[] = $info_list[$tmp_begin][$index_x_range4];
                $new_row_brake[] = $info_list[$tmp_begin][$index_x_range5];
                $new_row_brake[] = $info_list[$tmp_begin][$index_x_range6];
                $new_row_brake[] = $info_list[$tmp_begin][$index_x_range7];
                $new_row_brake[] = $info_list[$tmp_begin][$index_x_vel0];
                $new_row_brake[] = $info_list[$tmp_begin][$index_x_vel1];
                $new_row_brake[] = $info_list[$tmp_begin][$index_x_vel2];
                $new_row_brake[] = $info_list[$tmp_begin][$index_x_vel3];
                $new_row_brake[] = $info_list[$tmp_begin][$index_x_vel4];
                $new_row_brake[] = $info_list[$tmp_begin][$index_x_vel5];
                $new_row_brake[] = $info_list[$tmp_begin][$index_x_vel6];
                $new_row_brake[] = $info_list[$tmp_begin][$index_x_vel7];
                $new_row_brake[] = $info_list[$tmp_begin][$index_y_range0];
                $new_row_brake[] = $info_list[$tmp_begin][$index_y_range1];
                $new_row_brake[] = $info_list[$tmp_begin][$index_y_range2];
                $new_row_brake[] = $info_list[$tmp_begin][$index_y_range3];
                $new_row_brake[] = $info_list[$tmp_begin][$index_y_range4];
                $new_row_brake[] = $info_list[$tmp_begin][$index_y_range5];
                $new_row_brake[] = $info_list[$tmp_begin][$index_y_range6];
                $new_row_brake[] = $info_list[$tmp_begin][$index_y_range7];
                $new_row_brake[] = $info_list[$tmp_begin][$index_y_vel0];
                $new_row_brake[] = $info_list[$tmp_begin][$index_y_vel1];
                $new_row_brake[] = $info_list[$tmp_begin][$index_y_vel2];
                $new_row_brake[] = $info_list[$tmp_begin][$index_y_vel3];
                $new_row_brake[] = $info_list[$tmp_begin][$index_y_vel4];
                $new_row_brake[] = $info_list[$tmp_begin][$index_y_vel5];
                $new_row_brake[] = $info_list[$tmp_begin][$index_y_vel6];
                $new_row_brake[] = $info_list[$tmp_begin][$index_y_vel7];

//                $new_row_brake[] = $i;//seq 每次事件内部数据点编号
//                $new_row_brake[] = $s_s_num; //每对停车事件编号
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
                $new_row_start[] = "ini_start"; //事件类型
                $new_row_start[] = $event_id; //event_id
                $new_row_start[] = $trip_event_id;
                $new_row_start[] = $driver_id;
                $new_row_start[] = $trip_id;
                $new_row_start[] = $info_list[$tmp_begin][$index_speed];//速度
                $new_row_start[] = $info_list[$tmp_begin][$index_accel];//加速度
                $new_row_start[] = $info_list[$tmp_begin][$index_accel_y];
                $new_row_start[] = $info_list[$tmp_begin][$index_accel_z];
                $new_row_start[] = $info_list[$tmp_begin][$index_lati];
                $new_row_start[] = $info_list[$tmp_begin][$index_lonti];
                $new_row_start[] = $info_list[$tmp_begin][$index_pedal];
                $new_row_start[] = $info_list[$tmp_begin][$index_can_speed];
                $new_row_start[] = $info_list[$tmp_begin][$index_engine_speed];
                $new_row_start[] = $info_list[$tmp_begin][$index_object0];
                $new_row_start[] = $info_list[$tmp_begin][$index_object1];
                $new_row_start[] = $info_list[$tmp_begin][$index_object2];
                $new_row_start[] = $info_list[$tmp_begin][$index_object3];
                $new_row_start[] = $info_list[$tmp_begin][$index_object4];
                $new_row_start[] = $info_list[$tmp_begin][$index_object5];
                $new_row_start[] = $info_list[$tmp_begin][$index_object6];
                $new_row_start[] = $info_list[$tmp_begin][$index_object7];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_range0];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_range1];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_range2];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_range3];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_range4];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_range5];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_range6];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_range7];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_vel0];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_vel1];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_vel2];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_vel3];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_vel4];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_vel5];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_vel6];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_vel7];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_range0];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_range1];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_range2];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_range3];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_range4];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_range5];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_range6];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_range7];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_vel0];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_vel1];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_vel2];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_vel3];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_vel4];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_vel5];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_vel6];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_vel7];
//                $new_row_start[] = $i;//seq 每次事件内部数据点编号
//                $new_row_start[] = $s_s_num; //每对停车事件编号
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
                $new_row_start[] = "go"; //事件类型
                $new_row_start[] = $last_ss_id; //event_id
                $new_row_start[] = $trip_event_id;
                $new_row_start[] = $driver_id;
                $new_row_start[] = $trip_id;
                $new_row_start[] = $info_list[$tmp_begin][$index_speed];//速度
                $new_row_start[] = $info_list[$tmp_begin][$index_accel];//加速度
                $new_row_start[] = $info_list[$tmp_begin][$index_accel_y];
                $new_row_start[] = $info_list[$tmp_begin][$index_accel_z];
                $new_row_start[] = $info_list[$tmp_begin][$index_lati];
                $new_row_start[] = $info_list[$tmp_begin][$index_lonti];
                $new_row_start[] = $info_list[$tmp_begin][$index_pedal];
                $new_row_start[] = $info_list[$tmp_begin][$index_can_speed];
                $new_row_start[] = $info_list[$tmp_begin][$index_engine_speed];
                $new_row_start[] = $info_list[$tmp_begin][$index_object0];
                $new_row_start[] = $info_list[$tmp_begin][$index_object1];
                $new_row_start[] = $info_list[$tmp_begin][$index_object2];
                $new_row_start[] = $info_list[$tmp_begin][$index_object3];
                $new_row_start[] = $info_list[$tmp_begin][$index_object4];
                $new_row_start[] = $info_list[$tmp_begin][$index_object5];
                $new_row_start[] = $info_list[$tmp_begin][$index_object6];
                $new_row_start[] = $info_list[$tmp_begin][$index_object7];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_range0];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_range1];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_range2];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_range3];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_range4];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_range5];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_range6];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_range7];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_vel0];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_vel1];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_vel2];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_vel3];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_vel4];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_vel5];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_vel6];
                $new_row_start[] = $info_list[$tmp_begin][$index_x_vel7];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_range0];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_range1];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_range2];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_range3];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_range4];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_range5];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_range6];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_range7];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_vel0];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_vel1];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_vel2];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_vel3];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_vel4];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_vel5];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_vel6];
                $new_row_start[] = $info_list[$tmp_begin][$index_y_vel7];
//                $new_row_start[] = $i;//seq 每次事件内部数据点编号
//                $new_row_start[] = $s_s_num; //每对停车事件编号
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
                $new_row_stop[] = "stop"; //事件类型
                $new_row_stop[] = $event_id; //event_id
                $new_row_stop[] = $trip_event_id;
                $new_row_stop[] = $driver_id;
                $new_row_stop[] = $trip_id;
                $new_row_stop[] = $info_list[$tmp_begin + 1][$index_speed];//速度
                $new_row_stop[] = $info_list[$tmp_begin + 1][$index_accel];//加速度
                $new_row_stop[] = $info_list[$tmp_begin][$index_accel_y];
                $new_row_stop[] = $info_list[$tmp_begin][$index_accel_z];
                $new_row_stop[] = $info_list[$tmp_begin][$index_lati];
                $new_row_stop[] = $info_list[$tmp_begin][$index_lonti];
                $new_row_stop[] = $info_list[$tmp_begin][$index_pedal];
                $new_row_stop[] = $info_list[$tmp_begin][$index_can_speed];
                $new_row_stop[] = $info_list[$tmp_begin][$index_engine_speed];
                $new_row_stop[] = $info_list[$tmp_begin][$index_object0];
                $new_row_stop[] = $info_list[$tmp_begin][$index_object1];
                $new_row_stop[] = $info_list[$tmp_begin][$index_object2];
                $new_row_stop[] = $info_list[$tmp_begin][$index_object3];
                $new_row_stop[] = $info_list[$tmp_begin][$index_object4];
                $new_row_stop[] = $info_list[$tmp_begin][$index_object5];
                $new_row_stop[] = $info_list[$tmp_begin][$index_object6];
                $new_row_stop[] = $info_list[$tmp_begin][$index_object7];
                $new_row_stop[] = $info_list[$tmp_begin][$index_x_range0];
                $new_row_stop[] = $info_list[$tmp_begin][$index_x_range1];
                $new_row_stop[] = $info_list[$tmp_begin][$index_x_range2];
                $new_row_stop[] = $info_list[$tmp_begin][$index_x_range3];
                $new_row_stop[] = $info_list[$tmp_begin][$index_x_range4];
                $new_row_stop[] = $info_list[$tmp_begin][$index_x_range5];
                $new_row_stop[] = $info_list[$tmp_begin][$index_x_range6];
                $new_row_stop[] = $info_list[$tmp_begin][$index_x_range7];
                $new_row_stop[] = $info_list[$tmp_begin][$index_x_vel0];
                $new_row_stop[] = $info_list[$tmp_begin][$index_x_vel1];
                $new_row_stop[] = $info_list[$tmp_begin][$index_x_vel2];
                $new_row_stop[] = $info_list[$tmp_begin][$index_x_vel3];
                $new_row_stop[] = $info_list[$tmp_begin][$index_x_vel4];
                $new_row_stop[] = $info_list[$tmp_begin][$index_x_vel5];
                $new_row_stop[] = $info_list[$tmp_begin][$index_x_vel6];
                $new_row_stop[] = $info_list[$tmp_begin][$index_x_vel7];
                $new_row_stop[] = $info_list[$tmp_begin][$index_y_range0];
                $new_row_stop[] = $info_list[$tmp_begin][$index_y_range1];
                $new_row_stop[] = $info_list[$tmp_begin][$index_y_range2];
                $new_row_stop[] = $info_list[$tmp_begin][$index_y_range3];
                $new_row_stop[] = $info_list[$tmp_begin][$index_y_range4];
                $new_row_stop[] = $info_list[$tmp_begin][$index_y_range5];
                $new_row_stop[] = $info_list[$tmp_begin][$index_y_range6];
                $new_row_stop[] = $info_list[$tmp_begin][$index_y_range7];
                $new_row_stop[] = $info_list[$tmp_begin][$index_y_vel0];
                $new_row_stop[] = $info_list[$tmp_begin][$index_y_vel1];
                $new_row_stop[] = $info_list[$tmp_begin][$index_y_vel2];
                $new_row_stop[] = $info_list[$tmp_begin][$index_y_vel3];
                $new_row_stop[] = $info_list[$tmp_begin][$index_y_vel4];
                $new_row_stop[] = $info_list[$tmp_begin][$index_y_vel5];
                $new_row_stop[] = $info_list[$tmp_begin][$index_y_vel6];
                $new_row_stop[] = $info_list[$tmp_begin][$index_y_vel7];
//                $new_row_stop[] = $i; //seq 每次事件内部数据点编号
//                $new_row_stop[] = $s_s_num; //每对停车事件编号
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
                $new_row_swerve[] = $emer_degree; //事件类型
                $new_row_swerve[] = $event_id; //event_id
                $new_row_swerve[] = $trip_event_id;
                $new_row_swerve[] = $driver_id;
                $new_row_swerve[] = $trip_id;
                $new_row_swerve[] = $info_list[$tmp_begin][$index_speed];//速度
                $new_row_swerve[] = $info_list[$tmp_begin][$index_accel];//加速度
                $new_row_swerve[] = $info_list[$tmp_begin][$index_accel_y];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_accel_z];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_lati];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_lonti];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_pedal];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_can_speed];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_engine_speed];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_object0];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_object1];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_object2];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_object3];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_object4];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_object5];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_object6];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_object7];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_x_range0];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_x_range1];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_x_range2];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_x_range3];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_x_range4];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_x_range5];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_x_range6];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_x_range7];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_x_vel0];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_x_vel1];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_x_vel2];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_x_vel3];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_x_vel4];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_x_vel5];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_x_vel6];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_x_vel7];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_y_range0];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_y_range1];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_y_range2];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_y_range3];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_y_range4];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_y_range5];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_y_range6];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_y_range7];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_y_vel0];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_y_vel1];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_y_vel2];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_y_vel3];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_y_vel4];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_y_vel5];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_y_vel6];
                $new_row_swerve[] = $info_list[$tmp_begin][$index_y_vel7];
//                $new_row_swerve[] = $i;//seq 每次事件内部数据点编号
//                $new_row_swerve[] = ""; //每对停车事件编号
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
            $event_id += 1;
            $stop_count -= 1;
        } else {
            array_splice($tmp_events, count($tmp_events) - 1, 1);
            $event_id -= 1;
            $stop_count -= 1;
        }
    }

    if (in_array("car_following", $types)) {
        global $followingEvent;
        $car_following_count = count($followingEvent);
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
                $new_row_following[] = "car_following"; //事件类型
                $new_row_following[] = $event_id; //event_id
                $new_row_following[] = $trip_event_id;
                $new_row_following[] = $driver_id;
                $new_row_following[] = $trip_id;
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_speed];//速度
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_accel];//加速度
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_accel_y];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_accel_z];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_lati];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_lonti];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_pedal];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_can_speed];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_engine_speed];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_object0];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_object1];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_object2];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_object3];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_object4];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_object5];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_object6];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_object7];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_x_range0];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_x_range1];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_x_range2];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_x_range3];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_x_range4];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_x_range5];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_x_range6];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_x_range7];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_x_vel0];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_x_vel1];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_x_vel2];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_x_vel3];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_x_vel4];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_x_vel5];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_x_vel6];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_x_vel7];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_y_range0];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_y_range1];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_y_range2];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_y_range3];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_y_range4];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_y_range5];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_y_range6];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_y_range7];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_y_vel0];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_y_vel1];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_y_vel2];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_y_vel3];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_y_vel4];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_y_vel5];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_y_vel6];
                $new_row_following[] = $info_list[$tmp_begin + $i][$index_y_vel7];

//                $new_row_following[] = $i + 1;//seq 每次事件内部数据点编号
//                $new_row_following[] = ""; //每对停车事件编号
                $new_rows_car_following[] = $new_row_following;
                unset($new_row_following);
            }
            $trip_event_id += 1;
            $event_id += 1;
        }
        array_splice($followingEvent, 0, count($followingEvent));
    }

    if (in_array("lane_change", $types)) {
        global $laneChangeEvent;
        global $laneChangeEvents;
        $lane_change_file_name = '../public/lane_change/' . 'lane_change_list_' . $driver_id . '_' . $trip_id;
        if (file_exists($lane_change_file_name)) {
            $lane_change_list_file = fopen($lane_change_file_name, "r") or die("Unable to open file!");
            $laneChangeEvents = fread($lane_change_file_name, filesize($lane_change_file_name));
            fclose($lane_change_list_file);
            $tmp_arr = explode('&', $laneChangeEvents);
            $laneChangeEvent = $tmp_arr[$trip_seq];
            $trip_seq += 1;
        } else {
            $video_file_path = '../video/' . substr($file_name, 0, strlen($file_name) - 4) . '_Front.mp4';
            lane_change_detection($video_file_path, $file_dir, 1);
            if ($laneChangeEvents == "") {
                $laneChangeEvents = $laneChangeEvent;
            } else {
                if ($laneChangeEvent != "") {
                    $laneChangeEvents = $laneChangeEvents . "&" . $laneChangeEvent;
                }
            }
        }
        $lane_change_str = str_replace("]", "", trim($laneChangeEvent, '[ ]'));
        $lane_change_str = str_replace("[", "", $lane_change_str);
        $lane_change_arr = explode(',', $lane_change_str);
        $lane_change_count = count($lane_change_arr);
        foreach ($lane_change_arr as $time) {
            $tmp_begin = (int)trim($time);
            for($tmp_begin; ($tmp_begin < (int)trim($time) + 100)&($tmp_begin < count($info_list, 0)); $tmp_begin++){
                $new_row_lane_change = array();
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_time];//时间
                $new_row_lane_change[] = "car_following"; //事件类型
                $new_row_lane_change[] = $event_id; //event_id
                $new_row_lane_change[] = $trip_event_id;
                $new_row_lane_change[] = $driver_id;
                $new_row_lane_change[] = $trip_id;
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_speed];//速度
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_accel];//加速度
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_accel_y];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_accel_z];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_lati];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_lonti];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_pedal];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_can_speed];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_engine_speed];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_object0];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_object1];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_object2];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_object3];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_object4];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_object5];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_object6];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_object7];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_x_range0];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_x_range1];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_x_range2];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_x_range3];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_x_range4];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_x_range5];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_x_range6];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_x_range7];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_x_vel0];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_x_vel1];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_x_vel2];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_x_vel3];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_x_vel4];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_x_vel5];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_x_vel6];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_x_vel7];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_y_range0];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_y_range1];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_y_range2];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_y_range3];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_y_range4];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_y_range5];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_y_range6];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_y_range7];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_y_vel0];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_y_vel1];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_y_vel2];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_y_vel3];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_y_vel4];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_y_vel5];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_y_vel6];
                $new_row_lane_change[] = $info_list[$tmp_begin + $i][$index_y_vel7];

                $new_rows_lane_change[] = $new_row_lane_change;
            }
            $tmp_event_lane_change = new event();
            $tmp_event_lane_change->driver_id = $driver_id;
            $tmp_event_lane_change->trip_id = $trip_id;
            $tmp_event_lane_change->event_id = $event_id;
            $tmp_event_lane_change->type = "lane_change";
            $tmp_event_lane_change->time = (int)trim($time);
            $tmp_event_lane_change->duration = 0;
            $tmp_event_lane_change->csv_file_name = $file_name;
            $tmp_events[] = $tmp_event_lane_change;
            $event_id += 1;
            $trip_event_id += 1;
        }
        $laneChangeEvent = "";
    }
    fclose($file);

    $trip = new trip();
    $trip->driver_id = $driver_id;
    $trip->trip_id = $trip_id;
    $trip->stop_count = $stop_count;
    $trip->go_count = $go_count;
    $trip->cf_count = $car_following_count;
    $trip->turn_count = $turn_count;
    $trip->lc_count = $lane_change_count;
    $trip->hb_count = $hard_brake_count;
    $trip->hs_count = $hard_swerve_count;
    global $trips;
    $trips[] = $trip;
    global $trips_json_str;
    $trips_json_str = json_encode($trips);
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