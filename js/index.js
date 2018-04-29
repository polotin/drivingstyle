function hide_table() {
    var table_area = document.getElementById("page-wrapper1");
    table_area.style.visibility = "invisible";
}

function show_events_table() {
    var events_table_panel = document.getElementById("events_table_panel");
    var stat_table_panel = document.getElementById("stat_table_panel");
    var show_events_btn = document.getElementById("show_events_btn");
    var show_stat_btn = document.getElementById("show_stat_btn");
    events_table_panel.style.display = "block";
    events_table_panel.style.visibility = "visible";
    stat_table_panel.style.display = "none";
    show_events_btn.style.color = "#B0B0B0";
    show_stat_btn.style.color = "#2352A1";
}

function show_stat_table() {
    var events_table_panel = document.getElementById("events_table_panel");
    var stat_table_panel = document.getElementById("stat_table_panel");
    var show_events_btn = document.getElementById("show_events_btn");
    var show_stat_btn = document.getElementById("show_stat_btn");
    events_table_panel.style.display = "none";
    stat_table_panel.style.display = "block";
    stat_table_panel.style.visibility = "visible";
    show_events_btn.style.color = "#2352A1";
    show_stat_btn.style.color = "#B0B0B0";
}

function fill_table(json_str, trips_json_str, json_str_config) {
    console.log(trips_json_str);
    data = JSON.parse(json_str);
    if (data == null) {
        alert("failed");
        return false;
    }

    trips_data = JSON.parse(trips_json_str);
    if (trips_data == null) {
        alert("trips_failed");
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
                stop_time = stop_time * 10 + 10;
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
            "<td class=\"center\"><button class='fa fa-video-camera video_btn' onclick=\"playMyVideo('" + data[i]['csv_file_name'] + "','" + data[i]['driver_id'] + "','" + data[i]['trip_id'] + "','" + start_time + "','" + stop_time + "'); this.style ='background-color:#DBDBDB;color:#B0B0B0;'\">&nbsp;Video</button></td>" +
            "</tr>";
    }
    var table_area = document.getElementById("page-wrapper1");
    var table_body = document.getElementById("table_body");
    table_area.style.visibility = "visible";
    table_body.innerHTML = html;

    var trips_count = trips_data.length;
    var trips_html = "";
    for (var i = 0; i < trips_count; i++) {
        trips_html += "<tr>" +
            "<td>" + trips_data[i]['driver_id'] + "</td>" +
            "<td>" + trips_data[i]['trip_id'] + "</td>" +
            "<td>" + trips_data[i]['stop_count'] + "</td>" +
            "<td>" + trips_data[i]['go_count'] + "</td>" +
            "<td>" + trips_data[i]['hb_count'] + "</td>" +
            "<td>" + trips_data[i]['turn_count'] + "</td>" +
            "<td>" + trips_data[i]['hs_count'] + "</td>" +
            "<td>" + trips_data[i]['lc_count'] + "</td>" +
            "<td>" + trips_data[i]['cf_count'] + "</td>" +
            "<td class=\"center\"><button class='fa fa-bar-chart-o chart_btn' onclick=\"playMyVideo(); this.style ='background-color:#DBDBDB;color:#B0B0B0;'\">&nbsp;Video</button></td>" +
            "</tr>";
    }

    var table_body_stat = document.getElementById("table_body_stat");
    table_body_stat.innerHTML = trips_html;
}

function playMyVideo(csv_file_name, driver_id, trip_id, start_time, stop_time) {
    var video_file_name = csv_file_name.substring(0, csv_file_name.length - 4) + "_Front.mp4";
    var video_link = "video_play.php?file_name=" + video_file_name + "&start_time=" + start_time + "&stop_time=" + stop_time;
    window.open(video_link, "newwindow", "height=400, width=500, toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, status=no");
}