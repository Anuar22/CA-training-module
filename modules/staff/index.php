<?php
// modules/staff/index.php

require_once '../../app/config/config.php'; 
require_once '../../app/helpers/auth_helper.php';
restrictToLoggedInUsers();
require_once '../../app/models/CatalogueModel.php';
require_once '../../app/models/SessionModel.php';
$staffModel = new StaffModel();
$message = '';

// Handle Form Submission for Adding a New Staff Member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_staff') {
    $staffId    = trim($_POST['staff_id']);
    $firstName  = trim($_POST['first_name']);
    $lastName   = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $department = trim($_POST['department']);
    $dateJoined = !empty($_POST['date_joined']) ? $_POST['date_joined'] : date('Y-m-d');

    if (!empty($staffId) && !empty($firstName) && !empty($lastName) && !empty($email)) {
        try {
            if ($staffModel->addStaff($staffId, $firstName, $lastName, $email, $department, $dateJoined)) {
                $message = "<div class='alert alert-success'>Staff member successfully registered!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Failed to save records.</div>";
            }
        } catch (PDOException $e) {
            // Friendly error handler for unique key violations (Duplicate emails or Staff IDs)
            if ($e->getCode() == 23505) {
                $message = "<div class='alert alert-danger'>Error: A staff member with this Staff ID or Email already exists.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    } else {
        $message = "<div class='alert alert-warning'>Please fill in all required fields (*).</div>";
    }
}

// Fetch current directory
$allStaff = $staffModel->getAllStaff();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Directory - Corporate Apps</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">Staff Directory Management</h2>
            <?php echo $message; ?>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">Register Staff Profile</div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add_staff">
                        
                        <div class="mb-3">
                            <label class="form-label">St. Jude Staff ID *</label>
                            <input type="text" name="staff_id" class="form-control" placeholder="e.g. TSJ-1024" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control" placeholder="username@schoolofstjude.co.tz" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Department / Team</label>
                            <input type="text" name="department" class="form-control" placeholder="e.g. Finance, Academic, HR" value="General">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date Joined</label>
                            <input type="date" name="date_joined" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Add Staff Profile</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">Registered Employees</div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover m-0">
                        <thead>
                            <tr>
                                <th>Staff ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($allStaff)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted p-4">No staff members registered in the module yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($allStaff as $person): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($person['staff_id']); ?></code></td>
                                        <td><?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($person['email']); ?></td>
                                        <td><?php echo htmlspecialchars($person['department']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $person['status'] === 'Active' ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo htmlspecialchars($person['status']); ?>
                                            </span>
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