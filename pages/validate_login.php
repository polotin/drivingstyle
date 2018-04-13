<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/13
 * Time: 9:20
 */
function validate_login(){
    session_start();
    if(!isset($_SESSION['id'])){
        header("Location:login.php");
        exit();
    }
}
