<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>视频播放</title>
</head>
<body>
<?php
    $file_name = $_GET['file_name'];
    $start_time = $_GET['start_time'];
    $stop_time = $_GET['stop_time'];


?>
<div>
    <video id="video1">
        <source src=<?php echo "../video/".$file_name?> type="video/mp4">
    </video>
</div>

</body>

<script type="text/javascript">
    var myVid = document.getElementById("video1");
    myVid.addEventListener("timeupdate", timeupdate);
    myVid.onloadend = setTimeout(function () {
        playVideo();
    }, 150);
    var _endTime;

    function playVideo() {
        var startTime = 0;
        var endTime = 0;
        startTime = <?php echo $start_time?>;
        endTime = <?php echo $stop_time?>;
        //
        //if("<?php //echo $type?>//" == "sud_brake" ){
        //    startTime = <?php //echo $start?>// - 5;
        //    endTime = <?php //echo $start?>// + 5;
        //}else if("<?php //echo $type?>//" == "start"){
        //    startTime = <?php //echo $start?>// - 5;
        //    endTime = <?php //echo $start?>// + 5;
        //}else if("<?php //echo $type?>//" == "stop"){
        //    startTime = <?php //echo $start?>// - 5;
        //    endTime = <?php //echo $start?>// + 5;
        //}


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