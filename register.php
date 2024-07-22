<?php
// не размещать на сайте
require 'config.php';
require 'Database.php';

$config = require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $db = new Database($config);
    $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
    $db->query($sql, [$username, $password], 'ss');

    echo "User registered successfully";
}
?>

<form method="post" action="register.php">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Register</button>
</form>
