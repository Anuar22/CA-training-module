<?php
// app/config/config.php

// 1. URL Root: Adjust this if your folder name inside xampp/htdocs is different
define('URLROOT', 'http://localhost/training-management'); 

// 2. App Root: Dynamically calculates the absolute file system path to the /app directory
define('APPROOT', dirname(dirname(__FILE__)));

// 3. Site Name
define('SITENAME', 'The School of St. Jude - Corporate Applications Training System');

// 4. Start the session globally if it hasn't been initialized yet
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}