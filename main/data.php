<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $db = new SQLite3('/var/www/mysite/database/kabinets.db', SQLITE3_OPEN_READONLY);

    $dataPoints = [];
    $historyData = [];
    $categories = [];

    // Fetch all categories for checkboxes
    $categoryQuery = "SELECT DISTINCT category FROM categories ORDER BY category ASC";
    $categoryResults = $db->query($categoryQuery);
    while ($row = $categoryResults->fetchArray(SQLITE3_ASSOC)) {
        $categories[] = $row['category'];
    }

    // Get selected categories from URL (default: empty)
    $selectedCategories = isset($_GET['categories']) ? explode(',', urldecode($_GET['categories'])) : [];

    // Fetch historical price data for line chart
    if (!empty($selectedCategories)) {
        foreach ($selectedCategories as $category) {
            $query = "SELECT avg_price, timestamp FROM categories WHERE category = :category ORDER BY timestamp ASC";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':category', $category, SQLITE3_TEXT);
            $results = $stmt->execute();
            
            $points = [];
            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                $points[] = [
                    "x" => strtotime($row['timestamp']) * 1000,
                    "y" => (float)$row['avg_price']
                ];
            }
            if (!empty($points)) {
                $historyData[$category] = $points;
            }
        }
    }

    // Fetch store prices for bar chart
    $category = isset($_GET['category']) ? urldecode($_GET['category']) : '';

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

    // Generate category buttons for the bar chart
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
                $encodedValue = urlencode($row[$column]); // Properly encode it for URLs
                $html .= "<button onclick=\"loadBarChart('$encodedValue')\" class='button'>$value</button>\n";
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
    $categories = [];
}
?>
<!DOCTYPE HTML>
<html lang="lv">
<?php include 'head.php'; ?>
<body background="http://st.depositphotos.com/1987851/1904/i/450/depositphotos_19041043-Old-wallpaper-seamless-texture.jpg">
    <div class="container">
        <div class="chart-area">
            <div id="barChartContainer"></div>
            <div id="lineChartContainer"></div>
        </div>
        <div class="action-area">
            <div class="button-area">
                <div class="btn-group"><?php echo $buttonHTML; ?></div>
            </div>
            <div class="checkbox-area">
                <?php foreach ($categories as $category): ?>
                    <label>
                        <input type="checkbox" name="category" value="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>" 
                        <?php echo in_array($category, $selectedCategories) ? 'checked' : ''; ?>
                        onchange="updateLineChart()">
                        <?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    <script>
        function loadBarChart(category) {
            window.location.href = "?category=" + category;
        }

        function updateLineChart() {
            var selectedCategories = [];
            document.querySelectorAll('input[name="category"]:checked').forEach((checkbox) => {
                selectedCategories.push(encodeURIComponent(checkbox.value));
            });

            var queryString = selectedCategories.length > 0 ? "?categories=" + selectedCategories.join(',') : "";
            window.location.href = queryString;
        }

        window.onload = function () {
            // Bar Chart
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

            // Line Chart
            var historyData = <?php echo json_encode($historyData, JSON_NUMERIC_CHECK); ?>;
            var lineSeries = [];

            for (var category in historyData) {
                historyData[category].sort((a, b) => a.x - b.x);
                lineSeries.push({
                    type: "line",
                    showInLegend: true,
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
                toolTip: { shared: true },
                data: lineSeries
            });
            lineChart.render();
        };
    </script>
</body>
</html>