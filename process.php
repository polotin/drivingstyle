<?php
/**
 * Created by PhpStorm.
 * User: Polotin
 * Date: 2018/3/29
 * Time: 18:44
 */
include 'event.php';
include 'event_handler.php';
$json_file_names = array();
$new_rows_ss = array();
$new_rows_hb = array();
$new_rows_turn = array();
$event_id = 1;  //事件编号，所有TRIP内的事件都唯一
$tmp_events = array();  //所有事件 存为JSON

function process($driver_id, $trip_id, $types, $threshold, $csv_file_dir, $video_file_dir, $output_dir, $video_play_pre, $video_play_fol)
{
    echo "__FILE__: ========> ".__FILE__;
    $file_name = "";
    $file_names = array();
    $files_folder = array();

    $info_list = array();  //存放所有行车信息
    //driver的单次trip
    if (trim($trip_id) != "") {
        $file_name = "CCHN_" . $driver_id . "_229730_46_130419_1430_" . $trip_id . ".csv";
        $file_dir = $csv_file_dir . "/" . $file_name;
        global $json_file_names;
        $json_file_names[] = process_file($file_dir, $types, $threshold, $driver_id, $trip_id);

    } else if ($driver_id != "") {      //driver的所有trip
//        $hostdir = dirname($csv_file_dir);
        $files_folder = scandir($csv_file_dir);

        //test
        if(is_dir($csv_file_dir))
        {
            echo("<script>console.log('111111');</script>");
        }else{
            echo("<script>console.log('222222');</script>");
        }

        foreach ($files_folder as $file) {
                echo("<script>console.log('".$file."');</script>");
        }
        //test
        foreach ($files_folder as $name) {
            echo("<script>console.log('".$name."');</script>");
            if (startWith("CCHN_" . $driver_id, $name)) {
                $file_names[] = $name;
            }
        }


        if (!empty($file_names)) {
            foreach ($file_names as $file_name) {
                $file_dir = $csv_file_dir . "/" . $file_name;
                echo("<script>console.log('".$types[0]."');</script>");
                process_file($file_dir, $types, $threshold, $driver_id, $trip_id);
            }

        } else return null;
    } else {
        return null;
    }

    //输出JSON
    global $tmp_events;
    $json_str = json_encode($tmp_events);
    date_default_timezone_set("Asia/Shanghai");
    $cur_time = date("Y-m-d-H-i-s");
    $json_file_name = $driver_id."_".$cur_time . '.json';
    $file_path = "data/" . $json_file_name;
    $new_file = fopen($file_path, "w") or die("Unable to open file!");
    fwrite($new_file, $json_str);
    fclose($new_file);

    //输出为CSV文件

    $csv_header = ['time', 'speed', 'accel', 'event_type', 'event_id', 'seq', 's_s_num', 'trip_event_id', 'driver_id', 'trip_id'];
    //$content = $head.$new_rows;
    $header = implode(',', $csv_header) . PHP_EOL;

    global $new_rows_ss;
    global $new_rows_hb;
    global $new_rows_turn;

    if(in_array("start_stop",$types)){
        $content = '';
        foreach ($new_rows_ss as $line) {
            $content .= implode(',', $line) . PHP_EOL;
        }
        $csv_file_name = $driver_id . "_" . $trip_id . "_"."start_stop" . $cur_time . ".csv";
        $csv_file_dir = $output_dir."\\".$csv_file_name;
        $output = fopen($csv_file_dir, 'w') or die("can not open");
        $csv = $header . $content;
        fwrite($output, $csv);
        fclose($output) or die("can not close");
    }
    echo("<script>console.log('".sizeof($new_rows_hb)."');</script>");
    if(in_array("hard_brake",$types)){
        $content = '';
        foreach ($new_rows_hb as $line) {
            $content .= implode(',', $line) . PHP_EOL;
        }
        $csv_file_name = $driver_id . "_" . $trip_id . "_"."hard_brake" . $cur_time . ".csv";
        $csv_file_dir = $output_dir."\\".$csv_file_name;
        $output1 = fopen($csv_file_dir, 'w') or die("can not open");
        $csv = $header . $content;
        fwrite($output1, $csv);
        fclose($output1) or die("can not close");
    }
    if(in_array("turn",$types)){
        $content = '';
        foreach ($new_rows_turn as $line) {
            $content .= implode(',', $line) . PHP_EOL;
        }
        $csv_file_name = $driver_id . "_" . $trip_id . "_"."turn" . $cur_time . ".csv";
        $csv_file_dir = $output_dir."\\".$csv_file_name;
        $output = fopen($csv_file_dir, 'w') or die("can not open");
        $csv = $header . $content;
        fwrite($output, $csv);
        fclose($output) or die("can not close");
    }
//    $csv = $header . $content;
//    fwrite($output, $csv);
//    fclose($output) or die("can not close");

}

function process_file($file_dir, $types, $threshold, $driver_id, $trip_id)
{
    if ($trip_id == "") {
        $trip_id = substr($file_dir, sizeof($file_dir) -9, 5);
    }
    global $new_rows_ss;
    global $new_rows_hb;
    global $new_rows_turn;
    global $event_id;

    $brake_time = 0;  //急刹车事件发生时间，之后的10秒发生的急刹车算同一次。用于判断。
    $last_speed = 0;  //存放上一个时间点的速度
    $s_s_num = 0;       //停启编号
    $sud_brake_count = 0;   //急刹车次数
    //便遍历每一行数据
    $row_num = 0;   //当前遍历行数

    $new_rows = array();
    $trip_event_id = 1; //每次行程事件编号

    $file = fopen($file_dir, "r");
    $info_list = array();  //存放该文件中所有行车信息
    while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容
        $info_list[] = $data;
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
            if ($row[3] != "" & $row[3] != " ") {
                if (is_hard_brake((float)$row[3], $threshold, (float)$row[0], $brake_time)) {
                    $brake_time = $row[0];
                    $new_row[] = $row[0]; //时间
                    $new_row[] = $row[82]; //速度
                    $new_row[] = $row[3]; //加速度
                    $new_row[] = "sud_brake"; //事件类型
                    $event_type = 0;
                    $new_row[] = $event_id; // event_id
                    $new_row[] = ""; //seq 每次事件内部数据点编号
                    $new_row[] = ""; //每对停车事件编号
                    $new_row[] = $trip_event_id;
                    $new_row[] = $driver_id;
                    $new_row[] = $trip_id;
                    $sud_brake_count += 1;

                    $tmp_event->time = $row[0];
                    $tmp_event->driver_id = $driver_id;
                    $tmp_event->trip_id = $trip_id;
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
            if (($row[82] != "") & ($row[82] != " ")) {
                //启动事件

                if (((float)$row[82] != 0) & ($last_speed == 0)) {
                    if($is_ini_start){
                        $is_ini_start = false;

                    }else{
                        $s_s_num += 1;
                        if (empty($new_row)) {
                            $new_row[] = $row[0]; //时间
                            $new_row[] = $row[82]; //速度
                            $new_row[] = $row[3]; //加速度
                            $new_row[] = "start"; //事件类型
                            $event_type = 1;
                            $new_row[] = $event_id; // event_id
                            $new_row[] = 1; //seq 每次事件内部数据点编号
                            $new_row[] = $s_s_num; //每对停车事件编号
                            $new_row[] = $trip_event_id;
                            $new_row[] = $driver_id;
                            $new_row[] = $trip_id;

                            $tmp_event->time = $row[0];
                            $tmp_event->driver_id = $driver_id;
                            $tmp_event->trip_id = $trip_id;
                            $tmp_events[] = $tmp_event;
                        } else {
                            $new_row[3] = $new_row[3] . ",start";
                        }
                    }


                }
                //停车事件
               if (((float)$row[82] == 0) & ($last_speed != 0)) {
                    if (empty($new_row)) {
                        $new_row[] = $row[0]; //时间
                        $new_row[] = $row[82]; //速度
                        $new_row[] = $row[3]; //加速度
                        $new_row[] = "stop"; //事件类型
                        $event_type = 2;
                        $new_row[] = $event_id; // event_id
                        $new_row[] = 50; //seq 每次事件内部数据点编号
                        $new_row[] = $s_s_num; //每对停车事件编号
                        $new_row[] = $trip_event_id;
                        $new_row[] = $driver_id;
                        $new_row[] = $trip_id;

                        $tmp_event->time = $row[0];
                        $tmp_event->driver_id = $driver_id;
                        $tmp_event->trip_id = $trip_id;
                        $tmp_events[] = $tmp_event;
                    } else {
                        $new_row[3] = $new_row[3] . ",stop";
                    }
                }
                $last_speed = (float)$row[82];
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
            echo("<script>console.log('".$new_row[0]."');</script>");
            $new_rows_ss[] = $new_row;
            $tmp_begin = $row_num + 1;
            for ($tmp_begin, $i; ($tmp_begin < $row_num + 50) && ($tmp_begin < count($info_list, 0)); $tmp_begin++, $i++) {
                $new_row_start = array();
                $new_row_start[] = $info_list[$tmp_begin][0];//时间
                $new_row_start[] = $info_list[$tmp_begin][82];//速度
                $new_row_start[] = $info_list[$tmp_begin][3];//加速度
                $new_row_start[] = "start"; //事件类型
                $new_row_start[] = $event_id; //event_id
                $new_row_start[] = $i;//seq 每次事件内部数据点编号
                $new_row_start[] = $s_s_num; //每对停车事件编号
                $new_row_start[] = $trip_event_id;
                $new_row_start[] = $driver_id;
                $new_row_start[] = $trip_id;
                $new_rows_ss[]= $new_row_start;
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
                $new_row_stop[] = $info_list[$tmp_begin + 1][82];//速度
                $new_row_stop[] = $info_list[$tmp_begin + 1][3];//加速度
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