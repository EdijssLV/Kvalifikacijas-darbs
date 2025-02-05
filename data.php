<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $db = new SQLite3('/var/www/mysite/database/kabinets.db', SQLITE3_OPEN_READONLY);

    $dataPoints = [];
    $historyData = [];

    $category = $_GET['category'] ?? '';

    if ($category) {
        $query = "
            SELECT Store, ROUND(AVG(PricePerLiter), 2) AS avg_price
            FROM Kabinets
            WHERE category = :category
            GROUP BY store
            ORDER BY avg_price ASC;
        ";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':category', $category, SQLITE3_TEXT);
        $results = $stmt->execute();

        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $dataPoints[] = [
                "label" => $row['Store'],
                "y" => (float)$row['avg_price']
            ];
        }
    }

    $query = "SELECT category, avg_price, timestamp FROM categories ORDER BY timestamp ASC";
    $results = $db->query($query);

    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $cat = $row['category'];
        $avg_price = round((float)$row['avg_price'], 2);
        $timestamp = strtotime($row['timestamp']) * 1000;

        if (!isset($historyData[$cat])) {
            $historyData[$cat] = [];
        }
        $historyData[$cat][] = ["x" => $timestamp, "y" => $avg_price];
    }

    function button($db, $column) {
        $html = '';
        try {
            $buttonQuery = "
                SELECT DISTINCT $column FROM Kabinets WHERE $column IS NOT NULL
                UNION
                SELECT DISTINCT $column FROM nemainigs WHERE $column IS NOT NULL
                ORDER BY $column ASC
            ";
            $results = $db->query($buttonQuery);

            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                $value = htmlspecialchars($row[$column], ENT_QUOTES, 'UTF-8');
                $html .= "<button onclick=loadChart('$value') class='button'>$value</button>\n";
            }
        } catch (Exception $e) {
            $html .= "<p>Error generating buttons: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
        }
        return $html;
    }

    $buttonHTML = button($db, 'Category');

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    $dataPoints = [];
    $historyData = [];
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kabinets</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    <style>
        .container {
            display: flex;
            align-items: center;
        }
        .container > div {
            margin-right: 10px; /* Optional: Add some space between the divs */
        }
    </style>
    <script>
        function loadChart(category) {
            window.location.href = "?category=" + category;
        }

        window.onload = function () {
            var storeData = <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>;
            var barChart = new CanvasJS.Chart("barChartContainer", {
                animationEnabled: true,
                theme: "light2",
                title: {
                    text: "Vidējā cena par litru veikalos" + (new URLSearchParams(window.location.search).get("category") ? " - " + new URLSearchParams(window.location.search).get("category") : "")
                },
                axisY: {
                    title: "Vidējā cena (€)",
                    minimum: 0,
                    suffix: " €"
                },
                data: [{
                    type: "column",
                    dataPoints: storeData,
                    yValueFormatString: "#.## €",
                }]
            });
            barChart.render();

            var historyData = <?php echo json_encode($historyData, JSON_NUMERIC_CHECK); ?>;
            var lineSeries = [];

            for (var category in historyData) {
                historyData[category].sort((a, b) => a.x - b.x);

                lineSeries.push({
                    type: "line",
                    showInLegend: false,
                    name: category,
                    xValueType: "dateTime",
                    dataPoints: historyData[category]
                });
            }

            var lineChart = new CanvasJS.Chart("lineChartContainer", {
                animationEnabled: true,
                theme: "light2",
                title: { text: "Vidējā cena par litru pēc kategorijas laika gaitā" },
                axisX: { title: "Laiks", valueFormatString: "YYYY-MM-DD", labelAngle: -90 },
                axisY: { title: "Vidējā cena (€)", minimum: 0 },
                toolTip: { shared: true, contentFormatter: function(e) {
                    var content = "<strong>" + new Date(e.entries[0].dataPoint.x).toISOString().split("T")[0] + "</strong><br/>";
                    e.entries.sort((a, b) => b.dataPoint.y - a.dataPoint.y);
                    e.entries.forEach(entry => {
                        content += "<span style='color:" + entry.dataSeries.color + "'>● </span>" + entry.dataSeries.name + ": " + entry.dataPoint.y + " €<br/>";
                    });
                    return content;
                }},
                data: lineSeries
            });
            lineChart.render();
        };
    </script>
</head>
<body background="http://st.depositphotos.com/1987851/1904/i/450/depositphotos_19041043-Old-wallpaper-seamless-texture.jpg">
    <div class="container">
        <div id="barChartContainer" style="height: 370px; width: 100%;"></div>
        <div class="btn-group"><?php echo $buttonHTML; ?></div>
    </div>
    <div id="lineChartContainer" style="height: 370px; width: 100%; margin-top: 30px;"></div>
</body>
</html>
