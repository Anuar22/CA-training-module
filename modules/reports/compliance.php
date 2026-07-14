<?php
// modules/reports/compliance.php
require_once '../../app/config/config.php';
require_once '../../app/helpers/auth_helper.php';
restrictToLoggedInUsers();

require_once '../../app/models/ReportModel.php';

$reportModel = new ReportModel();
$roster = $reportModel->getComplianceRoster();

// Calculate metrics for the management overview widgets
$totalStaff = count($roster);
$fullyCompliant = 0;
$nonCompliant = 0;

foreach ($roster as $person) {
    if ($person['missing_mandatory_count'] == 0) {
        $fullyCompliant++;
    } else {
        $nonCompliant++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Compliance Matrix - Corporate Apps</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0">Mandatory Training Compliance Matrix</h2>
            <p class="text-muted small">Real-time gap visibility for Corporate Application modules</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">← Back to Audits</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm text-center bg-white border-start border-primary border-4 py-2">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small fw-bold">Total Staff Tracked</h6>
                    <h2 class="m-0 fw-bold text-primary"><?php echo $totalStaff; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm text-center bg-white border-start border-success border-4 py-2">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small fw-bold">Fully Compliant Users</h6>
                    <h2 class="m-0 fw-bold text-success"><?php echo $fullyCompliant; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm text-center bg-white border-start border-danger border-4 py-2">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small fw-bold">Users With Gaps</h6>
                    <h2 class="m-0 fw-bold text-danger"><?php echo $nonCompliant; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center py-3">
            <span>Staff Preparedness Audit Roster</span>
            <span class="badge bg-secondary">Action Priority Queue</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-striped align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <th>Staff ID</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Outstanding Mandatories</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($roster)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No active staff roster directory values found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($roster as $row): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($row['unique_code']); ?></code></td>
                                <td><?php echo htmlspecialchars($row['department']); ?></td>
                                <td>
                                    <?php if ($row['missing_mandatory_count'] == 0): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            ● Compliant
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                            ⚠️ Gaps Found
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['missing_mandatory_count'] == 0): ?>
                                        <span class="text-success small fw-bold">All Met</span>
                                    <?php else: ?>
                                        <span class="fw-bold text-danger">
                                            <?php echo $row['missing_mandatory_count']; ?> pending profile<?php echo $row['missing_mandatory_count'] > 1 ? 's' : ''; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="index.php?staff_id=<?php echo urlencode($row['unique_code']); ?>" class="btn btn-sm btn-primary py-1 px-3">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>