<?php
$file = fopen("CCHN_0008_229728_46_130406_0803_00172.csv", "r");
$vtti_time = array();
$accel_X = array();
$accel_Y = array();
$speed = array();
$roadName = array();
$latitude = array();
$longitude = array();
$count = 0;
while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容
    if ($count != 0 && $data[3] != ' ' && $data[4] != ' ' && $data[82] != ' ' && $data[29] != ' ' && $data[34] != ' ') {
        array_push($vtti_time, $data[1]);
        array_push($accel_X, $data[3]);
        array_push($accel_Y, $data[4]);
        array_push($speed, $data[82]);
        array_push($roadName, $data[251]);
        array_push($latitude, $data[29]);
        array_push($longitude, $data[34]);
    }
    $count++;
}

/**
 * @param $vtti_time
 * @param $accel_X
 * @param $accel_Y
 * @param $speed
 * @param $roadName
 * @param $latitude
 * @param $longitude
 */
function detect_turn($vtti_time, $accel_X, $accel_Y, $speed, $roadName, $latitude, $longitude)
{
    $count = 0;
    $bearing = [];
    $position = 0;
    $endRoad = '';
    $startRoad = '';
    $stopFlag = 0;
    $roadFlag = 0;
    $turnID = 0;
    $ID = [];
    $turn_time = [];
    for ($i = 1; $i < sizeof($latitude); $i++) {
        if ($i < sizeof($latitude)) {
            $lat_a = $latitude[$i];
            # print("start lat:")
            $lng_a = $longitude[$i];
            # print("start lng:")
            if ($i + 1 < sizeof($latitude)) {
                $lat_b = $latitude[$i + 1];
                $lng_b = $longitude[$i + 1];
                $y = sin($lng_b - $lng_a) * cos($lat_b);
                $x = cos($lat_a) * sin($lat_b) - sin($lat_a) * cos($lat_b) * cos($lng_b - $lng_a);
                $b = atan2($y, $x);
                $b = $b * 180 / M_PI;
                $count = $count + 1;
                if ($b < 0) {
                    $b = $b + 360;
                }
                if ($b == 0) {
                    $b_length = sizeof($bearing);
                    if ($b_length != 0) {
                        $b = $bearing[$b_length - 1];
                    }
                }
                if ($b == 180) {
                    $b_length = sizeof($bearing);
                    if ($b_length != 0) {
                        $b = $bearing[$b_length - 1];
                    }
                }
                array_push($bearing, $b);
            }
        }
    }

    while ($position < sizeof($latitude) - 10) {
        if ($accel_X[$position] >= 0.1) {
            for ($m = 1; $m < 10; $m++) {
                if ($position - $m >= 0) {
                    if ($speed[$position - $m] == 0) {
                        $stopFlag = 1;
                        break;
                    }
                }
            }
            for ($n = 5; $n < 10; $n++) {
                if ($roadName[$position + $n] != '') {
                    $endRoad = $roadName[$position + $n];
                    break;
                }
            }
            for ($n = 5; $n < 10; $n++) {
                if ($position - $n >= 0) {
                    if ($roadName[$position - $n] != '') {
                        $startRoad = $roadName[$position - $n];
                        break;
                    }
                }
            }
            if ($endRoad != '' and $startRoad != '' and $endRoad != $startRoad) {
                $roadFlag = 1;
            }

            if ($stopFlag == 0 and $roadFlag == 1) {
                $turnVTTITime = $vtti_time[$position];
                print("time:");
                print($turnVTTITime);
                array_push($turn_time, $turnVTTITime);
                print(' ');
                print("ID:");
                print($turnID);
                array_push($ID, $turnID);
                print('   ');
                $turnID++;
                $position = $position + 10;
            } else {
                $position = $position + 1;
            }
        } else {
            $position = $position + 1;
        }
        $stopFlag = 0;
        $roadFlag = 0;
        $endRoad = '';
        $startRoad = '';

    }
    return $turn_time;
}

detect_turn($vtti_time, $accel_X, $accel_Y, $speed, $roadName, $latitude, $longitude);


die;
