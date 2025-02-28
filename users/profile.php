<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}
$db = new SQLite3('/var/www/mysite/database/kabinets.db', SQLITE3_OPEN_READONLY);

function generateCheckboxes($db, $table, $column, $inputName) {
    $html = '';
    try {
        $query = "
            SELECT DISTINCT $column FROM Kabinets WHERE $column IS NOT NULL
            UNION
            SELECT DISTINCT $column FROM nemainigs WHERE $column IS NOT NULL
            ORDER BY $column ASC
        ";
        $results = $db->query($query);
        
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $value = htmlspecialchars($row[$column], ENT_QUOTES, 'UTF-8');
            $html .= "<label><input type=\"checkbox\" name=\"$inputName\" value=\"$value\"> $value</label><br>\n";
        }
    } catch (Exception $e) {
        $html .= "<p>Error generating checkboxes: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
    }
    return $html;
}
?>
<?php include 'head.php'; ?>
<body background="http://st.depositphotos.com/1987851/1904/i/450/depositphotos_19041043-Old-wallpaper-seamless-texture.jpg">
    <h1>Kabinets</h1>
    <div id="style">
        <div class="sidebar">
            <h3>Menu</h3>
            <h2>Sveiks, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</h2>
            <a href="logout.php">Izrakstīties</a>
        </div>
        <div class="table-container">
            <table id="myTable">
                <thead>
                    <tr>
                        <th>Dzēriens</th>
                        <th>Tilpums</th>
                        <th>Cena</th>
                        <th>Veikals</th>
                        <th class="hidden-column">Kategorija</th>
                        <th>Cena/L</th>
                        <th>Darbība</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $db = new SQLite3('/var/www/mysite/database/kabinets.db');

                        if (!$db) {
                                die("Database connection failed: " . $db->lastErrorMsg());
                        }

                        $query = "SELECT * FROM Kabinets UNION SELECT *, NULL AS links FROM nemainigs ORDER BY Name;";
                        $results = $db->query($query);

                        if ($results) {
                            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                                echo "<tr>";
                                echo "<td><a href='" . $row['links'] . "' target='_blank'>" . $row['Name'] . "</a></td>";
                                echo "<td>" . $row['Volume'] . " L</td>";
                                echo "<td>" . $row['Price'] . " €</td>";
                                echo "<td>" . $row['Store'] . "</td>";
                                echo "<td class='hidden-column'>" . $row['Category'] . "</td>";
                                echo "<td>" . $row['PricePerLiter'] . " €/L</td>";
                                echo "<td><i class='fa fa-thumbs-o-up' style='font-size:24px'></i></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "No data found!";
                        }
                       $db->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="scripts.js"></script>
</body>
</html>