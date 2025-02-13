<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Kabinets</title>
    <link rel="icon" type="image/x-icon" href="/var/www/mysite/images/favicon.ico">
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link rel="icon" href="/var/www/mysite/images/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/var/www/mysite/images/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="icon" href="/var/www/mysite/images/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="/var/www/mysite/images/favicon-192x192.png" sizes="192x192" type="image/png">

    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Kabinets">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#c6995f">
</head>
<body background="http://st.depositphotos.com/1987851/1904/i/450/depositphotos_19041043-Old-wallpaper-seamless-texture.jpg">
    <div class="container">
        <div class="sidebar">
            <h3>Menu</h3>
            <a href="#" onclick="showDiv('table1')">Dzērienu tabula</a>
            <a href="#" onclick="showDiv('table2')">Kategoriju vēstures tabula</a>
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
                        <th>Saite</th>
                        <th>Opcijas</th>
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
                        <td><a href="<?= htmlspecialchars($row['links']) ?>" target="_blank">Saite</a></td>
                        <td>
                            <button onclick="editRow(<?= $row['DrinkID'] ?>)">Labot</button>
                            <button onclick="deleteRow(<?= $row['DrinkID'] ?>)">Dzēst</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>

                        <br>
                        <a href="add.php"><button>Pievienot Jaunu Dzērienu</button></a>

                        <script>
                            function editRow(drinkID) {
                                window.location.href = "edit.php?id=" + encodeURIComponent(drinkID);
                            }

                            function deleteRow(drinkID) {
                                if (confirm("Vai tiešām vēlies dzēst šo dzērienu?")) {
                                    window.location.href = "delete.php?id=" + encodeURIComponent(drinkID);
                                }
                            }
                        </script>
                  </table>
            </div>
            <div id="table2">
                <h1>Kategoriju vēstures tabula</h1>
                <table>
                    <tr>
                        <th>Kategorija</th>
                        <th>Vidējā €/L</th>
                        <th>Laiks</th>
                        <th>Darbības</th>
                    </tr>
                    <?php
                    $db = new SQLite3('/var/www/mysite/database/kabinets.db', SQLITE3_OPEN_READONLY);

                    $sql = "SELECT * FROM categories";
                    $results = $db->query($sql);
                    
                    if ($results) {
                        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['avg_price']) . " €/L</td>";
                            echo "<td>" . htmlspecialchars($row['timestamp']) . "</td>";
                            echo "<td>
                                    <a href='edit_category.php?id=" . $row['id'] . "'>
                                        <button class='btn'>Labot</button>
                                    </a>
                                    <a href='delete_category.php?id=" . $row['id'] . "' onclick=\"return confirm('Vai tiešām dzēst?');\">
                                        <button class='btn'>Dzēst</button>
                                    </a>
                                </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No data found!</td></tr>";
                    }
                    $db->close();
                    ?>
                </table>
            </div>
        </div>
    </div>
    <script src="admin.js"></script>
</body>
</html>