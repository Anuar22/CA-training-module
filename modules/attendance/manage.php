<?php
// modules/attendance/manage.php
require_once '../../app/models/AttendanceModel.php';
require_once '../../app/models/SessionModel.php';

$attendanceModel = new AttendanceModel();
$sessionModel = new SessionModel();
$sessionId = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;

if ($sessionId <= 0) {
    header("Location: index.php");
    exit;
}

$message = '';

// Handling physical file attachment upload validation 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_document') {
    if (isset($_FILES['scanned_sheet']) && $_FILES['scanned_sheet']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['scanned_sheet']['tmp_name'];
        $fileName = $_FILES['scanned_sheet']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (in_array($fileExtension, ['pdf', 'jpg', 'jpeg', 'png'])) {
            $newFileName = "session_" . $sessionId . "_" . time() . "." . $fileExtension;
            $uploadFileDir = '../../assets/uploads/attendance/';
            
            if (!file_exists($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            if (move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
                $dbPath = 'assets/uploads/attendance/' . $newFileName;
                $sessionModel->updateAttachment($sessionId, $dbPath);
                $message = "<div class='alert alert-success'>Scanned attendance sheet uploaded and linked successfully!</div>";
            }
        } else {
            $message = "<div class='alert alert-warning'>Invalid format. Only PDFs and images allowed.</div>";
        }
    }
}

// Handling bulk matrix synchronizations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_roll') {
    $statuses = isset($_POST['status']) ? $_POST['status'] : [];
    $scores   = isset($_POST['score']) ? $_POST['score'] : [];
    $remarks  = isset($_POST['remarks']) ? $_POST['remarks'] : [];
    $reasons  = isset($_POST['absence_reason']) ? $_POST['absence_reason'] : [];

    foreach ($statuses as $staffId => $statusVal) {
        $scoreVal  = isset($scores[$staffId]) && $scores[$staffId] !== '' ? intval($scores[$staffId]) : null;
        $remarkVal = isset($remarks[$staffId]) ? trim($remarks[$staffId]) : '';
        $reasonVal = ($statusVal === 'Absent' && isset($reasons[$staffId])) ? trim($reasons[$staffId]) : null;
        
        $attendanceModel->logAttendance($sessionId, $staffId, $statusVal, $scoreVal, $remarkVal, $reasonVal);
    }
    $message = "<div class='alert alert-success'>Attendance records updated successfully.</div>";
}

$roster = $attendanceModel->getAttendanceBySession($sessionId);
// Fetch current metadata details
$db = new Database();
$sessionMeta = $db->connect()->query("SELECT ts.*, tc.system_name FROM training_sessions ts JOIN training_catalogue tc ON ts.catalogue_id = tc.id WHERE ts.id = $sessionId")->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Roster - Corporate Apps</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Worksheet: <?php echo htmlspecialchars($sessionMeta['system_name']); ?></h2>
            <small class="text-muted">Instructor: <?php echo htmlspecialchars($sessionMeta['trainer_name']); ?> | Date: <?php echo $sessionMeta['session_date']; ?></small>
        </div>
        <a href="index.php" class="btn btn-outline-secondary">← Back Hub</a>
    </div>

    <?php echo $message; ?>

    <!-- Document Link Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">📁 Scanned Verification Attachment</div>
        <div class="card-body">
            <?php if (!empty($sessionMeta['attachment_path'])): ?>
                <div class="alert alert-info py-2">
                    ✔ Linked Evidence Document: <a href="../../<?php echo $sessionMeta['attachment_path']; ?>" target="_blank" class="fw-bold text-decoration-underline">View Scanned File</a>
                </div>
            <?php endif; ?>
            <form method="POST" action="" enctype="multipart/form-data" class="row g-2 align-items-center">
                <input type="hidden" name="action" value="upload_document">
                <div class="col-md-9">
                    <input class="form-control form-control-sm" type="file" name="scanned_sheet" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-secondary w-100">Upload Signed Proof</button>
                </div>
            </form>
        </div>
    </div>

    <form method="POST" action="">
        <input type="hidden" name="action" value="save_roll">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-bordered table-striped m-0">
                    <thead class="table-secondary">
                        <tr>
                            <th>Staff ID</th>
                            <th>Employee Name</th>
                            <th>Department</th>
                            <th width="160">Status</th>
                            <th width="100">Score (%)</th>
                            <th>Remarks & Absence Violations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roster as $row): $sid = $row['staff_id']; ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($row['unique_code']); ?></code></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['department']); ?></td>
                                <td>
                                    <select name="status[<?php echo $sid; ?>]" class="form-select form-select-sm" onchange="toggleReasonField(this, '<?php echo $sid; ?>')">
                                        <option value="Attended" <?php echo $row['status'] === 'Attended' ? 'selected' : ''; ?>>Attended</option>
                                        <option value="Absent" <?php echo $row['status'] === 'Absent' ? 'selected' : ''; ?>>Absent</option>
                                        <option value="Incomplete" <?php echo $row['status'] === 'Incomplete' ? 'selected' : ''; ?>>Incomplete</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="score[<?php echo $sid; ?>]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($row['score'] ?? ''); ?>">
                                </td>
                                <td>
                                    <input type="text" name="remarks[<?php echo $sid; ?>]" class="form-control form-control-sm mb-1" placeholder="General updates" value="<?php echo htmlspecialchars($row['remarks'] ?? ''); ?>">
                                    <input type="text" id="reason_<?php echo $sid; ?>" name="absence_reason[<?php echo $sid; ?>]" 
                                           class="form-control form-control-sm border-danger text-danger" placeholder="⚠️ Reason for Absence Required" 
                                           value="<?php echo htmlspecialchars($row['absence_reason'] ?? ''); ?>" style="display: <?php echo $row['status'] === 'Absent' ? 'block' : 'none'; ?>;">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-end"><button type="submit" class="btn btn-primary px-4">Synchronize Roster Rows</button></div>
        </div>
    </form>
</div>

<script>
function toggleReasonField(element, id) {
    const field = document.getElementById('reason_' + id);
    field.style.display = (element.value === 'Absent') ? 'block' : 'none';
    if(element.value !== 'Absent') field.value = '';
}
</script>
</body>
</html>