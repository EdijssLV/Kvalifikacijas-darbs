<?php
$db = new SQLite3('/var/www/mysite/database/kabinets.db');

// Get all drink_keys with at least two price entries
$drinkKeys = $db->query("
    SELECT drink_key
    FROM price_history
    GROUP BY drink_key
    HAVING COUNT(*) >= 2
");

// For each drink_key, calculate price change
while ($row = $drinkKeys->fetchArray(SQLITE3_ASSOC)) {
    $drink_key = $row['drink_key'];

    // Get the last two price entries
    $prices = $db->query("
        SELECT price, recorded_at
        FROM price_history
        WHERE drink_key = '$drink_key'
        ORDER BY recorded_at DESC
        LIMIT 2
    ");

    $priceData = [];
    while ($p = $prices->fetchArray(SQLITE3_ASSOC)) {
        $priceData[] = $p;
    }

    if (count($priceData) === 2) {
        $newest = $priceData[0];
        $previous = $priceData[1];

        $change = round($newest['price'] - $previous['price'], 2);
        $recorded_at = $newest['recorded_at'];

        $stmt = $db->prepare("
    INSERT INTO price_history (drink_key, price, change, recorded_at)
    VALUES (:drink_key, :price, :change, :recorded_at)
    ");
    $stmt->bindValue(':drink_key', $drink_key, SQLITE3_TEXT);
    $stmt->bindValue(':price', $newest['price'], SQLITE3_FLOAT);  // <- Add this
    $stmt->bindValue(':change', $change, SQLITE3_FLOAT);
    $stmt->bindValue(':recorded_at', $newest['recorded_at'], SQLITE3_TEXT);
    $stmt->execute();
    }
}

echo "Price changes finalized.\n";
?>