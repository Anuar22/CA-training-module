<?php
// index.php (Root Dashboard)
require_once 'app/config/config.php';
require_once 'app/helpers/auth_helper.php';
restrictToLoggedInUsers();

require_once 'app/models/ReportModel.php';
require_once 'app/models/CatalogueModel.php';

$reportModel = new ReportModel();
$catalogueModel = new CatalogueModel();

// Fetch live counts for summary data cards
$roster = $reportModel->getComplianceRoster();
$totalStaff = count($roster);
$totalSystems = count($catalogueModel->getAllSystems());

$fullyCompliant = 0;
$gapsDetected = 0;
foreach ($roster as $person) {
    if ($person['missing_mandatory_count'] == 0) {
        $fullyCompliant++;
    } else {
        $gapsDetected++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Corporate Apps Training Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-shield-check text-primary me-2"></i>St. Jude Corp Apps Team
            </a>
            <div class="d-flex align-items-center">
                <span class="navbar-text text-white me-3 small">
                    <i class="bi bi-person-circle me-1"></i> Connected as <strong>Admin</strong>
                </span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm py-0 px-2 small">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        
        <div class="p-4 mb-4 bg-white rounded-3 shadow-sm border border-light">
            <h1 class="display-6 fw-bold text-dark">Training & Competency Management</h1>
            <p class="col-md-8 fs-6 text-muted m-0">Track compliance, map out mandatory system knowledge gaps, and verify operational preparedness across all organizational departments instantly.</p>
        </div>

        <div class="row text-center mb-4">
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 h-100 bg-white">
                    <div class="card-body">
                        <div class="text-primary mb-1"><i class="bi bi-people fs-3"></i></div>
                        <h6 class="text-uppercase text-muted small fw-bold">Total Staff</h6>
                        <h2 class="m-0 fw-bold"><?php echo $totalStaff; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 h-100 bg-white">
                    <div class="card-body">
                        <div class="text-success mb-1"><i class="bi bi-check-circle fs-3"></i></div>
                        <h6 class="text-uppercase text-muted small fw-bold">Fully Compliant</h6>
                        <h2 class="m-0 fw-bold text-success"><?php echo $fullyCompliant; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 h-100 bg-white">
                    <div class="card-body">
                        <div class="text-danger mb-1"><i class="bi bi-exclamation-triangle fs-3"></i></div>
                        <h6 class="text-uppercase text-muted small fw-bold">Gaps Flagged</h6>
                        <h2 class="m-0 fw-bold text-danger"><?php echo $gapsDetected; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 h-100 bg-white">
                    <div class="card-body">
                        <div class="text-secondary mb-1"><i class="bi bi-cpu fs-3"></i></div>
                        <h6 class="text-uppercase text-muted small fw-bold">System Modules</h6>
                        <h2 class="m-0 fw-bold text-secondary"><?php echo $totalSystems; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="fw-bold text-dark mb-3">Operational Management Suites</h4>
        <div class="row">
            
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 h-100 transition-card">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary-subtle text-primary rounded p-2 me-3">
                                    <i class="bi bi-grid-3x3-gap fs-4"></i>
                                </div>
                                <h5 class="card-title fw-bold m-0">Compliance Matrix</h5>
                            </div>
                            <p class="card-text text-muted small">Review real-time compliance dashboards tracking mandatory app gaps sorted by action-priority order.</p>
                        </div>
                        <a href="modules/reports/compliance.php" class="btn btn-primary btn-sm w-100 mt-3">Launch Matrix View</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 h-100 transition-card">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success-subtle text-success rounded p-2 me-3">
                                    <i class="bi bi-calendar-plus fs-4"></i>
                                </div>
                                <h5 class="card-title fw-bold m-0">Log Training Session</h5>
                            </div>
                            <p class="card-text text-muted small">Log new system walkthroughs, record details, and batch-update attendance records for the entire staff directory instantly.</p>
                        </div>
                        <a href="modules/attendance/add.php" class="btn btn-success btn-sm w-100 mt-3">Open Batch Logger</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 h-100 transition-card">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-dark-subtle text-dark rounded p-2 me-3">
                                    <i class="bi bi-file-earmark-spreadsheet fs-4"></i>
                                </div>
                                <h5 class="card-title fw-bold m-0">Bulk Directory Import</h5>
                            </div>
                            <p class="card-text text-muted small">Seamlessly sync your organizational records by importing staff spreadsheet files with conflict handling built-in.</p>
                        </div>
                        <a href="modules/staff/import.php" class="btn btn-dark btn-sm w-100 mt-3">Run CSV Pipeline</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card shadow-sm border-0 h-100 transition-card">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-warning-subtle text-warning rounded p-2 me-3">
                                    <i class="bi bi-gear fs-4"></i>
                                </div>
                                <h5 class="card-title fw-bold m-0">System Catalogue</h5>
                            </div>
                            <p class="card-text text-muted small">Configure active systems, define which software modules are mandatory, and register new trainings.</p>
                        </div>
                        <a href="modules/catalogue/manage.php" class="btn btn-warning btn-sm w-100 mt-3 text-dark fw-bold">Manage Systems</a>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <style>
        .transition-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .transition-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.08)!important; }
    </style>
</body>
</html>