<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/25
 * Time: 14:32
 */

function lane_change_detection($video_path,$csv_path, $with_video_flag){
    $cmd = 'python lane_changing_detection.py '.$video_path.' '.$csv_path.' '.$with_video_flag;
    echo "<script type=text/javascript>console.log('" . "csv_path:" . $csv_path . "')</script>";
    $result = shell_exec($cmd);
    echo $result;
//    global $laneChangeEvent;
//    $laneChangeEvent += $result;
}