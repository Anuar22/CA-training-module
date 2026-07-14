<?php
// modules/catalogue/manage.php
require_once '../../app/config/config.php';
require_once '../../app/helpers/auth_helper.php';
restrictToLoggedInUsers();

require_once '../../app/models/CatalogueModel.php';
$catalogueModel = new CatalogueModel();

$successMsg = '';
$errorMsg = '';

// Dynamically discover allowed values directly from database constraints
$allowedCategories = $catalogueModel->getAllowedConstraintValues('category');
$allowedReqTypes   = $catalogueModel->getAllowedConstraintValues('requirement_type');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['system_name'])) {
    $systemName      = trim($_POST['system_name']);
    $isMandatory     = isset($_POST['is_mandatory']) ? 1 : 0;
    $category        = $_POST['category'] ?? '';
    $requirementType = $_POST['requirement_type'] ?? '';

    if (!empty($systemName) && !empty($category) && !empty($requirementType)) {
        if ($catalogueModel->registerSystem($systemName, $isMandatory, $category, $requirementType)) {
            $successMsg = "Successfully registered <strong>" . htmlspecialchars($systemName) . "</strong>!";
        } else {
            $errorMsg = "Failed to add system. Check database integrity constraints.";
        }
    } else {
        $errorMsg = "All fields are required.";
    }
}

$systems = $catalogueModel->getAllSystems();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage System Catalogue - Corporate Apps</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container mt-5 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0">Corporate Application Systems Catalogue</h2>
            <p class="text-muted small">Register active target software modules</p>
        </div>
        <a href="../../index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-house-door me-1"></i>Home Dashboard</a>
    </div>

    <?php if (!empty($successMsg)): ?>
        <div class="alert alert-success shadow-sm small"><?php echo $successMsg; ?></div>
    <?php endif; ?>

    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger shadow-sm small"><?php echo $errorMsg; ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Form Panel -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold">Register New System</div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">System Name</label>
                            <input type="text" name="system_name" class="form-control" placeholder="e.g. Aruti" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">System Category</label>
                            <select name="category" class="form-select" required>
                                <option value="">-- Choose Category --</option>
                                <?php foreach ($allowedCategories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Requirement Type</label>
                            <select name="requirement_type" class="form-select" required>
                                <option value="">-- Choose Type --</option>
                                <?php foreach ($allowedReqTypes as $type): ?>
                                    <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_mandatory" id="mandatorySwitch" checked>
                                <label class="form-check-label fw-semibold small" for="mandatorySwitch">Mandatory Training Module</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-sm py-2">Add to Active Catalogue</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Registry List -->
        <div class="col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">Active Definitions Registry</div>
                <div class="card-body p-0">
                    <table class="table table-hover table-striped align-middle m-0">
                        <thead class="table-light">
                            <tr>
                                <th width="15%">System ID</th>
                                <th>System Module Name</th>
                                <th class="text-end px-4">Status Class</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($systems)): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted small">No systems registered.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($systems as $sys): ?>
                                    <tr>
                                        <td><code>#<?php echo htmlspecialchars($sys['id']); ?></code></td>
                                        <td><strong><?php echo htmlspecialchars($sys['system_name']); ?></strong></td>
                                        <td class="text-end px-4">
                                            <?php if ($sys['is_mandatory']): ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1">Mandatory</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle py-1">Optional</span>
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

</div>
</body>
</html>