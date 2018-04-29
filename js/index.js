function hide_table() {
    var table_area = document.getElementById("page-wrapper1");
    var table_body = document.getElementById("table_body");
    table_body.innerHTML = "";
    table_area.style.visibility = "invisible";
}

function fill_table(json_str, json_str_config) {
    data = JSON.parse(json_str);
    if (data == null) {
        alert("failed");
        return false;
    }

    config_data = JSON.parse(json_str_config);
    if (config_data == null) {
        alert("failed");
        return false;
    }
    var length = data.length;
    var html = "";
    for (var i = 0; i < length; i++) {
        var start_time = parseFloat(data[i]['time']) * 0.1;
        var stop_time = parseFloat(data[i]['time']) * 0.1;
        switch (data[i]['type']) {
            case 'go':
                start_time -= parseFloat(config_data['backward_go']);
                stop_time += parseFloat(config_data['forward_go']);
                break;
            case 'stop':
                start_time -= parseFloat(config_data['backward_stop']);
                stop_time += parseFloat(config_data['forward_stop']);
                break;
            case 'hard_brake':
                start_time -= parseFloat(config_data['backward_hard_brake']);
                stop_time += parseFloat(config_data['forward_hard_brake']);
                break;
            case 'hard_swerve_1':
                start_time -= parseFloat(config_data['backward_hard_swerve']);
                stop_time += parseFloat(config_data['forward_hard_swerve']);
                break;
            case 'hard_swerve_2':
                start_time -= parseFloat(config_data['backward_hard_swerve']);
                stop_time += parseFloat(config_data['forward_hard_swerve']);
                break;
            case 'hard_swerve_3':
                start_time -= parseFloat(config_data['backward_hard_swerve']);
                stop_time += parseFloat(config_data['forward_hard_swerve']);
                break;
            case 'car_following':
                stop_time = start_time + data[i]['duration'] * 0.1;
                break;
            case 'lane_change':
                start_time = start_time * 10 - 5;
                stop_time = stop_time*10 + 10;
                break;
            default:
                start_time -= 5;
                stop_time += 5;
                break;
        }

        html += "<tr>" +
            "<td>" + data[i]['driver_id'] + "</td>" +
            "<td>" + data[i]['trip_id'] + "</td>" +
            "<td>" + data[i]['event_id'] + "</td>" +
            "<td class=\"center\">" + data[i]['type'] + "</td>" +
            "<td>" + parseFloat(data[i]['time']) + "</td>" +
            "<td class=\"center\"><button class='fa fa-video-camera video_btn' onclick=playMyVideo('" + data[i]['csv_file_name'] + "','" + data[i]['driver_id'] + "','" + data[i]['trip_id'] + "','" + start_time + "','" + stop_time + "')>&nbsp;Video</button></td>" +
            "</tr>";
    }
    var table_area = document.getElementById("page-wrapper1");
    var table_body = document.getElementById("table_body");
    table_area.style.visibility = "visible";
    table_body.innerHTML = html;

    var progress_bar = document.getElementById("page");
}

function playMyVideo(csv_file_name, driver_id, trip_id, start_time, stop_time) {
    var video_file_name = csv_file_name.substring(0, csv_file_name.length-4)+"_Front.mp4";
    var video_link = "video_play.php?file_name=" + video_file_name + "&start_time=" + start_time + "&stop_time=" + stop_time;
    window.open(video_link,"newwindow","height=400, width=500, toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, status=no");
}