<?php
// modules/training_catalogue/index.php
require_once '../../app/models/CatalogueModel.php';
require_once __DIR__ . '/app/config/config.php'; // Ensure correct relative level
require_once __DIR__ . '/app/helpers/auth_helper.php';
restrictToLoggedInUsers();

$catalogueModel = new CatalogueModel();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $systemName = trim($_POST['system_name']);
    $description = trim($_POST['description']);
    $versionTracked = trim($_POST['version_tracked']);
    $isMandatory = isset($_POST['is_mandatory']) ? 1 : 0; // Captured value

    if (!empty($systemName)) {
        // Simple rewrite using direct execution logic for update compatibility
        $sql = "INSERT INTO training_catalogue (system_name, description, version_tracked, is_mandatory) 
                VALUES (:system_name, :description, :version_tracked, :is_mandatory)";
        $db = new Database();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':system_name', $systemName, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':version_tracked', $versionTracked, PDO::PARAM_STR);
        $stmt->bindValue(':is_mandatory', $isMandatory, PDO::PARAM_BOOL);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>System successfully added to the training registry!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to add the system. Please try again.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>System Name is required.</div>";
    }
}

// Fetch records dynamically
$db = new Database();
$systems = $db->connect()->query("SELECT * FROM training_catalogue ORDER BY system_name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Systems Catalogue - Corporate Apps</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">Corporate Systems Training Catalogue</h2>
            <?php echo $message; ?>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">Register New System</div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">System Name</label>
                            <input type="text" name="system_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Version Tracked</label>
                            <input type="text" name="version_tracked" class="form-control" value="v1.0">
                        </div>
                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_mandatory" id="isMandatorySwitch">
                            <label class="form-check-label fw-bold text-danger" for="isMandatorySwitch">Mandatory Training Required</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description / Purpose</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Save System</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">Currently Managed Platforms</div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover m-0">
                        <thead>
                            <tr>
                                <th>System Name</th>
                                <th>Type</th>
                                <th>Version</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($systems as $system): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($system['system_name']); ?></strong></td>
                                    <td>
                                        <span class="badge <?php echo $system['is_mandatory'] ? 'bg-danger' : 'bg-secondary'; ?>">
                                            <?php echo $system['is_mandatory'] ? 'Mandatory' : 'Optional'; ?>
                                        </span>
                                    </td>
                                    <td><code><?php echo htmlspecialchars($system['version_tracked']); ?></code></td>
                                    <td><?php echo htmlspecialchars($system['description']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>