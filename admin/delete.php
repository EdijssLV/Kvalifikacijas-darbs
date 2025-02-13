<?php
$db = new SQLite3('/var/www/mysite/database/kabinets.db');
$id = $_GET['id'];
$db->exec("DELETE FROM Kabinets WHERE DrinkID = $id");
header("Location: admin.php");
exit;
?>
