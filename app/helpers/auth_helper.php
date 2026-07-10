<?php
// app/helpers/auth_helper.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Protects an administrative route by forcing an active session check
 */
function restrictToLoggedInUsers() {
    if (!isset($_SESSION['user_id'])) {
        // Find path relative to execution environment to prevent redirect breakages
        header("Location: " . URLROOT . "/login.php");
        exit;
    }
}