<?php
$db = new SQLite3('/var/www/mysite/database/kabinets.db');

$id = $_GET['id'];
$row = $db->querySingle("SELECT * FROM categories WHERE id = $id", true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category = $_POST['category'];
    $avg_price = $_POST['avg_price'];

    $db->exec("UPDATE categories SET category='$category', avg_price='$avg_price', timestamp=CURRENT_TIMESTAMP WHERE id=$id");

    header("Location: admin.php");
    exit;
}
?>

<form method="post">
    Kategorija: <input type="text" name="category" value="<?= htmlspecialchars($row['category']) ?>"><br>
    Vidējā €/L: <input type="number" step="0.01" name="avg_price" value="<?= $row['avg_price'] ?>"><br>
    <button type="submit">Atjaunināt</button>
</form>