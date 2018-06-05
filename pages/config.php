<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Config</title>
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

</head>
<body onload="load()">
<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/16
 * Time: 13:22
 */
class config
{
    public $forward_go = 5;
    public $backward_go = 1;
    public $forward_stop = 1;
    public $backward_stop = 5;
    public $forward_hard_brake = 5;
    public $backward_hard_brake = 5;
    public $forward_hard_swerve = 5;
    public $backward_hard_swerve = 5;
    public $backward_lane_change = 5;
    public $forward_lane_change = 5;
    public $time_standard_car_following = 100;
}

?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <div class="row"  style="margin: 0 auto;">
        <div class="col-lg-12">
            <h1 class="page-header">Config</h1>
        </div>
        <!-- /.col-lg-12 -->
    </div>
    <div class="row" style="margin: 0 auto;">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>forward_go (s)</label>
                                <input class="form-control" type="text" id="forward_go" name="forward_go">
                            </div>
                            <div class="form-group">
                                <label>backward_go (s)</label>
                                <input class="form-control" type="text" id="backward_go" name="backward_go">
                            </div>
                            <div class="form-group">
                                <label>forward_stop (s)</label>
                                <input class="form-control" type="text" id="forward_stop" name="forward_stop">
                            </div>
                            <div class="form-group">
                                <label>backward_stop (s)</label>
                                <input class="form-control" type="text" id="backward_stop" name="backward_stop">
                            </div>
                            <div class="form-group">
                                <label>forward_hard_brake (s)</label>
                                <input class="form-control" type="text" id="forward_hard_brake"
                                       name="forward_hard_brake">
                            </div>
                            <div class="form-group">
                                <label>backward_hard_brake (s)</label>
                                <input class="form-control" type="text" id="backward_hard_brake"
                                       name="backward_hard_brake">
                            </div>
                            <div class="form-group">
                                <label>forward_hard_swerve (s)</label>
                                <input class="form-control" type="text" id="forward_hard_swerve"
                                       name="forward_hard_swerve">
                            </div>
                            <div class="form-group">
                                <label>backward_hard_swerve (s)</label>
                                <input class="form-control" type="text" id="backward_hard_swerve"
                                       name="backward_hard_swerve">
                            </div>
                            <div class="form-group">
                                <label>forward_lane_change (s)</label>
                                <input class="form-control" type="text" id="forward_lane_change"
                                       name="forward_lane_change">
                            </div>
                            <div class="form-group">
                                <label>backward_lane_change (s)</label>
                                <input class="form-control" type="text" id="backward_lane_change"
                                       name="backward_lane_change">
                            </div>
                            <div class="form-group">
                                <label>time_standard_car_following (s)</label>
                                <input class="form-control" type="text" id="time_std_following"
                                       name="time_standard_car_following">
                            </div>
                            <input type="submit" class="btn btn-default" name="xiugai" value="Confirm">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php

if (isset($_POST["xiugai"])) {
    if ($_POST["xiugai"] == "Confirm") {
        modConfig();
    }
}
function modConfig()
{
    $mod_config = new config();
    if (isset($_POST["forward_go"])) {
        $mod_config->forward_go = $_POST["forward_go"];
    }
    if (isset($_POST["backward_go"])) {
        $mod_config->backward_go = $_POST["backward_go"];
    }
    if (isset($_POST["forward_stop"])) {
        $mod_config->forward_stop = $_POST["forward_stop"];
    }
    if (isset($_POST["backward_stop"])) {
        $mod_config->backward_stop = $_POST["backward_stop"];
    }
    if (isset($_POST["forward_hard_brake"])) {
        $mod_config->forward_hard_brake = $_POST["forward_hard_brake"];
    }
    if (isset($_POST["backward_hard_brake"])) {
        $mod_config->backward_hard_brake = $_POST["backward_hard_brake"];;
    }
    if (isset($_POST["forward_hard_swerve"])) {
        $mod_config->forward_hard_swerve = $_POST["forward_hard_swerve"];
    }
    if (isset($_POST["backward_hard_swerve"])) {
        $mod_config->backward_hard_swerve = $_POST["backward_hard_swerve"];
    }
    if (isset($_POST["backward_lane_change"])) {
        $mod_config->backward_lane_change = $_POST["backward_lane_change"];
    }
    if (isset($_POST["forward_lane_change"])) {
        $mod_config->forward_lane_change = $_POST["forward_lane_change"];
    }
    if (isset($_POST["time_standard_car_following"])) {
        $mod_config->time_standard_car_following = $_POST["time_standard_car_following"] *10;
    }
    $mod_json_str = json_encode($mod_config);

    $config_file = fopen("../Config.json", "w") or die("Unable to open file!");
    fwrite($config_file, $mod_json_str);
    fclose($config_file);
}
$config;
load_config();
function load_config()
{
    global $config;
    $config = new config();
    $config_file = fopen("../Config.json", "r") or die("Unable to open file!");
    $json_str = fread($config_file, filesize("../Config.json"));
    fclose($config_file);
    $config = json_decode($json_str);
}

?>

</body>
<script type="application/javascript">
    function load() {
        document.getElementById("forward_go").setAttribute("value", <?php echo $config->forward_go;?>);
        document.getElementById("backward_go").setAttribute("value", <?php echo $config->backward_go;?>);
        document.getElementById("forward_stop").setAttribute("value", <?php echo $config->forward_stop;?>);
        document.getElementById("backward_stop").setAttribute("value", <?php echo $config->backward_stop;?>);
        document.getElementById("forward_hard_brake").setAttribute("value", <?php echo $config->forward_hard_brake;?>);
        document.getElementById("backward_hard_brake").setAttribute("value", <?php echo $config->backward_hard_brake;?>);
        document.getElementById("forward_hard_swerve").setAttribute("value", <?php echo $config->forward_hard_swerve;?>);
        document.getElementById("backward_hard_swerve").setAttribute("value", <?php echo $config->backward_hard_swerve;?>);
        document.getElementById("forward_lane_change").setAttribute("value", <?php echo $config->forward_lane_change;?>);
        document.getElementById("backward_lane_change").setAttribute("value", <?php echo $config->backward_lane_change;?>);
        document.getElementById("time_std_following").setAttribute("value", <?php echo $config->time_standard_car_following/10;?>);
    }
</script>

</html>
