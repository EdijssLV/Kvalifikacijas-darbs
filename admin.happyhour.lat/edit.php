<?php
$db = new SQLite3('/var/www/mysite/database/kabinets.db');

$id = $_GET['id'];
$row = $db->querySingle("SELECT * FROM Kabinets WHERE DrinkID = $id", true);

// Fetch unique stores
$storeResults = $db->query("SELECT DISTINCT Store FROM Kabinets ORDER BY Store ASC");
$stores = [];
while ($storeRow = $storeResults->fetchArray(SQLITE3_ASSOC)) {
    $stores[] = $storeRow['Store'];
}

// Fetch unique categories
$categoryResults = $db->query("SELECT DISTINCT Category FROM Kabinets ORDER BY Category ASC");
$categories = [];
while ($categoryRow = $categoryResults->fetchArray(SQLITE3_ASSOC)) {
    $categories[] = $categoryRow['Category'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $volume = $_POST['volume'];
    $price = $_POST['price'];
    $store = $_POST['store'];
    $category = $_POST['category'];
    $link = $_POST['link'];
    $pricePerLiter = ($volume > 0) ? ($price / $volume) : 0; // Avoid division by zero

    $db->exec("UPDATE Kabinets SET 
               Name='$name', 
               Volume='$volume', 
               Price='$price', 
               Store='$store', 
               Category='$category', 
               PricePerLiter='$pricePerLiter', 
               links='$link' 
               WHERE DrinkID=$id");

    header("Location: admin.php");
    exit;
}
?>

<form method="post">
    Nosaukums: <input type="text" name="name" value="<?= htmlspecialchars($row['Name']) ?>"><br>
    Tilpums: <input type="number" step="0.01" name="volume" value="<?= $row['Volume'] ?>"><br>
    Cena: <input type="number" step="0.01" name="price" value="<?= $row['Price'] ?>"><br>

    Veikals:
    <select name="store">
        <?php foreach ($stores as $storeOption): ?>
            <option value="<?= htmlspecialchars($storeOption) ?>" <?= ($row['Store'] == $storeOption) ? 'selected' : '' ?>>
                <?= htmlspecialchars($storeOption) ?>
            </option>
        <?php endforeach; ?>
    </select><br>

    Kategorija:
    <select name="category">
        <?php foreach ($categories as $categoryOption): ?>
            <option value="<?= htmlspecialchars($categoryOption) ?>" <?= ($row['Category'] == $categoryOption) ? 'selected' : '' ?>>
                <?= htmlspecialchars($categoryOption) ?>
            </option>
        <?php endforeach; ?>
    </select><br>

    Saite: <input type="url" name="link" value="<?= htmlspecialchars($row['links']) ?>"><br>
    
    <button type="submit">Atjaunināt</button>
</form>
