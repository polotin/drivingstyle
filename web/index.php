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


<?php


if (isset($_POST["user_name"]) & isset($_POST["pwd"])) {
    $user_name = $_POST["user_name"];
    $pwd = $_POST["pwd"];
    if ($user_name == "admin" & $pwd == "admin") {
        header('Location:../home.php');
    } else {
        echo "<script type=text/javascript>login_failed()</script>";
    }
}

?>
</body>
</html>
