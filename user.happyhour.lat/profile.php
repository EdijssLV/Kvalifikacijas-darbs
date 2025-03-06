<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}
$user_id = $_SESSION["user_id"];
$db = new SQLite3('/var/www/mysite/database/kabinets.db');

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

                            // Check if the drink is favorited
                            $is_favorited = isset($fav_drinks[$drink_key]);

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

                            $change = "";
                            if ($is_favorited) {
                                // Display price change info only if favorited
                                if (count($prices) == 2) {
                                    if ($prices[0] < $prices[1]) {
                                        $change = "<span style='color: green;'>↓ " . round(($prices[1] - $prices[0]), 2) . " €</span>";
                                    } elseif ($prices[0] > $prices[1]) {
                                        $change = "<span style='color: red;'>↑ " . round(($prices[0] - $prices[1]), 2) . " €</span>";
                                    } else {
                                        $change = "Nav izmaiņu";
                                    }
                                } else {
                                    $change = "Nav izmaiņu";
                                }
                            }
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