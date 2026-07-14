<?php
// modules/reports/index.php
require_once '../../app/config/config.php';
require_once '../../app/helpers/auth_helper.php';
restrictToLoggedInUsers();

require_once '../../app/models/ReportModel.php';

$reportModel = new ReportModel();
$selectedStaffId = isset($_GET['staff_id']) ? trim($_GET['staff_id']) : '';

$history = [];
$staffData = null;

if (!empty($selectedStaffId)) {
    $history = $reportModel->getStaffTrainingHistory($selectedStaffId);
    
    $roster = $reportModel->getComplianceRoster();
    foreach ($roster as $person) {
        if ($person['unique_code'] == $selectedStaffId) {
            $staffData = $person;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Audit Profile - Corporate Apps</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container mt-5 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0">Individual Operational Preparedness Audit</h2>
            <p class="text-muted small">Verification ledger for individual system permissions, history and retraining tracking</p>
        </div>
        <div class="d-flex gap-2">
            <a href="compliance.php" class="btn btn-outline-secondary btn-sm">← Back to Compliance</a>
            <a href="../../index.php" class="btn btn-dark btn-sm"><i class="bi bi-house-door me-1"></i>Home Dashboard</a>
        </div>
    </div>

    <?php if (empty($selectedStaffId)): ?>
        <div class="alert alert-info shadow-sm p-4 text-center">
            <h5>No Staff Profile Selected</h5>
        </div>
    <?php elseif (!$staffData): ?>
        <div class="alert alert-danger shadow-sm p-4 text-center">
            <h5>Profile Identity Not Found</h5>
        </div>
    <?php else: ?>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 mb-4 text-center">
                    <div class="card-body py-4">
                        <div class="rounded-circle bg-dark text-white d-inline-flex align-items-center justify-content-center fw-bold mb-3" style="width: 70px; height: 70px; font-size: 1.5rem;">
                            ID
                        </div>
                        <h4 class="fw-bold text-dark mb-1">Staff Reference Code</h4>
                        <code><?php echo htmlspecialchars($staffData['unique_code']); ?></code>
                        <hr class="my-3">
                        <div class="text-start px-2">
                            <p class="small m-0 text-secondary fw-bold">Department Assignment:</p>
                            <p class="small text-dark fw-semibold mb-3"><?php echo htmlspecialchars($staffData['department']); ?></p>
                            
                            <p class="small m-0 text-secondary fw-bold">Current Standing:</p>
                            <p class="m-0">
                                <?php if ($staffData['missing_mandatory_count'] == 0): ?>
                                    <span class="badge bg-success w-100 py-2">● Fully Compliant</span>
                                <?php else: ?>
                                    <span class="badge bg-danger w-100 py-2">⚠️ Gaps Flagged</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 text-center bg-white p-3 border-start border-danger border-4">
                    <h6 class="text-uppercase text-muted small fw-bold m-0">Outstanding Mandatory Courses</h6>
                    <h1 class="fw-bold text-danger m-0 display-5"><?php echo $staffData['missing_mandatory_count']; ?></h1>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white fw-bold">System Competency Timeline Ledger</div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped align-middle m-0">
                            <thead class="table-light">
                                <tr>
                                    <th>System Module Name</th>
                                    <th>Session Date</th>
                                    <th>Status / Method</th>
                                    <th>Exceptions</th>
                                    <th class="text-center">Evidence Document</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($history)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">No verified attendance history logged for this profile.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($history as $log): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($log['system_name']); ?></strong></td>
                                            <td><small><?php echo date('Y-m-d', strtotime($log['session_date'])); ?></small></td>
                                            <td>
                                                <?php if (strtoupper($log['status']) === 'PRESENT'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">Attended</span>
                                                    <span class="text-muted d-block mt-1" style="font-size: 0.7rem;"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($log['venue']); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 small">Absent</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="small text-muted">
                                                    <?php echo !empty($log['absence_reason']) ? htmlspecialchars($log['absence_reason']) : '—'; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($log['attachment_path'])): ?>
                                                    <a href="../../uploads/<?php echo urlencode($log['attachment_path']); ?>" target="_blank" class="btn btn-outline-danger btn-sm py-0 px-2 fw-bold" style="font-size: 0.8rem;">
                                                        <i class="bi bi-file-earmark-pdf-fill"></i> View Sheet
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">No File Attached</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>
</body>
</html>