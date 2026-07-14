<?php
// modules/reports/sessions.php
require_once '../../app/config/config.php';
require_once '../../app/helpers/auth_helper.php';
restrictToLoggedInUsers();

// Standard database connector logic inline to keep it fast and self-contained
require_once '../../app/config/database.php';
$db = new Database();

// Fetch every recorded training session, joining the definitions table using our verified keys
$sql = "SELECT 
            ts.session_id,
            ts.session_date,
            ts.venue,
            ts.trainer,
            ts.notes,
            ts.attachment_path,
            (SELECT COUNT(*) FROM attendance WHERE session_id = ts.session_id AND status = 'Attended') AS attendee_count,
            td.training_name,
            td.name AS alt_name
        FROM training_sessions ts
        LEFT JOIN training_definitions td ON ts.training_id = td.training_id
        ORDER BY ts.session_date DESC";

$stmt = $db->prepare($sql);
$stmt->execute();
$sessions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Historical Session Log - Corporate Apps</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container mt-5 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0">Master Training Session Records</h2>
            <p class="text-muted small">Historical register of all system walkthroughs and signed compliance documents</p>
        </div>
        <div class="d-flex gap-2">
            <a href="../attendance/add.php" class="btn btn-success btn-sm"><i class="bi bi-plus-circle me-1"></i>Log New Session</a>
            <a href="../../index.php" class="btn btn-outline-secondary btn-sm">← Main Hub</a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white fw-bold py-3">
            Historical Events & Uploaded Signatures
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-striped align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <th>Date Conducted</th>
                        <th>Application Module</th>
                        <th>Lead Trainer</th>
                        <th>Location/Method</th>
                        <th class="text-center">Staff Present</th>
                        <th class="text-center">Evidence Document</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sessions)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted small">No training sessions have been logged in the system registry yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sessions as $row): ?>
                            <?php $systemDisplayName = $row['training_name'] ?? $row['alt_name'] ?? 'System Module #' . $row['session_id']; ?>
                            <tr>
                                <td><strong class="text-dark"><?php echo date('Y-m-d', strtotime($row['session_date'])); ?></strong></td>
                                <td><span class="fw-semibold text-primary"><?php echo htmlspecialchars($systemDisplayName); ?></span></td>
                                <td><small class="text-secondary"><?php echo htmlspecialchars($row['trainer']); ?></small></td>
                                <td>
                                    <span class="small"><i class="bi bi-geo-alt text-muted me-1"></i><?php echo htmlspecialchars($row['venue']); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2">
                                        <?php echo $row['attendee_count']; ?> Staff
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($row['attachment_path'])): ?>
                                        <a href="../../uploads/<?php echo urlencode($row['attachment_path']); ?>" target="_blank" class="btn btn-sm btn-danger py-1 px-3 fw-bold" style="font-size: 0.75rem;">
                                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download Roster
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small italic">No Sheet Attached</span>
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
</body>
</html>