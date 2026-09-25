<?php
include "db.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "create";

    if ($action === "update") {
        $leaveId = (int) ($_POST["leave_id"] ?? 0);
        $status = trim($_POST["status"] ?? "");

        if ($leaveId <= 0 || !in_array($status, ["Approved", "Rejected"], true)) {
            $error = "Invalid leave update request.";
        } else {
            $stmt = $conn->prepare("UPDATE leaves SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $leaveId);

            if ($stmt->execute()) {
                $message = "Leave status updated successfully.";
            } else {
                $error = "Unable to update leave status.";
            }
        }
    } else {
        $employeeId = (int) ($_POST["employee_id"] ?? 0);
        $leaveType = trim($_POST["leave_type"] ?? "");
        $startDate = trim($_POST["start_date"] ?? "");
        $endDate = trim($_POST["end_date"] ?? "");
        $reason = trim($_POST["reason"] ?? "");

        if ($employeeId <= 0 || $leaveType === "" || $startDate === "" || $endDate === "") {
            $error = "Please fill in all required leave fields.";
        } elseif (strtotime($endDate) < strtotime($startDate)) {
            $error = "End date cannot be earlier than the start date.";
        } else {
            $employeeCheck = $conn->prepare("SELECT id FROM employees WHERE id = ? AND deleted_at IS NULL LIMIT 1");
            $employeeCheck->bind_param("i", $employeeId);
            $employeeCheck->execute();
            $employeeResult = $employeeCheck->get_result();

            if ($employeeResult->num_rows === 0) {
                $error = "Selected employee was not found.";
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO leaves (employee_id, leave_type, start_date, end_date, reason, status, applied_at)
                     VALUES (?, ?, ?, ?, ?, 'Pending', NOW())"
                );
                $stmt->bind_param("issss", $employeeId, $leaveType, $startDate, $endDate, $reason);

                if ($stmt->execute()) {
                    $message = "Leave request submitted successfully.";
                } else {
                    $error = "Unable to submit leave request.";
                }
            }
        }
    }
}

$employees = $conn->query("SELECT id, full_name, email FROM employees WHERE deleted_at IS NULL ORDER BY full_name ASC");
$leaveRecords = $conn->query(
    "SELECT l.*, e.full_name, e.email
     FROM leaves l
     LEFT JOIN employees e ON e.id = l.employee_id
     ORDER BY l.applied_at DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<nav class="navbar navbar-dark metallic-navbar">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="index.php" class="navbar-brand mb-0 h1">Employee Management System</a>
        <button id="themeToggle" class="theme-toggle" type="button">🌙 Dark</button>
    </div>
</nav>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <span class="dashboard-pill">Leave Management</span>
            <h2 class="mb-1">Leave Requests</h2>
            <p class="text-secondary mb-0">Track employee leaves and approval status.</p>
        </div>
        <div>
            <a href="index.php" class="btn btn-outline-secondary">← Back</a>
        </div>
    </div>

    <?php if ($message !== ""): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card metallic-card mb-4">
        <div class="card-body">
            <h3 class="section-title mb-3">Submit Leave Request</h3>
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php while ($emp = $employees->fetch_assoc()): ?>
                                <option value="<?= (int) $emp['id'] ?>"><?= htmlspecialchars($emp['full_name']) ?> (<?= htmlspecialchars($emp['email']) ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Leave Type</label>
                        <select name="leave_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="Sick">Sick</option>
                            <option value="Vacation">Vacation</option>
                            <option value="Casual">Casual</option>
                            <option value="Unpaid">Unpaid</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Optional reason"></textarea>
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-metal">Submit Leave</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card metallic-card">
        <div class="card-body">
            <h3 class="section-title mb-3">Recent Leave Requests</h3>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($leaveRecords->num_rows > 0): ?>
                        <?php while ($leave = $leaveRecords->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($leave['full_name'] ?? 'Unknown') ?></strong><br>
                                    <small><?= htmlspecialchars($leave['email'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($leave['leave_type']) ?></td>
                                <td><?= htmlspecialchars($leave['start_date']) ?> to <?= htmlspecialchars($leave['end_date']) ?></td>
                                <td>
                                    <span class="department-badge"><?= htmlspecialchars($leave['status']) ?></span>
                                </td>
                                <td>
                                    <?php if ($leave['status'] === 'Pending'): ?>
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="leave_id" value="<?= (int) $leave['id'] ?>">
                                            <button type="submit" name="status" value="Approved" class="btn btn-sm btn-metal">Approve</button>
                                            <button type="submit" name="status" value="Rejected" class="btn btn-sm btn-outline-secondary">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-secondary">Handled</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">No leave records found.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<footer class="page-footer text-center text-secondary">Employee Management System &copy; 2026</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const body = document.body;
const toggle = document.getElementById('themeToggle');
if (localStorage.getItem('ems-theme') === 'dark') {
    body.classList.add('dark-theme');
    toggle.textContent = '☀️ Light';
}
toggle.addEventListener('click', () => {
    body.classList.toggle('dark-theme');
    const darkMode = body.classList.contains('dark-theme');
    localStorage.setItem('ems-theme', darkMode ? 'dark' : 'light');
    toggle.textContent = darkMode ? '☀️ Light' : '🌙 Dark';
});
</script>
</body>
</html>
