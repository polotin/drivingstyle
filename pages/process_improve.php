<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/10
 * Time: 13:05
 */

include 'event.php';
include 'trip.php';
include 'event_handler.php';
include 'process_handler.php';
$json_file_names = array();
$new_rows_ss = array();
$new_rows_hb = array();
$new_rows_hs = array();
$new_rows_turn = array();
$new_rows_car_following = array();
$new_rows_lane_change = array();
$event_id = 1;  //事件编号，所有TRIP内的事件都唯一
$tmp_events = array();  //所有事件 存为JSON
$followingEvent = array();//承装完成判定的跟车事件的编号，起始时间，持续时间
$trips = array();
$laneChangeEvent = "";//承装完成判定的跟车，单次trip
$laneChangeEvents = "" ;////承装完成判定的跟车，driver所有`trip
$new_row_brake[] = "hard_brake"; //事件类型
function process($driver_id, $trip_id, $types, $csv_file_dir)
{
    if($trip_id == ""){
        echo "<script type=text/javascript>document.cookie='isAllTrips=true'</script>";
    }else{
        echo "<script type=text/javascript>document.cookie='isAllTrips=false'</script>";
    }
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
    global $cur_time;
//    array_pop($tmp_events);
    $json_str = json_encode($tmp_events);
    //输出为CSV文件
    date_default_timezone_set("Asia/Shanghai");
    $cur_time = date("Y-m-d-H-i-s");
    $csv_header = ['System.Time_Stamp', 'Event_Type', 'Event_Id', 'Trip_Event_Id', 'Driver_Id', 'Trip_Id', 'FOT_Control.Speed', 'IMU.Accel_X', 'IMU.Accel_Y', 'IMU.Accel_Z',
        'Head_Unit.Latitude','Head_Unit.Longitude','VehicleCAN_1.BrakePedalPosition','VehicleCAN_1.Speed','VehicleNetworkBox.EngineSpeed','Road Scout.Left_Lane_Distance_To_Right_Side','Road Scout.Right_Lane_Distance_To_Left_Side','Road Scout.Lane_Offset',
        'SMS.Object_ID_T0','SMS.Object_ID_T1', 'SMS.Object_ID_T2','SMS.Object_ID_T3','SMS.Object_ID_T4','SMS.Object_ID_T5','SMS.Object_ID_T6','SMS.Object_ID_T7',
        'SMS.X_Range_T0','SMS.X_Range_T1','SMS.X_Range_T2','SMS.X_Range_T3','SMS.X_Range_T4','SMS.X_Range_T5','SMS.X_Range_T6','SMS.X_Range_T7',
        'SMS.X_Velocity_T0','SMS.X_Velocity_T1','SMS.X_Velocity_T2','SMS.X_Velocity_T3','SMS.X_Velocity_T4','SMS.X_Velocity_T5','SMS.X_Velocity_T6','SMS.X_Velocity_T7',
        'SMS.Y_Range_T0','SMS.Y_Range_T1','SMS.Y_Range_T2','SMS.Y_Range_T3','SMS.Y_Range_T4','SMS.Y_Range_T5','SMS.Y_Range_T6','SMS.Y_Range_T7',
        'SMS.Y_Velocity_T0','SMS.Y_Velocity_T1','SMS.Y_Velocity_T2','SMS.Y_Velocity_T3','SMS.Y_Velocity_T4','SMS.Y_Velocity_T5','SMS.Y_Velocity_T6','SMS.Y_Velocity_T7'];
    //$content = $head.$new_rows;
    $header = implode(',', $csv_header) . PHP_EOL;

    global $new_rows_ss;
    global $new_rows_hb;
    global $new_rows_hs;
    global $new_rows_turn;
    global $new_rows_car_following;
    global $new_rows_lane_change;

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
    if(in_array("hard_swerve",$types)){
        $content = '';
        foreach ($new_rows_hs as $line) {
            $content .= implode(',', $line) . PHP_EOL;
        }
        $csv_file_name = $driver_id . "_" . $trip_id . "_" . "hard_swerve" . $cur_time . ".csv";
        $csv_file_dir = "../public/csv/" . $csv_file_name;
        $output1 = fopen($csv_file_dir, 'w') or die("can not open");
        $csv = $header . $content;
        fwrite($output1, $csv);
        fclose($output1) or die("can not close");
    }
    if(in_array("car_following",$types)){
        $content = '';
        foreach ($new_rows_car_following as $line) {
            $content .= implode(',', $line) . PHP_EOL;
        }
        $csv_file_name = $driver_id . "_" . $trip_id . "_" . "car_following" . $cur_time . ".csv";
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
    if(in_array("lane_change", $types)){
        $content = '';
        foreach ($new_rows_lane_change as $line) {
            $content .= implode(',', $line) . PHP_EOL;
        }
        $csv_file_name = $driver_id . "_" . $trip_id . "_" . "lane_change" . $cur_time . ".csv";
        $csv_file_dir = "../public/csv/" . $csv_file_name;
        $output = fopen($csv_file_dir, 'w') or die("can not open");
        $csv = $header . $content;
        fwrite($output, $csv);
        fclose($output) or die("can not close");
    }
    return $json_str;
}


