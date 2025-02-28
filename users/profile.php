<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="lv">
    <?php include 'head.php'; ?>
    <body background="http://st.depositphotos.com/1987851/1904/i/450/depositphotos_19041043-Old-wallpaper-seamless-texture.jpg">
        <div class="container">
            <div class="sidebar">
                <h3>Menu</h3>
                <h1>Sveiks, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</h1>
                <a href="logout.php">Izrakstīties</a>
            </div>
            <div class="table">
                <div id="table1">
                    <h1>Dzērienu tabula</h1>
                    <table>
                        <tr>
                            <th>Nosaukums</th>
                            <th>Tilpums</th>
                            <th>Cena</th>
                            <th>Veikals</th>
                            <th>Kategorija</th>
                            <th>Cena/L</th>
                        </tr>
                        <?php
                            $db = new SQLite3('/var/www/mysite/database/kabinets.db');

                            $sql = "SELECT * FROM Kabinets ORDER BY Name ASC";
                            $results = $db->query($sql);
                        ?>

                        <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['Name']) ?></td>
                            <td><?= $row['Volume'] ?> L</td>
                            <td><?= $row['Price'] ?> €</td>
                            <td><?= htmlspecialchars($row['Store']) ?></td>
                            <td><?= htmlspecialchars($row['Category']) ?></td>
                            <td><?= $row['PricePerLiter'] ?> €/L</td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>