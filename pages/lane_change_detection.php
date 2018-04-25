<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/25
 * Time: 14:32
 */

function lane_change_detection($video_path, $csv_path){
    $result = "result";
    urlencode($result);
    unset($out);
    $result = exec('python lane_changing_detection.py {$video_path} {$csv_path}', $out, $res);
    if($result!= null && $result!='None'){
        global $laneChangeEvent;
        $laneChangeEvent[] = $result;
    }
}