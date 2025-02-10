<?php
session_start();

$timeout = 30 * 60;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

define('PASSWORD_HASH', password_hash("parole", PASSWORD_DEFAULT));
?>
