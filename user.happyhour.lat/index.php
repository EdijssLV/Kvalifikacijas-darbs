<?php
session_start();
$db = new SQLite3('/var/www/mysite/database/kabinets.db');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Find user by email
    $query = $db->prepare("SELECT * FROM users WHERE email = :email");
    $query->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $query->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result && password_verify($password, $result['password'])) {
        $_SESSION["user_id"] = $result["id"];
        $_SESSION["name"] = $result["name"];
        
        header("Location: profile.php");
        exit;
    } else {
        echo "Nepareizs e-pasts vai parole!";
    }
}
?>
<?php include 'head.php'; ?>
<body class="formLapa" background="http://st.depositphotos.com/1987851/1904/i/450/depositphotos_19041043-Old-wallpaper-seamless-texture.jpg">
    <form method="post" class="form">
        <h1>Pieslēgties</h1>
        E-pasts: <input type="email" name="email" required><br>
        Parole: <input type="password" name="password" required><br>
        <button type="submit" class="btn">Pieslēgties</button>
        <p>Jums nav konta? <a href="signup.php">Reģistrējieties šeit!</a>.</p>
    </form>
</body>