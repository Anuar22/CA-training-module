<?php
// modules/attendance/add.php
require_once '../../app/config/config.php';
require_once '../../app/helpers/auth_helper.php';
restrictToLoggedInUsers();

require_once '../../app/models/SessionModel.php';
require_once '../../app/models/CatalogueModel.php';
require_once '../../app/models/ReportModel.php';

$catalogueModel = new CatalogueModel();
$reportModel = new ReportModel();
$sessionModel = new SessionModel();

$systems = $catalogueModel->getAllSystems(); 
$staffRoster = $reportModel->getComplianceRoster(); 

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainingId  = $_POST['training_id'];
    $sessionDate = $_POST['session_date'];
    $venue       = trim($_POST['venue']);
    $trainer     = trim($_POST['trainer']);
    $notes       = trim($_POST['notes']);
    
    $attendanceData = isset($_POST['attendance']) ? $_POST['attendance'] : [];
    $reasonsData    = isset($_POST['reasons']) ? $_POST['reasons'] : [];

    // File Upload Handler Setup
    $attachmentName = null;
    if (isset($_FILES['attendance_sheet']) && $_FILES['attendance_sheet']['error'] === UPLOAD_ERR_OK) {
        // Create root upload directory folder path if it does not exist yet
        $uploadDir = '../../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExtension = pathinfo($_FILES['attendance_sheet']['name'], PATHINFO_EXTENSION);
        // Generate a unique audit name to prevent historical document overrides
        $attachmentName = 'session_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $targetFilePath = $uploadDir . $attachmentName;

        if (!move_uploaded_file($_FILES['attendance_sheet']['tmp_name'], $targetFilePath)) {
            $attachmentName = null; // Reset pointer if save pipeline fails
        }
    }

    if (!empty($trainingId) && !empty($sessionDate)) {
        // Passed file name tracking handle safely into the model record creator
        $newSessionId = $sessionModel->createSession($trainingId, $sessionDate, $venue, $trainer, $notes, $attachmentName);

        if ($newSessionId) {
            foreach ($staffRoster as $staff) {
                $uid = $staff['unique_code'];
                $status = isset($attendanceData[$uid]) ? 'Attended' : 'Absent';
                $reason = isset($reasonsData[$uid]) ? trim($reasonsData[$uid]) : '';

                $sessionModel->logAttendance($newSessionId, $uid, $status, $reason);
            }
            $successMsg = "Training event and file upload records have been committed successfully!";
        } else {
            $errorMsg = "Critical failure: Could not record training session metadata.";
        }
    } else {
        $errorMsg = "Please populate all mandatory framework fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log Session - Corporate Apps</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container mt-5 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0">Log New Training Session</h2>
            <p class="text-muted small">Record corporate application system completion updates and file signatures</p>
        </div>
        <a href="../../index.php" class="btn btn-outline-secondary btn-sm">← Back to Main Command Center</a>
    </div>

    <?php if (!empty($successMsg)): ?>
        <div class="alert alert-success shadow-sm small"><?php echo $successMsg; ?></div>
    <?php endif; ?>

    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger shadow-sm small"><?php echo $errorMsg; ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                    <div class="card-header bg-dark text-white fw-bold">Session Details</div>
                    <div class="card-body">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Target System Module</label>
                            <select name="training_id" class="form-select form-select-sm" required>
                                <option value="">-- Choose Corporate App --</option>
                                <?php foreach ($systems as $sys): ?>
                                    <option value="<?php echo $sys['id']; ?>">
                                        <?php echo htmlspecialchars($sys['system_name']); ?> 
                                        <?php echo $sys['is_mandatory'] ? '(Mandatory)' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Session Date</label>
                            <input type="date" name="session_date" class="form-control form-control-sm" required value="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Venue / Platform</label>
                            <input type="text" name="venue" class="form-control form-control-sm" placeholder="e.g. Server Room / Teams" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Lead Trainer</label>
                            <input type="text" name="trainer" class="form-control form-control-sm" placeholder="Trainer Name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark"><i class="bi bi-file-earmark-pdf text-danger me-1"></i>Signed Attendance Form</label>
                            <input type="file" name="attendance_sheet" class="form-control form-control-sm" accept=".pdf,image/*">
                            <div class="form-text text-muted" style="font-size: 0.75rem;">Scan/Photo upload (PDF, PNG, JPG)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Notes / Synopsis</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Optional overview logs..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">Commit Attendance Registry</button>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white fw-bold">Staff Attendance Checklist</div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped align-middle m-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="10%">Attended</th>
                                    <th>Staff ID</th>
                                    <th>Department</th>
                                    <th>Reason For Absence (If Empty)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($staffRoster as $staff): ?>
                                    <?php $uid = $staff['unique_code']; ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="attendance[<?php echo $uid; ?>]" value="1" class="form-check-input" checked>
                                        </td>
                                        <td><code><?php echo htmlspecialchars($uid); ?></code></td>
                                        <td><small class="text-dark fw-medium"><?php echo htmlspecialchars($staff['department']); ?></small></td>
                                        <td>
                                            <input type="text" name="reasons[<?php echo $uid; ?>]" class="form-control form-control-sm py-0" placeholder="e.g. Leave / Sick Day">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
</body>
</html>