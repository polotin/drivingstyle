<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- jQuery -->
    <script src="../vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.min.js"></script>

    <!-- DataTables JavaScript -->
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
    <script src="../vendor/datatables-responsive/dataTables.responsive.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>
    <script src="../js/index.js"></script>

    <title>Events Filter</title>
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
    <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="../css/index.css" rel="stylesheet" type="text/css">
    <link href="../css/progress-bar.css" rel="stylesheet" type="text/css">
</head>
<script type=text/javascript>
    function hide_bar() {
        var pro_bar = document.getElementById("page");
        pro_bar.style.display = "none";
    }

    function show_bar() {
        var pro_bar = document.getElementById("page");
        pro_bar.style.display = "block";
        return true;
    }
</script>

<body>
<?php
include 'validate_login.php';
validate_login();
include 'process_improve.php';
?>
<div id="wrapper">
    <p id="page" onclick="hide_bar()"></p>
    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.html">Driving S</a>
        </div>
        <!-- /.navbar-header -->

        <div class="navbar-default sidebar" role="navigation">
            <div class="sidebar-nav navbar-collapse">
                <ul class="nav" id="side-menu">
                    <li class="sidebar-search">
                        <div class="input-group custom-search-form">
                            <input type="text" class="form-control" placeholder="Search...">
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button">
                                    <i class="fa fa-search"></i>
                                </button>
                            </span>
                        </div>
                        <!-- /input-group -->
                    </li>
                    <li>
                        <a href="index.html"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                    </li>

                    <li>
                        <a href="index.php"><i class="fa fa-edit fa-fw"></i> Forms</a>
                    </li>
                    <li>
                        <a href="tables.html"><i class="fa fa-table fa-fw"></i> Tables</a>
                    </li>

                    <li>
                        <a href="#"><i class="fa fa-bar-chart-o fa-fw"></i> Charts<span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li>
                                <a href="flot.html">Flot Charts</a>
                            </li>
                            <li>
                                <a href="morris.html">Morris.js Charts</a>
                            </li>
                        </ul>
                        <!-- /.nav-second-level -->
                    </li>

                    <li>
                        <a href="#"><i class="fa fa-wrench fa-fw"></i> UI Elements<span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li>
                                <a href="panels-wells.html">Panels and Wells</a>
                            </li>
                            <li>
                                <a href="buttons.html">Buttons</a>
                            </li>
                            <li>
                                <a href="notifications.html">Notifications</a>
                            </li>
                            <li>
                                <a href="typography.html">Typography</a>
                            </li>
                            <li>
                                <a href="icons.html"> Icons</a>
                            </li>
                            <li>
                                <a href="grid.html">Grid</a>
                            </li>
                        </ul>
                        <!-- /.nav-second-level -->
                    </li>
                    <li>
                        <a href="#"><i class="fa fa-sitemap fa-fw"></i> Multi-Level Dropdown<span
                                    class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li>
                                <a href="#">Second Level Item</a>
                            </li>
                            <li>
                                <a href="#">Second Level Item</a>
                            </li>
                            <li>
                                <a href="#">Third Level <span class="fa arrow"></span></a>
                                <ul class="nav nav-third-level">
                                    <li>
                                        <a href="#">Third Level Item</a>
                                    </li>
                                    <li>
                                        <a href="#">Third Level Item</a>
                                    </li>
                                    <li>
                                        <a href="#">Third Level Item</a>
                                    </li>
                                    <li>
                                        <a href="#">Third Level Item</a>
                                    </li>
                                </ul>
                                <!-- /.nav-third-level -->
                            </li>
                        </ul>
                        <!-- /.nav-second-level -->
                    </li>
                    <li>
                        <a href="#"><i class="fa fa-files-o fa-fw"></i> Sample Pages<span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li>
                                <a href="blank.html">Blank Page</a>
                            </li>
                            <li>
                                <a href="login.php">Login Page</a>
                            </li>
                        </ul>
                        <!-- /.nav-second-level -->
                    </li>
                </ul>
            </div>
            <!-- /.sidebar-collapse -->
        </div>
        <!-- /.navbar-static-side -->
    </nav>

    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Input</h1>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /.row -->
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Basic Form Elements
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return show_bar();"
                                      method="post" id="my-form">
                                    <div>
                                        <div class="form-group">
                                            <label>Trip Folder</label>
                                            <input class="form-control" type="text" placeholder="csv_folder"
                                                   name="csv_file_dir">
                                        </div>
                                        <div class="form-group" style="display: inline-block; width: 50%">
                                            <label>Driver Id</label>
                                            <input class="form-control" type="text" name="driver_id"
                                                   placeholder="driver_id">
                                        </div>
                                        <div class="form-group" style="display: inline-block; width: 49%">
                                            <label>Trip Id</label>
                                            <input class="form-control" type="text" name="trip_id"
                                                   placeholder="trip_id(optional)">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Event Types</label> &nbsp
                                        <a class="fa fa-gears" style="font-size:10px; cursor: pointer;"
                                           href="config.php" target="_blank"> Config</a><br>
                                        <input type="checkbox" name="types[]" value="start_stop">stop-and-go &nbsp
                                        <input type="checkbox" name="types[]" value="ini_start">ini-start &nbsp
                                        <input type="checkbox" name="types[]" value="final_stop">final-stop &nbsp
                                        <input type="checkbox" name="types[]" value="hard_brake">hard-brake &nbsp
                                        <input type="checkbox" name="types[]" value="turn">turn &nbsp
                                        <input type="checkbox" name="types[]" value="hard_swerve">hard-swerve &nbsp
                                        <input type="checkbox" name="types[]" value="lane_change">lane-change &nbsp
                                        <input type="checkbox" name="types[]" value="car_following">car-following &nbsp

                                    </div>

                                    <input type="submit" class="btn btn-default" name="fenxi" value="Submit">
                                    <button type="reset" class="btn btn-default">Reset</button>
                                </form>
                            </div>

                        </div>
                        <!-- /.row (nested) -->
                    </div>
                    <!-- /.panel-body -->
                </div>
                <!-- /.panel -->
            </div>

            <!-- /.col-lg-12 -->

        </div>
        <!-- /.row -->
    </div>

    <div id="page-wrapper1" style="visibility: hidden">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Tables</h1>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /.row -->
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        DataTables Advanced Tables
                    </div>
                    <!-- /.panel-heading -->
                    <div class="panel-body">
                        <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables">
                            <thead>
                            <tr>
                                <th>Driver Id</th>
                                <th>Trip Id</th>
                                <th>Event Id</th>
                                <th>Type</th>
                                <th>Time</th>
                                <th>Video</th>
                            </tr>
                            </thead>
                            <tbody id="table_body">

                            </tbody>
                        </table>
                        <!-- /.table-responsive -->
                        <div class="well">
                            <!--                            <h4>DataTables Usage Information</h4>-->
                            <!--                            <p>DataTables is a very flexible, advanced tables plugin for jQuery. In SB Admin, we are using a specialized version of DataTables built for Bootstrap 3. We have also customized the table headings to use Font Awesome icons in place of images. For complete documentation on DataTables, visit their website at <a target="_blank" href="https://datatables.net/">https://datatables.net/</a>.</p>-->
                            <a class="btn btn-default btn-lg btn-block" target="_blank" href="https://datatables.net/">View
                                Data Charts</a>
                        </div>
                    </div>
                    <!-- /.panel-body -->
                </div>
                <!-- /.panel -->
            </div>
            <!-- /.col-lg-12 -->
        </div>
    </div>
    <!-- /#page-wrapper -->


</div>

<?php
//处理
$driver_id = "";
$trip_id = "";
$types = "";
$threshold = 0;
$csv_file_dir = "";
$video_play_pre = 5;
$video_play_fol = 5;

if (isset($_POST["driver_id"])) {
    $driver_id = $_POST["driver_id"];
}
if (isset($_POST["trip_id"])) {
    $trip_id = $_POST["trip_id"];
}
if (isset($_POST["types"])) {
    $types = $_POST["types"];
}
if (isset($_POST["csv_file_dir"])) {
    $csv_file_dir = $_POST["csv_file_dir"];
}
$json_str = '';
if (isset($_POST["fenxi"])) {
    if ($_POST["fenxi"] == "Submit") {
        $json_str = process($driver_id, $trip_id, $types, $csv_file_dir);
    }
}else{
    echo "<script type=text/javascript>hide_bar();</script>";
}

$config_file = fopen("../Config.json", "r") or die("Unable to open file!");
$json_str_config = fread($config_file, filesize("../Config.json"));
fclose($config_file);
echo "<script type=text/javascript>fill_table('" . $json_str . "','" . $json_str_config . "')</script>";
?>

<script>
    $(document).ready(function () {
        hide_bar();
        $('#dataTables').DataTable({
            responsive: true
        });
    });
</script>
</body>

</html>
