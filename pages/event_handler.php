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

function is_hard_swerve($accel_y, $speed, $cur_time, $swerve_time){
    if(($cur_time - $swerve_time)<100){
        return "";
    }
    if($speed>0 & $speed<40){
        $emer_degree1 = 0.075*abs($speed)+1;
        $emer_degree2 = 0.075*abs($speed)+2;
        $emer_degree3 = 6;
        if($accel_y> $emer_degree1&$accel_y<$emer_degree2){
            return "hard_swerve_1";
        }else if($accel_y> $emer_degree2&$accel_y<$emer_degree3){
            return "hard_swerve_2";
        }else if($accel_y>$emer_degree3){
            return "hard_swerve_3";
        }
    }else if($speed>40 & $speed<80){
        $emer_degree1 = 4;
        $emer_degree2 = 5;
        $emer_degree3 = 6;
        if($accel_y> $emer_degree1&$accel_y<$emer_degree2){
            return "hard_swerve_1";
        }else if($accel_y> $emer_degree2&$accel_y<$emer_degree3){
            return "hard_swerve_2";
        }else if($accel_y>$emer_degree3){
            return "hard_swerve_3";
        }
    }else if($speed>80 & $speed<100){
        $emer_degree1 = -0.1*abs($speed)+12;
        $emer_degree2 = -0.1*abs($speed)+13;
        $emer_degree3 = 6;
        if($accel_y> $emer_degree1&$accel_y<$emer_degree2){
            return "hard_swerve_1";
        }else if($accel_y> $emer_degree2&$accel_y<$emer_degree3){
            return "hard_swerve_2";
        }else if($accel_y>$emer_degree3){
            return "hard_swerve_3";
        }
    }
    return "";
}