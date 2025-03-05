<?php
session_start();
$db = new SQLite3('/var/www/mysite/database/kabinets.db');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Check if email already exists
    $checkQuery = $db->prepare("SELECT id FROM users WHERE email = :email");
    $checkQuery->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $checkQuery->execute()->fetchArray();

    if ($result) {
        echo "Šis e-pasts jau ir reģistrēts!";
    } else {
        // Insert new user
        $query = $db->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
        $query->bindValue(':name', $name, SQLITE3_TEXT);
        $query->bindValue(':email', $email, SQLITE3_TEXT);
        $query->bindValue(':password', $password, SQLITE3_TEXT);
        $query->execute();

        echo "Reģistrācija veiksmīga! <a href='index.php'>Pieslēgties</a>";
    }
}
?>
<?php include 'head.php'; ?>
<body class="formLapa" background="http://st.depositphotos.com/1987851/1904/i/450/depositphotos_19041043-Old-wallpaper-seamless-texture.jpg">
    <form method="post">
        <h1>Reģistrēties</h1>
        Vārds: <input type="text" name="name" required><br>
        E-pasts: <input type="email" name="email" required><br>
        Parole: <input type="password" name="password" required><br>
        <button type="submit" class="btn">Reģistrēties</button>
    </form>
</body>