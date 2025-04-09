<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}
$user_id = $_SESSION["user_id"];
$db = new SQLite3('/var/www/mysite/database/kabinets.db');

// Function to generate a unique drink key
function generateDrinkKey($name, $volume, $store) {
    return hash("sha256", strtolower(trim($name)) . $volume . $store);
}

// Fetch user's favorite drinks
$fav_query = $db->prepare("SELECT drink_key FROM favorites WHERE user_id = :user_id");
$fav_query->bindValue(":user_id", $user_id, SQLITE3_INTEGER);
$fav_results = $fav_query->execute();

$fav_drinks = [];
while ($fav_row = $fav_results->fetchArray(SQLITE3_ASSOC)) {
    $fav_drinks[$fav_row['drink_key']] = true;
}

// WEEKLY WIPE: Here you would reset the prices and insert the new prices into the history
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wipe'])) {
    // Assuming you reset drink prices (example, setting them to a new value):
    $resetPrice = 2.50; // Example of a new price after the wipe
    $resetQuery = $db->prepare("UPDATE Kabinets SET Price = :new_price");
    $resetQuery->bindValue(":new_price", $resetPrice, SQLITE3_FLOAT);
    $resetQuery->execute();

    // Insert the new prices into the price_history table
    $insertPriceQuery = $db->prepare("
        INSERT INTO price_history (drink_key, price, recorded_at)
        SELECT drink_key, Price, CURRENT_TIMESTAMP
        FROM Kabinets
    ");
    $insertPriceQuery->execute();

    header("Location: profile.php");
    exit;
}

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
// Fetch all drinks
$query = "SELECT * FROM Kabinets UNION SELECT *, NULL AS links FROM nemainigs ORDER BY Name";
$results = $db->query($query);
?>
<?php include 'head.php'; ?>
<body background="http://st.depositphotos.com/1987851/1904/i/450/depositphotos_19041043-Old-wallpaper-seamless-texture.jpg">
    <h1>Kabinets</h1>
    <div id="style">
        <div class="sidebar">
            <h3>Menu</h3>
            <h2>Sveiks, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</h2>
            <div>
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
                        <label><input type="checkbox" name="tilpums" value="0.25 L"> 0.25 L</label>
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
            </div>
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
                        <th>Izmaiņas</th>
                        <th>Darbība</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)): ?>
                        <?php
                            $drink_key = generateDrinkKey($row['Name'], $row['Volume'], $row['Store']);

                            // Get latest and previous price
                            $price_query = $db->prepare("
                                SELECT price FROM price_history
                                WHERE drink_key = :drink_key
                                ORDER BY recorded_at DESC LIMIT 2
                            ");
                            $price_query->bindValue(":drink_key", $drink_key, SQLITE3_TEXT);
                            $price_results = $price_query->execute();

                            $prices = [];
                            while ($price_row = $price_results->fetchArray(SQLITE3_ASSOC)) {
                                $prices[] = $price_row['price'];
                            }

                            $change = "Nav izmaiņu";
                            if (count($prices) == 2) {
                                if ($prices[0] < $prices[1]) {
                                    $change = "<span style='color: green;'>↓ " . round(($prices[1] - $prices[0]), 2) . " €</span>";
                                } elseif ($prices[0] > $prices[1]) {
                                    $change = "<span style='color: red;'>↑ " . round(($prices[0] - $prices[1]), 2) . " €</span>";
                                }
                            }

                            // Check if drink is favorited
                            $is_favorited = isset($fav_drinks[$drink_key]);
                        ?>
                        <tr>
                            <td><a href="<?php echo $row['links']; ?>" target="_blank"><?php echo htmlspecialchars($row['Name']); ?></a></td>
                            <td><?php echo $row['Volume']; ?> L</td>
                            <td><?php echo $row['Price']; ?> €</td>
                            <td><?php echo $row['Store']; ?></td>
                            <td class="hidden-column"><?php echo $row['Category']; ?></td>
                            <td><?php echo $row['PricePerLiter']; ?> €/L</td>
                            <td><?php echo $change; ?></td>
                            <td>
                                <form method='post' action='favorite.php'>
                                    <input type='hidden' name='drink_key' value='<?php echo $drink_key; ?>'>
                                    <button type='submit' style='background:none; border:none; cursor:pointer;'>
                                        <i class="fa <?php echo $is_favorited ? 'fa-thumbs-up' : 'fa-thumbs-o-up'; ?>" style="font-size:24px"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="scripts.js"></script>
</body>
</html>