<?php
/**
 * Created by PhpStorm.
 * User: Polotin
 * Date: 2018/3/29
 * Time: 20:38
 */

function is_hard_brake($accel, $threshold, $cur_time, $brake_time)
{
//    echo("<script>console.log('".$accel."');</script>");
//    echo("<script>console.log('".$threshold."');</script>");
//    echo("<script>console.log('".$time_gap."');</script>");
    $time_gap = $cur_time - $brake_time;
    if (($accel < $threshold) && ($cur_time == 0)){
        return true;
    }else if (($accel < $threshold) && ($time_gap > 100)){
        return true;
    }else if (($accel < $threshold) && ($time_gap > 100)){
        return true;
    }else return false;
}


function is_s_s()
{

}

function is_turn($prams)
{

}