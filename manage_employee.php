<?php
include "db.php";

$message = "";
$search = trim($_GET['search'] ?? "");
$departmentFilter = trim($_GET['department'] ?? "");

if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

$conditions = ["deleted_at IS NULL"];
$params = [];
$types = "";

if ($search !== "") {
    $searchTerm = "%" . $conn->real_escape_string($search) . "%";
    $conditions[] = "(full_name LIKE ? OR position LIKE ? OR email LIKE ? OR department LIKE ?)";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ssss";
}

if ($departmentFilter !== "") {
    $conditions[] = "department = ?";
    $params[] = $departmentFilter;
    $types .= "s";
}

$sql = "SELECT * FROM employees";
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY id DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$departmentOptions = $conn->query("SELECT DISTINCT department FROM employees WHERE deleted_at IS NULL ORDER BY department ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Employee Management System</title>

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

    <div class="mb-4">
        <div>
            <h2>Manage Employee Records</h2>
            <p class="text-secondary mb-0">
                Manage employee information
            </p>
        </div>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch gap-3 mb-3">
        <form method="GET" class="d-flex search-box flex-wrap" action="manage_employee.php">
            <input
                type="text"
                name="search"
                class="form-control"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Search employee"
            >
            <select name="department" class="form-select" style="max-width: 180px; border-radius: 0;">
                <option value="">All Departments</option>
                <?php while ($dept = $departmentOptions->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($dept['department']) ?>" <?= $departmentFilter === $dept['department'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['department']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-metal">Search</button>
            <?php if ($search !== "" || $departmentFilter !== ""): ?>
                <a href="manage_employee.php" class="btn btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($message != ""): ?>

        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>
        </div>

    <?php endif; ?>

    <div class="card metallic-card">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Position</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($result->num_rows > 0): ?>

                        <?php while ($employee = $result->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?= $employee['id'] ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($employee['full_name']) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($employee['position']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($employee['email']) ?>
                                </td>

                                <td>
                                    <span class="department-badge">
                                        <?= htmlspecialchars($employee['department']) ?>
                                    </span>
                                </td>

                                <td class="text-center">

                                    <a
                                        href="edit_employee.php?id=<?= $employee['id'] ?>"
                                        class="btn btn-sm btn-outline-secondary">
                                        Edit
                                    </a>

                                    <a
                                        href="delete_employee.php?id=<?= $employee['id'] ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this employee?');">
                                        Delete
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="text-center py-4">
                                No employee records found.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <div class="d-flex justify-content-end mt-3 mb-4">
        <a href="add_employee.php" class="btn btn-metal">
            + Add Employee
        </a>
    </div>

    <div class="mt-2">
        <a href="index.php" class="btn btn-outline-secondary">
            ← Back to Dashboard
        </a>
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
