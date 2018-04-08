function play_result(file_name) {

    var showBox = $('.box');

    $.getJSON(file_name, function (data) {
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
                '&start=' + parseFloat(data[i]['time']) * 0.1+
                '&type=' + data[i]['type'];
            html += "<tr>\n" +
                "<td>" + data[i]['time'] + "</td>\n" +
                "<td>" + data[i]['driver_id'] + "</td>\n" +
                "<td>" + data[i]['trip_id'] + "</td>\n" +
                "<td>" + data[i]['type'] + "</td>\n" +
                "<td><a href='" + video_link + "'" + "target=\"_blank\">Video" + "" + "</a></td>\n" +
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
                        '&start=' + parseFloat(data[pageIndex * 10 + i]['time']) * 0.1+
                        '&type=' + data[pageIndex * 10 + i]['type'];
                    html1 += "<tr>\n" +
                        "<td>" + data[pageIndex * 10 + i]['time'] + "</td>\n" +
                        "<td>" + data[pageIndex * 10 + i]['driver_id'] + "</td>\n" +
                        "<td>" + data[pageIndex * 10 + i]['trip_id'] + "</td>\n" +
                        "<td>" + data[pageIndex * 10 + i]['type'] + "</td>\n" +
                        "<td><a href='" + video_link + "'" + "target=\"_blank\">Video" + "" + "</a></td>\n" +
                        "</tr>";
                }
                html1 += "</table>";
                showBox.html(html1);
            }
        })
    });


}