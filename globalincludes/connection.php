<?php
//google
$dbtype = "mysql";
$dbhost = "104.154.153.225";
$dbuser = "bentley";
$dbpass = "dave41";
$dbname = "gillingham";
$conn1 = new PDO("{$dbtype}:host={$dbhost};dbname={$dbname};charset=utf8", $dbuser, $dbpass, array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC));