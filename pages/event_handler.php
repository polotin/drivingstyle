<?php
/**
 * Created by PhpStorm.
 * User: Polotin
 * Date: 2018/3/29
 * Time: 20:38
 */

function is_hard_brake($accel, $cur_time, $brake_time)
{
    $time_gap = $cur_time - $brake_time;
    if (($accel < -0.51) && ($cur_time == 0)){
        return true;
    }else if (($accel <  -0.51) && ($time_gap > 100)){
        return true;
    }else if (($accel <  -0.51) && ($time_gap > 100)){
        return true;
    }else return false;
}

function is_s_s()
{

}

function is_turn($prams)
{

}