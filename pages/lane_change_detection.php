<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/25
 * Time: 14:32
 */

function lane_change_detection($video_path,$csv_path, $with_video_flag){
    $cmd = 'python lane_changing_detection.py '.$video_path.' '.$csv_path.' '.$with_video_flag;
    $result = shell_exec($cmd);
    global $laneChangeEvent;
    $laneChangeEvent = $result;
}