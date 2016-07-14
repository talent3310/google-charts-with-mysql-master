<?php
/**
 * Database connection details
 * Stored in a seperate file called db.php and included
 *
 * Fake example details for reference
 *
 * $db_host = 'localhost';
 * $db_database = 'bigcompanyinc';
 * $db_user = 'corporate_stooge';
 * $db_password = 'password1';
 */
include('../../inc/db.php');

$db = mysql_connect($db_host, $db_user, $db_password);
mysql_select_db($db_database);

// Collect prices
$current_year_prices = getAveragePrices('2013');
$previous_year_prices = getAveragePrices('2012');

// Store graph data
$graphData = buildPriceArray($current_year_prices, $previous_year_prices);

/**
 * [getAveragePrices : Grabs data from db]
 * @param  [int] $year
 * @return [array]
 */
function getAveragePrices($year)
{
    $sqlAverageCheck = "SELECT week, price FROM average_weekly_prices WHERE year = '$year'";
    $sqlAverageResult = mysql_query($sqlAverageCheck);

    while ($row = mysql_fetch_assoc($sqlAverageResult)) {
        $averageResult[] = $row;
    }

    return $averageResult;
}

/**
 * [buildPriceArray : Formats data for api]
 * @param  [array] $current
 * @param  [array] $previous
 * @return [string]
 */
function buildPriceArray($current, $previous)
{
    $maxWeek = 51;
    $output = "['Week', '2012', '2013'], ";

    // The data needs to be in a format ['string', int, int]
    for ($i = 0; $i < $maxWeek; $i++) {
        $output .= "['" . ($i + 1) . "', ";
        $output .= $previous[$i]['price'] . ", ";
        // Check to see if current price is empty
        if (!empty($current[$i]['price'])) {
            $output .= $current[$i]['price'];
        } else {
            $output .= 'null';
        }
        // On the final count do not add a comma
        if ($i !== ($maxWeek) - 1) {
            $output .= "],\n";
        } else {
            $output .= "]\n";
        }
    };

    return $output;
}
?>
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Google Graphs API</title>

    <meta name="viewport" content="width=device-width">

    <style type="text/css">
        #chart {
            height: 400px;
            width: 100%;
        }
        #siteWrapper {
            padding: 2em;
        }
        h1 {
            font: bold 2em/1.5 sans-serif;
        }
    </style>

</head>

<body>

    <div id="siteWrapper">

        <div class="container">
            <h1>Comparison of weekly widget prices</h1>
            <div id="chart"></div>
        </div>

    </div>

    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
        google.load("visualization", "1", {packages:["corechart"]});
        google.setOnLoadCallback(drawChart);
        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                <?php echo $graphData ?>
            ]);

            var options = {
                title: 'Weekly Widget Prices',
                fontSize: 11,
                series: {
                    0:{color: 'red', visibleInLegend: true, pointSize: 3, lineWidth: 1},
                    1:{color: 'blue', visibleInLegend: true, pointSize: 5, lineWidth: 3}
                },
                hAxis: {title: 'Weeks of the year', titleTextStyle:{color: '#03619D'}},
                vAxis: {title: 'GBP', titleTextStyle:{color: '#03619D'}}
            };

            var chart = new google.visualization.LineChart(document.getElementById('chart'));
            chart.draw(data, options);
        }
    </script>
</body>
</html>