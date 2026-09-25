<?php

include "db.php";

$totalEmployees = $conn->query(
    "SELECT COUNT(*) AS total FROM employees WHERE deleted_at IS NULL"
)->fetch_assoc()['total'];

$totalDepartments = $conn->query(
    "SELECT COUNT(DISTINCT department) AS total FROM employees WHERE deleted_at IS NULL"
)->fetch_assoc()['total'];

$totalPositions = $conn->query(
    "SELECT COUNT(DISTINCT position) AS total FROM employees WHERE deleted_at IS NULL"
)->fetch_assoc()['total'];

$recentEmployees = $conn->query(
    "SELECT full_name, position, department FROM employees WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 5"
);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Dashboard</title>

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            rel="stylesheet"
        >

        <link rel="stylesheet" href="assets/style.css">
    </head>

<body>

<nav class="navbar navbar-dark metallic-navbar">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="index.php" class="navbar-brand mb-0 h1">
            Employee Management System
        </a>
        <button id="themeToggle" class="theme-toggle" type="button">🌙 Dark</button>
    </div>
</nav>

<div class="container mt-5">
    <div class="text-center mb-5">
        <span class="dashboard-pill">Overview</span>
        <h1>Dashboard</h1>
        <p class="text-secondary mb-0">
            Overview of employee records, departments, and positions.
        </p>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card metallic-card stat-card h-100 text-center">
                <div class="card-body">
                    <div class="stat-icon">👥</div>
                    <h5 class="card-title">Total Employees</h5>
                    <h2 class="display-5 fw-bold mb-0"><?= $totalEmployees ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card metallic-card stat-card h-100 text-center">
                <div class="card-body">
                    <div class="stat-icon">🏢</div>
                    <h5 class="card-title">Departments</h5>
                    <h2 class="display-5 fw-bold mb-0"><?= $totalDepartments ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card metallic-card stat-card h-100 text-center">
                <div class="card-body">
                    <div class="stat-icon">📌</div>
                    <h5 class="card-title">Positions</h5>
                    <h2 class="display-5 fw-bold mb-0"><?= $totalPositions ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card metallic-card h-100">
                <div class="card-body">
                    <h3 class="section-title mb-3">Quick Actions</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="add_employee.php" class="quick-action text-center">
                                <div class="quick-action-icon">➕</div>
                                <h5 class="mb-1">Add Employee</h5>
                                <small class="text-secondary">Create a new record</small>
                            </a>
                        </div>

                        <div class="col-md-6">
                            <a href="manage_employee.php" class="quick-action text-center">
                                <div class="quick-action-icon">📝</div>
                                <h5 class="mb-1">Manage Staff</h5>
                                <small class="text-secondary">Edit or delete entries</small>
                            </a>
                        </div>

                        <div class="col-md-6">
                            <a href="records_employee.php" class="quick-action text-center">
                                <div class="quick-action-icon">📄</div>
                                <h5 class="mb-1">View Records</h5>
                                <small class="text-secondary">Check employee list</small>
                            </a>
                        </div>

                        <div class="col-md-6">
                            <a href="leave.php" class="quick-action text-center">
                                <div class="quick-action-icon">🗓️</div>
                                <h5 class="mb-1">Leave Requests</h5>
                                <small class="text-secondary">Approve or reject leave</small>
                            </a>
                        </div>

                        <div class="col-md-6">
                            <a href="payroll.php" class="quick-action text-center">
                                <div class="quick-action-icon">💰</div>
                                <h5 class="mb-1">Payroll</h5>
                                <small class="text-secondary">Create and process pays</small>
                            </a>
                        </div>

                        <div class="col-md-6">
                            <a href="index.php" class="quick-action text-center">
                                <div class="quick-action-icon">🏠</div>
                                <h5 class="mb-1">Dashboard</h5>
                                <small class="text-secondary">Back to overview</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card metallic-card h-100">
                <div class="card-body">
                    <h3 class="section-title mb-3">Recent Employees</h3>
                    <ul class="list-group list-group-flush">
                        <?php while ($employee = $recentEmployees->fetch_assoc()): ?>
                            <li class="list-group-item px-0">
                                <strong><?= htmlspecialchars($employee['full_name']) ?></strong><br>
                                <small class="text-secondary"><?= htmlspecialchars($employee['position']) ?> • <?= htmlspecialchars($employee['department']) ?></small>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="page-footer text-center text-secondary">
    Employee Management System &copy; 2026
</footer>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>
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
