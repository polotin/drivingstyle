function fill_table(json_str, file_names, video_play_pre, video_play_fol) {

    data = JSON.parse(json_str);
    if (data == null) {
        alert("failed");
        return false;
    }
    var length = data.length;
    var html = "";
    for (var i = 0; i < length; i++) {
        var video_link =
            './video_play.php?' +
            'driver_id=' + data[i]['driver_id'] +
            '&trip_id=' + data[i]['trip_id'] +
            '&start=' + (parseFloat(data[i]['time']) * 0.1 - video_play_pre) +
            '&end=' + (parseFloat(data[i]['time']) * 0.1 + video_play_fol) +
            '&type=' + data[i]['type'];
        var start_time = parseFloat(data[i]['time']) * 0.1 - parseFloat(video_play_pre);
        var stop_time = parseFloat(data[i]['time']) * 0.1 + parseFloat(video_play_fol);
        html += "<tr>" +
            "<td>"+data[i]['time']+"</td>" +
            "<td>"+ data[i]['driver_id'] + "</td>" +
            "<td>" + data[i]['trip_id'] + "</td>" +
            "<td class=\"center\">" + data[i]['type'] + "</td>" +
            "<td class=\"center\"><button onclick=playMyVideo('" + file_names + "','" + data[i]['driver_id'] + "','" + data[i]['trip_id'] + "','" + start_time + "','" + stop_time + "')>Video</button></td>" +
            "</tr>";
    }
    var table_area = document.getElementById("page-wrapper1");
    var table_body = document.getElementById("table_body");
    table_area.style.visibility = "visible";
    table_body.innerHTML=html;
}