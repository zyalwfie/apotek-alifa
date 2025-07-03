<?php
$conn = new mysqli('localhost', 'root', 'root', 'apotek_alifa');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
