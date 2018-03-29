<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>视频播放</title>
</head>
<body>
<?php
    $driver_id = $_GET['driver_id'];
    $trip_id = $_GET['trip_id'];
    $start = $_GET['start'];
    $type = $_GET['type'];

    $video_path = 'video/'.$driver_id.'_'.$trip_id.'.mp4';


?>
<div>
    <button onclick="playVideo()">播放</button>

    <video id="video1">
        <source src=<?php echo $video_path?> type="video/mp4">
    </video>
</div>

</body>

<script type="text/javascript">
    var myVid = document.getElementById("video1");
    myVid.addEventListener("timeupdate", timeupdate);

    var _endTime;

    function playVideo() {
        var startTime = 0;
        var endTime = 0;
        if("<?php echo $type?>" == "sud_brake" ){
            startTime = <?php echo $start?> - 5;
            endTime = <?php echo $start?> + 5;
        }else if("<?php echo $type?>" == "start"){
            startTime = <?php echo $start?> - 5;
            endTime = <?php echo $start?> + 5;
        }else if("<?php echo $type?>" == "stop"){
            startTime = <?php echo $start?> - 5;
            endTime = <?php echo $start?> + 5;
        }


        _endTime = endTime;
        myVid.currentTime = startTime;
        myVid.play();
    }

    function timeupdate() {
        var time = myVid.currentTime + "";
        var ts = time.substring(0, time.indexOf("."));
        if (ts == _endTime | ts > _endTime) {
            myVid.pause();
        }
    }
</script>

</html>