<?php
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

$categoriesHTML = generateCheckboxes($db, 'Kabinets', 'Category', 'stiprieDzerieni');

$storesHTML = generateCheckboxes($db, 'Kabinets', 'Store', 'veikals');
?>
<!DOCTYPE html>
<html lang="lv">
<?php include 'head.php'; ?>
<body background="http://st.depositphotos.com/1987851/1904/i/450/depositphotos_19041043-Old-wallpaper-seamless-texture.jpg">
    <h1>Kabinets</h1>
    <div class = "top-right">
        <button class="top-right-button"  onclick="toggleDiv()"><span class="material-icons md-48">tune</span></button>
        <button class="top-right-button" ><a href="https://user.happyhour.lat" target='_blank'><span class="material-icons md-48">person</span></a></button>
    </div>
    <div id="myDiv" class="hidden-div">
        <h2>Filtrs</h2>
        <input type="text" id="filterInput" onkeyup="filterFirstColumn()" placeholder="Search for names.." style="width:100%">
        <div>
            <div>
                <a href="#" onclick="toggleFilters('tilpums', 'tilpumsArrow')">
                    <span class="material-icons md-48" id="tilpumsArrow">expand_less</span>
                    <span>Tilpums</span>
                </a>
            </div>
            <div id="tilpums" style="display: none;">
            <label>
                <input type="checkbox" name="tilpums" value="0.25 L"> 0.25 L</label>
                <br>
                <input type="checkbox" name="tilpums" value="0.33 L"> 0.333 L</label>
                <br>
                <label><input type="checkbox" name="tilpums" value="0.5 L"> 0.5 L</label>
                <br>
                <label><input type="checkbox" name="tilpums" value="0.7 L"> 0.7 L</label>
                <br>
                <label><input type="checkbox" name="tilpums" value="1 L"> 1 L</label>
                <br>
                <label><input type="checkbox" name="tilpums" value="1.5 L"> 1.5 L</label>
                <br>
                <label><input type="checkbox" name="tilpums" value="2 L"> 2 L</label>
            </div>
        </div>
        <div>
            <div>
                <a href="#" onclick="toggleFilters('kategorija', 'kategorijaArrow')">
                    <span class="material-icons md-48" id="kategorijaArrow">expand_less</span>
                    <span>Kategorija</span>
                </a>
            </div>
            <div id="kategorija" style="display: none;">
                <?php echo $categoriesHTML; ?>
            </div>
        </div>
        <div>
            <div>
                <a href="#" onclick="toggleFilters('veikals', 'veikalsArrow')">
                    <span class="material-icons md-48" id="veikalsArrow">expand_less</span>
                    <span>Veikals</span>
                </a>
            </div>
            <div id="veikals" style="display: none;">
                <?php echo $storesHTML; ?>
            </div>
            <p style="font-size: 12px;">* - Veikali kuriem netiek atjaunoti dati</p>
            <button onclick="window.open('data.php', '_blank')">Statistiku lapa</button>
        </div>
    </div>

    <div id="style">
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
