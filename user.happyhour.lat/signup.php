<?php
session_start();
$db = new SQLite3('/var/www/mysite/database/kabinets.db');

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $raw_password = $_POST["password"];

    if (empty($name) || empty($email) || empty($raw_password)) {
        $errors[] = "Visi lauki ir obligāti!";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "E-pasta formāts nav derīgs!";
    }

    if (preg_match('/\s/', $name)) {
        $errors[] = "Vārdā nevar būt atstarpes!";
    }
    if (preg_match('/\s/', $email)) {
        $errors[] = "E-pastā nevar būt atstarpes!";
    }
    if (preg_match('/\s/', $raw_password)) {
        $errors[] = "Parolē nevar būt atstarpes!";
    }

    if (preg_match('/(.)\1{1,}/', $name)) {
        $errors[] = "Vārdā nedrīkst būt atkārtoti simboli!";
    }
    if (preg_match('/(.)\1{1,}/', $email)) {
        $errors[] = "E-pastā nedrīkst būt atkārtoti simboli!";
    }
    if (preg_match('/(.)\1{1,}/', $raw_password)) {
        $errors[] = "Parolē nedrīkst būt atkārtoti simboli!";
    }

    if (empty($errors)) {
        $checkQuery = $db->prepare("SELECT id FROM users WHERE email = :email");
        $checkQuery->bindValue(':email', $email, SQLITE3_TEXT);
        $result = $checkQuery->execute()->fetchArray();

        if ($result) {
            $errors[] = "Šis e-pasts jau ir reģistrēts!";
        } else {
            $password = password_hash($raw_password, PASSWORD_DEFAULT);
            $query = $db->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
            $query->bindValue(':name', $name, SQLITE3_TEXT);
            $query->bindValue(':email', $email, SQLITE3_TEXT);
            $query->bindValue(':password', $password, SQLITE3_TEXT);
            $query->execute();

            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;

            header("Location: profile.php");
            exit;
        }
    }
}
?>

<?php include 'head.php'; ?>
<body class="formLapa" background="http://st.depositphotos.com/1987851/1904/i/450/depositphotos_19041043-Old-wallpaper-seamless-texture.jpg">
    <form method="post" class="form">
        <h1>Reģistrēties</h1>
        <div class="error">
            <?php
            if (!empty($errors)) {
                echo "<ul>";
                foreach ($errors as $error) {
                    echo "<li>" . htmlspecialchars($error) . "</li>";
                }
                echo "</ul>";
            }
            if (!empty($successMessage)) {
                echo "<p class='success'>" . $successMessage . "</p>";
            }
            ?>
        </div>
        Vārds: <input type="text" name="name" class="input"><br>
        E-pasts: <input type="email" name="email" class="input"><br>
        Parole: <input type="password" name="password" class="input"><br>
        <button type="submit" class="btn">Reģistrēties</button>
    </form>
</body>