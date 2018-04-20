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
            case 'swerve':
                start_time -= parseFloat(config_data['backward_hard_swerve']);
                stop_time += parseFloat(config_data['forward_hard_swerve']);
                break;
            case 'car-following':
                stop_time = start_time + data[i]['duration'] * 0.1;
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
            "<td>" + data[i]['time'] + "</td>" +
            // "<td class=\"center\"><button onclick=playMyVideo('" + file_names + "','" + data[i]['driver_id'] + "','" + data[i]['trip_id'] + "','" + start_time + "','" + stop_time + "')>Video</button></td>" +
            "<td class=\"center\"><button onclick=playMyVideo('" + data[i]['csv_file_name'] + "','" + data[i]['driver_id'] + "','" + data[i]['trip_id'] + "','" + start_time + "','" + stop_time + "')>Video</button></td>" +
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

    // var arr = new Array();
    // arr = file_names.split(',');
    // for (index in arr) {
    //     if (arr[index].startsWith("CCHN_" + driver_id) && arr[index].endsWith(trip_id + ".csv")) {
    //         video_file_name += arr[index].replace(".csv", ".mp4");
    //     }
    // }

    var video_link = "video_play.php?file_name=" + video_file_name + "&start_time=" + start_time + "&stop_time=" + stop_time;
    // window.location.href = video_link;
    window.open(video_link);
}