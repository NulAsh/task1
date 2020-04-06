<?php
/**
 * Main file
 */

require_once('./dbfuncs.php');

$requestUri = explode('/', trim($_SERVER['REQUEST_URI'],'/'));
$tmp = count($requestUri);
if (($tmp == 2) && ($requestUri[0] == 'api') && ($requestUri[1] == 'valuteList')) {
    $valutes_list = db_read_valutes();
    echo json_encode($valutes_list);
} elseif (($tmp == 5) && ($requestUri[0] == 'api') && ($requestUri[1] == 'valuteExcerpt')) {
    echo json_encode(db_read_excerpt($requestUri[2], $requestUri[3], $requestUri[4]));
} else {
?><html>
<head>
<meta charset="utf-8" />
<title>Task2</title>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
</head>
<body>
    <?php
        
        if (($tmp != 1) || ($requestUri[0] != 'api')) {
    ?><h1>Error in parameters</h1>
    <?php
        } else {
            
    ?>
    <input type="text" id="daterange"/><br />
    <input type="hidden" id="datefrom"/>
    <input type="hidden" id="dateto"/>
    <style>
        .wrapper {
            display: flex;
        }
    </style>
    <div class="wrapper">
        <div id="valutelist"></div>
        <div id="results"></div>
    </div>
    <script>
        function getValuteValues(vid, dfrom, dto) {
            $.getJSON('/api/valuteExcerpt/' + vid + '/' + dfrom + '/' + dto).done(function(data) {
                htmlstr = '<table><tr><th>Дата</th><th>Номинал</th><th>Курс</th></tr>';              
                for (var i=0; i<data.length; i++) {
                    htmlstr += '<tr>';
                    htmlstr += '<td>' + data[i][2] + '</td>';
                    htmlstr += '<td>' + data[i][0] + '</td>';
                    htmlstr += '<td>' + data[i][1] + '</td>';
                    htmlstr += '</tr>';
                }
                htmlstr += '</table>';
                $('#results').html(htmlstr);
            });
        }
        function changeValEvent(vid) {
            var df = $('#datefrom').val();
            var dt = $('#dateto').val();
            if (df && dt) {
                getValuteValues(vid, df, dt);
            }
        }
        $(function() {
            $('#daterange').daterangepicker({
                "locale": {
                    "format": "YYYY-MM-DD",
                    "separator": " - ",
                    "applyLabel": "Применить",
                    "cancelLabel": "Отмена",
                    "fromLabel": "От",
                    "toLabel": "До",
                    "customRangeLabel": "Custom",
                    "weekLabel": "W",
                    "daysOfWeek": [
                        "Вс",
                        "Пн",
                        "Вт",
                        "Ср",
                        "Чт",
                        "Пт",
                        "Сб"
                    ],
                    "monthNames": [
                        "Январь",
                        "Февраль",
                        "Март",
                        "Апрель",
                        "Май",
                        "Июнь",
                        "Июль",
                        "Август",
                        "Сентябрь",
                        "Октябрь",
                        "Ноябрь",
                        "Декабрь"
                    ],
                    "firstDay": 1
                },
                opens: 'left'
            }, function(start, end, label) {
                $('#datefrom').val(start.format('YYYY-MM-DD'));
                $('#dateto').val(end.format('YYYY-MM-DD'));
                var vid = $('input[name="valuteID"]:checked').val();
                if (vid) {
                    getValuteValues(vid, $('#datefrom').val(), $('#dateto').val());
                }
            });
        });
        $.getJSON('/api/valuteList').done(function(data) {
            htmlstr = '<table>';              
            for (var i=0; i<data.length; i++) {
                htmlstr += '<tr>';
                htmlstr += '<td><input type="radio" name="valuteID" onchange="changeValEvent(this.value)" value="' + data[i][0] + '"/></td>';
                htmlstr += '<td>' + data[i][1] + '</td>';
                htmlstr += '<td>' + data[i][2] + '</td>';
                htmlstr += '<td>' + data[i][3] + '</td>';
                htmlstr += '</tr>';
            }
            htmlstr += '</table>';
            $('#valutelist').html(htmlstr);
        });
    </script>
    <?php
        }
    ?>
</body>
</html><?php
}