<?php

require_once 'app/config/database.php';

$db = new Database();

$conn = $db->connect();

if ($conn) {
    echo "✅ PostgreSQL Connected Successfully!";
}