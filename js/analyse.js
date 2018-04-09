function play_result(json_str, video_play_pre, video_play_fol) {
        console.log(video_play_pre);
        console.log(video_play_fol);
    var showBox = $('.box');

    // $.getJSON(file_name, function (data) {
    var data;
    console.log(json_str);
    data = JSON.parse(json_str);
        if (data == null) {
            alert("failed");
            return false;
        }

        var length = data.length, pageIndex;
        var html =
            "<table border='1'>" +
            "<tr>\n" +
            "<th>Time</th>\n" +
            "<th>Driver_Id</th>\n" +
            "<th>Trip_Id</th>\n" +
            "<th>Type</th>\n" +
            "<th>Video</th>\n" +
            "</tr>";
        for (var i = 0; i < 10 & i < length; i++) {

            // var video_link = './video/' + data[i]['driver_id'] + '_' + data[i]['trip_id'] + '.mp4';
            var video_link =
                './video_play.php?' +
                'driver_id=' + data[i]['driver_id'] +
                '&trip_id=' + data[i]['trip_id'] +
                '&start=' + (parseFloat(data[i]['time']) * 0.1 - video_play_pre) +
                '&end=' + (parseFloat(data[i]['time']) * 0.1 + video_play_fol) +
                '&type=' + data[i]['type'];

            var start_time = parseFloat(data[i]['time']) * 0.1 - parseFloat(video_play_pre);
            var stop_time = parseFloat(data[i]['time']) * 0.1 + parseFloat(video_play_fol);
            html += "<tr>\n" +
                "<td>" + data[i]['time'] + "</td>\n" +
                "<td>" + data[i]['driver_id'] + "</td>\n" +
                "<td>" + data[i]['trip_id'] + "</td>\n" +
                "<td>" + data[i]['type'] + "</td>\n" +
                // "<td><a href='" + video_link + "'" + "target=\"_blank\">Video" + "" + "</a></td>\n" +
                "<td><button onclick=playMyVideo('"+data[i]['trip_id']+"','"+start_time+"','"+stop_time+"')>Video</button></td>\n" +
                "</tr>";
        }
        html += "</table>";
        showBox.html(html);

        $(".M-box").pagination({
            pageCount: length,
            jump: true,
            coping: true,
            homePage: '首页',
            endPage: '末页',
            preContent: '上页',
            nextContent: '下页',
            callback: function (index) {
                pageIndex = index.getCurrent() - 1;
                var html1 =
                    "<table border='1'>" +
                    "<tr>\n" +
                    "<th>Time</th>\n" +
                    "<th>Driver_Id</th>\n" +
                    "<th>Trip_Id</th>\n" +
                    "<th>Type</th>\n" +
                    "<th>Video</th>\n" +
                    "</tr>";
                for (var i = 0; i < 10 & pageIndex * 10 + i < length; i++) {
                    // var video_link = './video/' + data[pageIndex * 10 + i]['driver_id'] + '_' + data[pageIndex * 10 + i]['trip_id'] + '.mp4';
                    var video_link =
                        './video_play.php?' +
                        'driver_id=' + data[pageIndex * 10 + i]['driver_id'] +
                        '&trip_id=' + data[pageIndex * 10 + i]['trip_id'] +
                        '&start=' + (parseFloat(data[i]['time']) * 0.1 - video_play_pre) +
                        '&end=' + (parseFloat(data[i]['time']) * 0.1 + video_play_fol) +
                        '&type=' + data[pageIndex * 10 + i]['type'];
                    var start_time = parseFloat(data[i]['time']) * 0.1 - parseFloat(video_play_pre);
                    var stop_time = parseFloat(data[i]['time']) * 0.1 + parseFloat(video_play_fol);
                    html1 += "<tr>\n" +
                        "<td>" + data[pageIndex * 10 + i]['time'] + "</td>\n" +
                        "<td>" + data[pageIndex * 10 + i]['driver_id'] + "</td>\n" +
                        "<td>" + data[pageIndex * 10 + i]['trip_id'] + "</td>\n" +
                        "<td>" + data[pageIndex * 10 + i]['type'] + "</td>\n" +
                        // "<td><a href='" + video_link + "'" + "target=\"_blank\">Video" + "" + "</a></td>\n" +
                        "<td><button onclick=playMyVideo(\""+ data[i]['driver_id']+"','"+data[i]['trip_id']+"','"+start_time+"','"+stop_time+"')>Video</button></td>\n" +
                        "</tr>";
                }
                html1 += "</table>";
                showBox.html(html1);
            }
        }
    // }
    );
}

function playMyVideo(driver_id, trip_id, start_time, stop_time){
    console.log(start_time);
    console.log(stop_time);
    var video_dir="video/CCHN_"+driver_id+"_229729_46_130426_1154_"+trip_id+".mp4";
    // if(video_file_dir != ""){
    //     video_dir = video_file_dir+"/CCHN_"+driver_id+"_229729_46_130426_1154_"+trip_id+".mp4";
    // }else
    // {
    //     video_dir = "video/CCHN_"+driver_id+"_229729_46_130426_1154_"+trip_id+".mp4";
    // }
    // var html ="    <video id=\"video1\">\n" +
    //     "        <source src="+video_dir+" type=\"video/mp4\">\n" +
    //     "    </video>";
    // $(".myvideo").html(html);

    var myVid = document.getElementById("video1");
    var videoSrc = document.getElementById("videosrc");
    videoSrc.src=video_dir;
    myVid.load();
    myVid.addEventListener("timeupdate", timeupdate);
    myVid.onloadend=setTimeout(function () {
        playVideo();
    }, 150);
    var _endTime;
    function playVideo() {
        myVid.pause();
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

        _endTime = parseInt(stop_time);
        myVid.currentTime = parseInt(start_time);
        myVid.play();
    }

    function timeupdate() {
        var time = myVid.currentTime + "";
        var ts = time.substring(0, time.indexOf("."));
        if (ts == _endTime | ts > _endTime) {
            myVid.pause();
        }
    }

}