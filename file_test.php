<?php
/**
 * Created by PhpStorm.
 * User: Polotin
 * Date: 2018/3/29
 * Time: 18:36
 */

$file = fopen("C:/Users/Polotin/Desktop/test.csv", "r");
$info_list = array();  //存放所有行车信息
while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容
    $info_list[] = $data;
}
print_r($info_list);
fclose($file);