<?php
// index.php

require_once 'app/config/config.php';
require_once 'app/config/database.php';
require_once __DIR__ . '/app/config/config.php'; // Ensure correct relative level
require_once __DIR__ . '/app/helpers/auth_helper.php';
restrictToLoggedInUsers();

// Instantiate database connection to pull quick dashboard counters
$db = new Database();
$conn = $db->connect();

$systemCount = 0;
$staffCount = 0;
$sessionCount = 0;

try {
    // Quick metric counters for St. Jude admin overview
    $systemCount  = $conn->query("SELECT COUNT(*) FROM training_catalogue")->fetchColumn();
    $staffCount   = $conn->query("SELECT COUNT(*) FROM staff WHERE status = 'Active'")->fetchColumn();
    $sessionCount = $conn->query("SELECT COUNT(*) FROM training_sessions")->fetchColumn();
} catch (PDOException $e) {
    // If tables don't exist yet, gracefully default to 0 metrics
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>St. Jude - Corporate Apps Training Platform</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .hub-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }
        .hub-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
        }
        .icon-box {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="bg-light">

<!-- Navbar Header -->
<nav class="navbar navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <strong class="text-uppercase tracking-wider">The School of St. Jude</strong>
            <span class="badge bg-primary ms-3">Corporate Applications Portal</span>
        </a>
        <span class="navbar-text text-white-50 small">Training Management System v1.0</span>
    </div>
</nav>

<!-- Main Container -->
<div class="container mt-5">
    
    <!-- Welcome Header -->
    <div class="p-5 mb-4 bg-white rounded-3 shadow-sm border">
        <div class="container-fluid py-2">
            <h1 class="display-6 fw-bold text-dark">Staff Systems Competency Hub</h1>
            <p class="col-md-10 fs-6 text-muted">
                Welcome to the centralized registry. This module eliminates paper trails, making it effortless to record, track, and audit staff profiles against St. Jude's custom CRM, ERP modules, and auxiliary corporate applications.
            </p>
        </div>
    </div>

    <!-- Quick Metric Cards Row -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card text-center bg-white border shadow-sm py-3">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small fw-bold">Tracked Platforms</h6>
                    <h2 class="display-5 fw-bold text-primary m-0"><?php echo $systemCount; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-white border shadow-sm py-3">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small fw-bold">Active Staff Directory</h6>
                    <h2 class="display-5 fw-bold text-success m-0"><?php echo $staffCount; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-white border shadow-sm py-3">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small fw-bold">Training Events Logged</h6>
                    <h2 class="display-5 fw-bold text-dark m-0"><?php echo $sessionCount; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Navigation Matrix -->
    <h4 class="mb-4 text-secondary fw-semibold">Operational Modules</h4>
    <div class="row g-4">
        
        <!-- Module 1: Systems Registry -->
        <div class="col-md-3" onclick="location.href='modules/training_catalogue/index.php'">
            <div class="card h-100 hub-card text-center border shadow-sm">
                <div class="card-body py-4">
                    <div class="icon-box text-primary">⚙️</div>
                    <h5 class="card-title fw-bold">Systems Catalogue</h5>
                    <p class="card-text text-muted small">Register internal application names, descriptions, and active versions.</p>
                </div>
            </div>
        </div>

        <!-- Module 2: Staff Profiles -->
        <div class="col-md-3" onclick="location.href='modules/staff/index.php'">
            <div class="card h-100 hub-card text-center border shadow-sm">
                <div class="card-body py-4">
                    <div class="icon-box text-success">👥</div>
                    <h5 class="card-title fw-bold">Staff Directory</h5>
                    <p class="card-text text-muted small">Manage organizational staff information, departments, and onboarding timelines.</p>
                </div>
            </div>
        </div>

        <!-- Module 3: Attendance Matrix -->
        <div class="col-md-3" onclick="location.href='modules/attendance/index.php'">
            <div class="card h-100 hub-card text-center border shadow-sm">
                <div class="card-body py-4">
                    <div class="icon-box text-warning">📝</div>
                    <h5 class="card-title fw-bold">Log Attendance</h5>
                    <p class="card-text text-muted small">Initialize live training sessions, complete rolls, and evaluate performance scores.</p>
                </div>
            </div>
        </div>

        <!-- Module 4: Intelligent Reports -->
        <div class="col-md-3" onclick="location.href='modules/reports/index.php'">
            <div class="card h-100 hub-card text-center border shadow-sm">
                <div class="card-body py-4">
                    <div class="icon-box text-danger">📊</div>
                    <h5 class="card-title fw-bold">Audit Reports</h5>
                    <p class="card-text text-muted small">Search individuals instantly to track historical competencies or refresher needs.</p>
                </div>
            </div>
        </div>

    </div>
</div>

<footer class="footer mt-5 py-3 bg-white border-top text-center">
    <div class="container">
        <span class="text-muted small">© 2026 The School of St. Jude — Corporate Applications Department</span>
    </div>
</footer>

</body>
</html>