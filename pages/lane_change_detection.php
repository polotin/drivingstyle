<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/25
 * Time: 14:32
 */

function lane_change_detection($video_path,$csv_path, $with_video_flag){
    $result = "result";
    urlencode($result);
    unset($out);
//    $result = exec('python lane_changing_detection.py {$video_path} {$csv_path}', $out, $res);
    $result = exec('python lane_changing_detection.py {'.$video_path.'} {'.$csv_path.'}{'.$with_video_flag."}'");
    global $laneChangeEvent;
    if($result!= null && $result!='None'){
        $laneChangeEvent += $result.',';
    }else{
        $laneChangeEvent += "None";
    }
    echo "<script type=text/javascript>console.log('" . "result:" . $result . "')</script>";
}