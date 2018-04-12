<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/10
 * Time: 13:24
 */

function find_trip($driver_id, $trip_id, $types, $threshold, $csv_file_dir)
{
    $files_folder = scandir($csv_file_dir);
    $file_name = "";
    foreach ($files_folder as $name) {
        if (startWith("CCHN_" . $driver_id, $name) && endWith($trip_id . ".csv", $name)) {
            $file_name = $name;
        }
    }

    $file_dir = $csv_file_dir . "/" . $file_name;
    process_file($file_dir, $types, $threshold, $driver_id, $trip_id);
}

function find_trips($driver_id, $trip_id, $types, $threshold, $csv_file_dir)
{
    $files_folder = scandir($csv_file_dir);
    foreach ($files_folder as $name) {
        echo "<script type=text/javascript>console.log('" . $name . "')</script>";
        if (startWith("CCHN_" . $driver_id, $name)) {
            $file_names[] = $name;
        }
    }
    if (!empty($file_names)) {
        foreach ($file_names as $file_name) {
            $file_dir = $csv_file_dir . "/" . $file_name;
            process_file($file_dir, $types, $threshold, $driver_id, $trip_id);
        }

    } else return null;
}

function process_file($file_dir, $types, $threshold, $driver_id, $trip_id)
{
    if ($trip_id == "") {
        $trip_id = substr($file_dir, sizeof($file_dir) - 10, 5);
    }
    global $new_rows_ss;
    global $new_rows_hb;
    global $new_rows_turn;
    global $event_id;

    $brake_time = 0;  //急刹车事件发生时间，之后的10秒发生的急刹车算同一次。用于判断。
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
    $Speed = "FOT_Control.Speed";

    $index_time = 0;
    $index_accel = 0;
    $index_speed = 0;

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
            default:
                $col_index += 1;
                break;
        }
    }
    global $tmp_events;
    $is_ini_start = true;

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
                if (is_hard_brake((float)$row[$index_speed], $threshold, (float)$row[$index_time], $brake_time)) {
                    $brake_time = $row[$index_time];
                    $new_row[] = $row[$index_time]; //时间
                    $new_row[] = $row[$index_speed]; //速度
                    $new_row[] = $row[$index_accel]; //加速度
                    $new_row[] = "sud_brake"; //事件类型
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
                    $tmp_event->type = "sud_brake";
                    $tmp_events[] = $tmp_event;

                    $new_rows_hb[] = $new_row;
                    $event_id += 1;
                    $trip_event_id += 1;
                }
            }

        }

        //判断是否为启停事件
        if (in_array("start_stop", $types)) {
            //判断是否为启停事件
            if (($row[$index_speed] != "") & ($row[$index_speed] != " ")) {
                //启动事件

                if (((float)$row[$index_speed] != 0) & ($last_speed == 0)) {
                    if ($is_ini_start) {
                        $is_ini_start = false;

                    } else {
                        $s_s_num += 1;
                        if (empty($new_row)) {
                            $new_row[] = $row[$index_time]; //时间
                            $new_row[] = $row[$index_speed]; //速度
                            $new_row[] = $row[$index_accel]; //加速度
                            $new_row[] = "start"; //事件类型
                            $event_type = 1;
                            $new_row[] = $event_id; // event_id
                            $new_row[] = 1; //seq 每次事件内部数据点编号
                            $new_row[] = $s_s_num; //每对停车事件编号
                            $new_row[] = $trip_event_id;
                            $new_row[] = $driver_id;
                            $new_row[] = $trip_id;

                            $tmp_event->time = $row[$index_time];
                            $tmp_event->driver_id = $driver_id;
                            $tmp_event->trip_id = $trip_id;
                            $tmp_event->type = "start";
                            $tmp_events[] = $tmp_event;
                        } else {
                            $new_row[3] = $new_row[3] . ",start";
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
                        $event_type = 2;
                        $new_row[] = $event_id; // event_id
                        $new_row[] = 50; //seq 每次事件内部数据点编号
                        $new_row[] = $s_s_num; //每对停车事件编号
                        $new_row[] = $trip_event_id;
                        $new_row[] = $driver_id;
                        $new_row[] = $trip_id;

                        $tmp_event->time = $row[$index_time];
                        $tmp_event->driver_id = $driver_id;
                        $tmp_event->trip_id = $trip_id;
                        $tmp_event->type = "stop";
                        $tmp_events[] = $tmp_event;
                    } else {
                        $new_row[3] = $new_row[3] . ",stop";
                    }
                }
                $last_speed = (float)$row[$index_speed];
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
            $new_rows_ss[] = $new_row;
            $tmp_begin = $row_num + 1;
            for ($tmp_begin, $i; ($tmp_begin < $row_num + 50) && ($tmp_begin < count($info_list, 0)); $tmp_begin++, $i++) {
                $new_row_start = array();
                $new_row_start[] = $info_list[$tmp_begin][$index_time];//时间
                $new_row_start[] = $info_list[$tmp_begin][$index_speed];//速度
                $new_row_start[] = $info_list[$tmp_begin][$index_accel];//加速度
                $new_row_start[] = "start"; //事件类型
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
            $i = 1;
            $tmp_begin = 1;
            if ($row_num - 50 >= 0) {
                $tmp_begin = $row_num - 50;
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
            $event_id += 1;
            $trip_event_id += 1;
        }

        $row_num += 1;
        array_splice($new_row, 0, count($new_row));
//        unset($new_row);
    }

    fclose($file);

}


function startWith($needle, $name)
{
    return strpos($name, $needle) === 0;
}

function endWith($needle, $name)
{
    return (strrchr($name, $needle) == $needle);
}