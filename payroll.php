<?php
include "db.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "create";

    if ($action === "update") {
        $payrollId = (int) ($_POST["payroll_id"] ?? 0);
        $status = trim($_POST["status"] ?? "");

        if ($payrollId <= 0 || !in_array($status, ["Processed", "Paid"], true)) {
            $error = "Invalid payroll update request.";
        } else {
            $stmt = $conn->prepare("UPDATE payroll SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $payrollId);

            if ($stmt->execute()) {
                $message = "Payroll status updated successfully.";
            } else {
                $error = "Unable to update payroll status.";
            }
        }
    } else {
        $employeeId = (int) ($_POST["employee_id"] ?? 0);
        $startDate = trim($_POST["pay_period_start"] ?? "");
        $endDate = trim($_POST["pay_period_end"] ?? "");
        $baseSalary = (float) ($_POST["base_salary"] ?? 0);
        $allowances = (float) ($_POST["allowances"] ?? 0);
        $deductions = (float) ($_POST["deductions"] ?? 0);

        if ($employeeId <= 0 || $startDate === "" || $endDate === "") {
            $error = "Please fill in all required payroll fields.";
        } elseif (strtotime($endDate) < strtotime($startDate)) {
            $error = "Pay period end date cannot be earlier than the start date.";
        } else {
            $employeeCheck = $conn->prepare("SELECT id FROM employees WHERE id = ? AND deleted_at IS NULL LIMIT 1");
            $employeeCheck->bind_param("i", $employeeId);
            $employeeCheck->execute();
            $employeeResult = $employeeCheck->get_result();

            if ($employeeResult->num_rows === 0) {
                $error = "Selected employee was not found.";
            } else {
                $netPay = round($baseSalary + $allowances - $deductions, 2);

                $check = $conn->prepare(
                    "SELECT id FROM payroll WHERE employee_id = ? AND pay_period_start = ? AND pay_period_end = ? LIMIT 1"
                );
                $check->bind_param("iss", $employeeId, $startDate, $endDate);
                $check->execute();
                $existing = $check->get_result();

                if ($existing->num_rows > 0) {
                    $error = "Payroll already exists for this employee and pay period.";
                } else {
                    $stmt = $conn->prepare(
                        "INSERT INTO payroll (employee_id, pay_period_start, pay_period_end, base_salary, allowances, deductions, net_pay, status, payment_date)
                         VALUES (?, ?, ?, ?, ?, ?, ?, 'Draft', NULL)"
                    );
                    $stmt->bind_param("issdddd", $employeeId, $startDate, $endDate, $baseSalary, $allowances, $deductions, $netPay);

                    if ($stmt->execute()) {
                        $message = "Payroll record created successfully.";
                    } else {
                        $error = "Unable to create payroll record.";
                    }
                }
            }
        }
    }
}

$employees = $conn->query("SELECT id, full_name, email FROM employees WHERE deleted_at IS NULL ORDER BY full_name ASC");
$payrollRecords = $conn->query(
    "SELECT p.*, e.full_name, e.email
     FROM payroll p
     LEFT JOIN employees e ON e.id = p.employee_id
     ORDER BY p.pay_period_end DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll</title>
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
            <span class="dashboard-pill">Payroll</span>
            <h2 class="mb-1">Payroll Records</h2>
            <p class="text-secondary mb-0">Create and manage employee payroll entries.</p>
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
            <h3 class="section-title mb-3">Create Payroll</h3>
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php while ($emp = $employees->fetch_assoc()): ?>
                                <option value="<?= (int) $emp['id'] ?>"><?= htmlspecialchars($emp['full_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="pay_period_start" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="pay_period_end" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Base Salary</label>
                        <input type="number" step="0.01" min="0" name="base_salary" class="form-control" value="0" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Allowances</label>
                        <input type="number" step="0.01" min="0" name="allowances" class="form-control" value="0" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Deductions</label>
                        <input type="number" step="0.01" min="0" name="deductions" class="form-control" value="0" required>
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-metal">Create Payroll</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card metallic-card">
        <div class="card-body">
            <h3 class="section-title mb-3">Payroll Summary</h3>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Period</th>
                        <th>Base</th>
                        <th>Net Pay</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($payrollRecords->num_rows > 0): ?>
                        <?php while ($payroll = $payrollRecords->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($payroll['full_name'] ?? 'Unknown') ?></strong><br>
                                    <small><?= htmlspecialchars($payroll['email'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($payroll['pay_period_start']) ?> to <?= htmlspecialchars($payroll['pay_period_end']) ?></td>
                                <td>$<?= number_format((float) $payroll['base_salary'], 2) ?></td>
                                <td>$<?= number_format((float) $payroll['net_pay'], 2) ?></td>
                                <td><span class="department-badge"><?= htmlspecialchars($payroll['status']) ?></span></td>
                                <td>
                                    <?php if ($payroll['status'] !== 'Paid'): ?>
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="payroll_id" value="<?= (int) $payroll['id'] ?>">
                                            <button type="submit" name="status" value="Processed" class="btn btn-sm btn-metal">Process</button>
                                            <button type="submit" name="status" value="Paid" class="btn btn-sm btn-outline-secondary">Paid</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-secondary">Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">No payroll records found.</td>
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
