<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kabinets</title>
    <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

        <!-- For IE 11, Chrome, Firefox, Safari, Opera -->
        <link rel="icon" href="/images/favicon.ico" type="image/x-icon">
        <link rel="icon" href="/images/favicon-16x16.png" sizes="16x16" type="image/png">
        <link rel="icon" href="/images/favicon-32x32.png" sizes="32x32" type="image/png">
        <link rel="icon" href="/images/favicon-192x192.png" sizes="192x192" type="image/png">

        <!-- Apple iOS: Disable automatic detection and formatting of possible phone numbers -->
        <meta name="format-detection" content="telephone=no">

        <!-- Apple iOS: Add to Home Screen -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="Kabinets">

        <!-- Apple iOS: Touch Icons -->
        <link rel="apple-touch-icon" href="images/apple-touch-icon.png">

        <!-- Google Android -->
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="theme-color" content="#c6995f">
</head>
<body background="http://st.depositphotos.com/1987851/1904/i/450/depositphotos_19041043-Old-wallpaper-seamless-texture.jpg">
    <h1>Kabinets</h1>

    <button class="top-right-button" style ="border: 1px solid #111;" onclick="toggleDiv()"><span class="material-icons md-48">tune</span></button>
    <div id="myDiv" class="hidden-div">
        <h2>Filtrs</h2>
        <input type="text" id="filterInput" onkeyup="filterFirstColumn()" placeholder="Search for names.." style="width:100%">
        <div>
            <div>
                <a href="#" onclick="toggleFilters('tilpums')">
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
                <a href="#" onclick="kategorijas()">
                    <span class="material-icons md-48" id="kategorijaArrow">expand_less</span>
                    <span>Kategorija</span>
                </a>
            </div>
            <div id="kategorija" style="display: none;">
                <label><input type="checkbox" name="stiprieDzerieni" value="Alus">Alus</label>
                <br>
                <label><input type="checkbox" name="stiprieDzerieni" value="Balzāms">Balzāms</label>
                <br>
                <label><input type="checkbox" name="stiprieDzerieni" value="Degvīns">Degvīns</label>
                <br>
                <label><input type="checkbox" name="stiprieDzerieni" value="Enerģijas dzēriens">Enerģijas dzēriens</label>
                <br>
                <label><input type="checkbox" name="stiprieDzerieni" value="Gāzēts limonāde">Gāzēts limonāde</label>
                <br>
                <label><input type="checkbox" name="stiprieDzerieni" value="Liķieris">Liķieris</label>
                <br>
                <label><input type="checkbox" name="stiprieDzerieni" value="Rums">Rums</label>
                <br>
                <label><input type="checkbox" name="stiprieDzerieni" value="Sula">Sula</label>
                <br>
                <label><input type="checkbox" name="stiprieDzerieni" value="Viskijs">Viskijs</label>
            </div>
        </div>
        <div>
            <div>
                <a href="#" onclick="veikals()">
                    <span class="material-icons md-48" id="veikalsArrow">expand_less</span>
                    <span>Veikals</span>
                </a>
            </div>
            <div id="veikals" style="display: none;">
                <label><input type="checkbox" name="veikals" value="AlkOutlet"> AlkOutlet</label>
                <br>
                <label><input type="checkbox" name="veikals" value="Depo"> Depo</label>
                <br>
                <label><input type="checkbox" name="veikals" value="Dzēriens bez uzcenojuma"> Dzēriens bez uzcenojuma</label>
                <br>
                <label><input type="checkbox" name="veikals" value="Mego"> Mego</label>
                <br>
                <label><input type="checkbox" name="veikals" value="Rimi"> Rimi</label>
            </div>
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

                        $query = "SELECT * FROM Kabinets UNION SELECT * FROM nemainigs ORDER BY Name;";
                        $results = $db->query($query);

                        // Display the data
                        if ($results) {
                            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                                echo "<tr>";
                                echo "<td>" . $row['Name'] . "</td>";
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
