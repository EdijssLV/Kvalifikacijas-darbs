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
                        <th>Dzēriens</th>
                        <th>Tilpums</th>
                        <th>Kategorija</th>
                        <th>Veikals</th>
                        <th>Cena</th>
                        <th>Cena/L</th>
                        <th>Darbības</th>
                    </tr>
                    <?php
                        $db = new SQLite3('/var/www/mysite/database/kabinets.db', SQLITE3_OPEN_READONLY);

                        $sql = "SELECT * FROM Kabinets";
                        $results = $db->query($sql);

                        if ($results) {
                            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                                echo "<tr>";
                                echo "<td>" . $row['Name'] . "</td>";
                                echo "<td>" . $row['Volume'] . " L</td>";
                                echo "<td>" . $row['Price'] . " €</td>";
                                echo "<td>" . $row['Store'] . "</td>";
                                echo "<td>" . $row['Category'] . "</td>";
                                echo "<td>" . $row['PricePerLiter'] . " €/L</td>";
                                echo "<td><a href='" . $row['links'] . "' target='_blank'>Links</a></td>";
                                echo "<td><button class='btn'>Labot</button><button class='btn'>Dzēst</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr>";
                            echo "<td>No data found!</td>";
                            echo "</tr>";
                        }

                        $db->close();
                        ?>
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
                                echo "<td>" . $row['category'] . "</a></td>";
                                echo "<td>" . $row['avg_price'] . " €/L</td>";
                                echo "<td>" . $row['timestamp'] . "</td>";
                                echo "<td><button class='btn'>Labot</button><button class='btn'>Dzēst</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr>";
                            echo "<td>No data found!</td>";
                            echo "</tr>";
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