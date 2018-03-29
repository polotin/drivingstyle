<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>首页</title>
    <script src="../js/index.js"></script>
</head>

<body>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" id="login_form" method="post">
    ID:<input type="text" name="user_name">&nbsp;
    PWD:<input type="password" name="pwd">
    <input type="submit" value="Log in">
</form>

<form action="../event_filter.php" method="post" id="my_form" style="visibility: hidden">
    driver_id:
    <input type="text" name="driver_id">&nbsp;
    trip_id:
    <input type="text" name="trip_id"><br/><br/>
    event_types: &nbsp;
    <input type="checkbox" name="types[]" value="start_stop">start_stop
    <input type="checkbox" name="types[]" value="hard_brake">hard_brake
    <input type="checkbox" name="types[]" value="trun">trun
    <input type="checkbox" name="types[]" value="high_speed">high_speed
    <input type="checkbox" name="types[]" value="ini_start">ini_start
    <input type="checkbox" name="types[]" value="final_stop">final_stop

    <input type="submit" value="分析">
</form>

<?php
    if(isset($_POST["user_name"]) & isset($_POST["pwd"])){
        $user_name = $_POST["user_name"];
        $pwd = $_POST["pwd"];
        if($user_name =="admin" & $pwd=="admin"){
            echo "<script type=text/javascript>login_success()</script>";
        }else{
            echo "<script type=text/javascript>login_failed()</script>";
        }
    }
?>


</body>

<script type=text/javascript>

</script>
</html>