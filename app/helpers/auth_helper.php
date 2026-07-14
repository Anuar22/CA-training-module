<?php
// app/helpers/auth_helper.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Protects an administrative route by forcing an active session check
 */
function restrictToLoggedInUsers() {
    // If config hasn't been loaded by the parent file, load it safely relative to this file
    if (!defined('URLROOT')) {
        require_once dirname(__DIR__) . '/config/config.php';
    }

    if (!isset($_SESSION['user_id'])) {
        header("Location: " . URLROOT . "/login.php");
        exit;
    }
}