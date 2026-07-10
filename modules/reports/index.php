<?php
// modules/reports/index.php
require_once '../../app/models/ReportModel.php';
require_once '../../app/models/StaffModel.php';

$reportModel = new ReportModel();
$staffModel = new StaffModel();

$allStaff = $staffModel->getAllStaff();
$selectedStaffId = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : 0;

$individualHistory = [];
$outstandingMandatory = [];
$selectedStaffDetails = null;

if ($selectedStaffId > 0) {
    $individualHistory = $reportModel->getStaffTrainingHistory($selectedStaffId);
    $selectedStaffDetails = $staffModel->getStaffById($selectedStaffId);

    // Direct database calculation query to find outstanding mandatory sessions
    $db = new Database();
    $sql = "SELECT id, system_name, description FROM training_catalogue 
            WHERE is_mandatory = TRUE 
            AND id NOT IN (
                SELECT ts.catalogue_id FROM attendance att
                JOIN training_sessions ts ON att.session_id = ts.id
                WHERE att.staff_id = :staff_id AND att.status = 'Attended'
            )";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':staff_id', $selectedStaffId, PDO::PARAM_INT);
    $stmt->execute();
    $outstandingMandatory = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Training Audit Reports</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5 mb-5">
    <h2>Corporate Applications Training Audits</h2>
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-primary mb-3">
                <div class="card-header bg-primary text-white">Staff Selection Lookup</div>
                <div class="card-body">
                    <form method="GET" action="">
                        <select name="staff_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Select Staff Profile --</option>
                            <?php foreach ($allStaff as $st): ?>
                                <option value="<?php echo $st['id']; ?>" <?php echo $selectedStaffId === $st['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($st['last_name'] . ', ' . $st['first_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <?php if ($selectedStaffDetails): ?>
                <!-- OUTSTANDING MANDATORY COMPLIANCE WINDOW -->
                <div class="card shadow-sm border-danger mb-4">
                    <div class="card-header bg-danger text-white fw-bold">⚠️ Outstanding Mandatory Training Gaps</div>
                    <div class="card-body p-0">
                        <table class="table m-0">
                            <thead><tr><th>System Name</th><th>Description</th></tr></thead>
                            <tbody>
                                <?php if (empty($outstandingMandatory)): ?>
                                    <tr><td colspan="2" class="text-center text-success py-3 fw-bold">✔ Compliance Met! No outstanding mandatory items.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($outstandingMandatory as $gap): ?>
                                        <tr class="table-danger">
                                            <td><strong><?php echo htmlspecialchars($gap['system_name']); ?></strong></td>
                                            <td class="small"><?php echo htmlspecialchars($gap['description']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- HISTORICAL TRANSCRIPT WINDOW -->
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">Completed Sessions Registry</div>
                    <div class="card-body p-0">
                        <table class="table table-striped m-0">
                            <thead>
                                <tr>
                                    <th>System</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Non-Attendance Tracking Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($individualHistory as $h): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($h['system_name']); ?></td>
                                        <td><?php echo $h['session_date']; ?></td>
                                        <td>
                                            <span class="badge <?php echo $h['status'] === 'Attended' ? 'bg-success' : 'bg-warning'; ?>">
                                                <?php echo $h['status']; ?>
                                            </span>
                                        </td>
                                        <td class="text-danger small fw-bold"><?php echo htmlspecialchars($h['absence_reason'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-secondary text-center">Please use the left panel tool to extract data matrices.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>