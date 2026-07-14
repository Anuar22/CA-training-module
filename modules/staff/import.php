<?php
// modules/staff/import.php
require_once '../../app/config/config.php';
require_once '../../app/helpers/auth_helper.php';
restrictToLoggedInUsers();

require_once '../../app/models/StaffModel.php';

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = "Error uploading file. Please try again.";
    } else {
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (strtolower($fileExtension) !== 'csv') {
            $errorMsg = "Invalid file format. Please upload a valid .csv file.";
        } else {
            if (($handle = fopen($file['tmp_name'], 'r')) !== FALSE) {
                $staffModel = new StaffModel();
                
                // Skip header row
                $header = fgetcsv($handle, 1000, ",");
                
                $successCount = 0;
                $rowCount = 0;

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $rowCount++;
                    
                    if (empty($data) || count($data) < 4) continue;

                    // Parse columns matching our new template layout
                    $staffId    = trim($data[0]);
                    $fullName   = trim($data[1]);
                    $department = trim($data[2]);
                    $status     = trim($data[3]);

                    if (!empty($staffId)) {
                        if ($staffModel->upsertStaff($staffId, $fullName, $department, $status)) {
                            $successCount++;
                        }
                    }
                }
                fclose($handle);
                $successMsg = "Import complete! Successfully processed <strong>{$successCount}</strong> out of {$rowCount} staff entries.";
            } else {
                $errorMsg = "Could not parse the uploaded file stream.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bulk Import Staff - Corporate Apps</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0">Bulk Staff Directory Import</h2>
            <p class="text-muted small">Upload CSV files to seed or update your organizational directory metrics</p>
        </div>
        <a href="../reports/compliance.php" class="btn btn-outline-secondary btn-sm">← Back to Compliance</a>
    </div>

    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white fw-bold">CSV Formatting Template Requirements</div>
                <div class="card-body">
                    <p class="small text-muted">Your file must have a header row exactly as shown below, and use commas to separate columns:</p>
                    <pre class="bg-light p-3 border rounded small"><code>staff_id,full_name,department,status
101,Anwari Mntangi,Corporate Applications,Active
102,John Doe,Administration,Active
103,Jane Smith,Facilities Management,Active</code></pre>
                    <div class="alert alert-warning py-2 px-3 small border m-0">
                        💡 <strong>Note:</strong> If a <code>staff_id</code> matches an existing record, its name, department, and status values will automatically update.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">Select and Execute Directory Upload</div>
                <div class="card-body p-4">
                    
                    <?php if (!empty($successMsg)): ?>
                        <div class="alert alert-success small shadow-sm"><?php echo $successMsg; ?></div>
                    <?php endif; ?>

                    <?php if (!empty($errorMsg)): ?>
                        <div class="alert alert-danger small shadow-sm"><?php echo $errorMsg; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-secondary">Target Spreadsheet File (.csv)</label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm px-4">Execute Matrix Parse & Import</button>
                    </form>

                </div>
            </div>
        </div>
    </div>

</div>
</body>
</html>