<?php
// modules/attendance/index.php

require_once '../../app/models/CatalogueModel.php';
require_once '../../app/models/SessionModel.php';

$catalogueModel = new CatalogueModel();
$sessionModel = new SessionModel();

$systems = $catalogueModel->getAllSystems();
$sessions = $sessionModel->getAllSessions();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_session') {
    $catalogueId    = intval($_POST['catalogue_id']);
    $trainerName    = trim($_POST['trainer_name']);
    $sessionDate    = $_POST['session_date'];
    $sessionTime    = $_POST['session_time'];
    $locationOrLink = trim($_POST['location_or_link']);
    $notes          = trim($_POST['notes']);

    if ($catalogueId > 0 && !empty($trainerName) && !empty($sessionDate)) {
        $newSessionId = $sessionModel->createSession($catalogueId, $trainerName, $sessionDate, $sessionTime, $locationOrLink, $notes);
        if ($newSessionId) {
            header("Location: manage.php?session_id=" . $newSessionId);
            exit;
        } else {
            $message = "<div class='alert alert-danger'>Failed to schedule training session.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Please fill in all mandatory parameters.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Training Sessions - Corporate Apps</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12 mb-4">
            <h2>Track & Record Training Sessions</h2>
            <?php echo $message; ?>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">Create New Training Event</div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="create_session">
                        
                        <div class="mb-3">
                            <label class="form-label">Select Target System *</label>
                            <select name="catalogue_id" class="form-select" required>
                                <option value="">-- Choose Platform --</option>
                                <?php foreach ($systems as $sys): ?>
                                    <option value="<?php echo $sys['id']; ?>"><?php echo htmlspecialchars($sys['system_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lead Trainer Name *</label>
                            <input type="text" name="trainer_name" class="form-control" required>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Date *</label>
                                <input type="date" name="session_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Time</label>
                                <input type="time" name="session_time" class="form-control" value="09:00">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Venue / Room Location</label>
                            <input type="text" name="location_or_link" class="form-control" value="Corporate Apps Room">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Session Agenda / Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100">Initialize Session Worksheet</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">Historical Training Events Log</div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover m-0">
                        <thead>
                            <tr>
                                <th>System</th>
                                <th>Trainer</th>
                                <th>Execution Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sessions)): ?>
                                <tr>
                                    <td colspan="4" class="text-center p-4 text-muted">No historical training sessions initialized yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($sessions as $sess): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($sess['system_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($sess['trainer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($sess['session_date']); ?></td>
                                        <td>
                                            <a href="manage.php?session_id=<?php echo $sess['id']; ?>" class="btn btn-sm btn-primary">
                                                Update Attendance Roll
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
    </div>
</div>

</body>
</html>