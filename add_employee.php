<?php
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $position = trim($_POST["position"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $department = trim($_POST["department"] ?? "");

    if (
        $full_name === "" ||
        $position === "" ||
        $email === "" ||
        $department === ""
    ) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $existing = $conn->prepare("SELECT id FROM employees WHERE email = ? AND deleted_at IS NULL LIMIT 1");
        $existing->bind_param("s", $email);
        $existing->execute();
        $existingResult = $existing->get_result();

        if ($existingResult->num_rows > 0) {
            $error = "This email address is already registered.";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO employees (full_name, position, email, department, created_at)
                 VALUES (?, ?, ?, ?, NOW())"
            );

            $stmt->bind_param("ssss", $full_name, $position, $email, $department);

            if ($stmt->execute()) {
                header("Location: manage_employee.php?message=" . urlencode("Employee added successfully!"));
                exit();
            }

            $error = "Something went wrong. Please try again.";
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Add Employee</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="assets/style.css">

</head>

<body>

<nav class="navbar navbar-dark metallic-navbar">

    <div class="container d-flex justify-content-between align-items-center">

        <a
            href="index.php"
            class="navbar-brand">
            Employee Management System
        </a>

        <button id="themeToggle" class="theme-toggle" type="button">🌙 Dark</button>

    </div>

</nav>

<div class="container mt-5">

    <div class="form-container">

        <h2>Add Employee</h2>

        <p class="text-secondary">
            Enter the employee's information below.
        </p>

        <?php if ($error != ""): ?>

            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">

                <label class="form-label">
                    Full Name *
                </label>

                <input
                    type="text"
                    name="full_name"
                    class="form-control"
                    required>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Position *
                </label>

                <select
                    name="position"
                    class="form-select"
                    required>

                    <option value="">
                        Select Position
                    </option>

                    <option value="CEO">
                        CEO
                    </option>

                    <option value="Manager">
                        Manager
                    </option>

                    <option value="Supervisor">
                        Supervisor
                    </option>

                    <option value="Senior Staff">
                        Senior Staff
                    </option>

                    <option value="Staff">
                        Staff
                    </option>

                    <option value="Intern">
                        Intern
                    </option>

                </select>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Email *
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    required>

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Department *
                </label>

                <select
                    name="department"
                    class="form-select"
                    required>

                    <option value="">
                        Select Department
                    </option>

                    <option value="Information Technology (IT)">
                        Information Technology (IT)
                    </option>

                    <option value="Human Resources (HR)">
                        Human Resources (HR)
                    </option>

                    <option value="Finance & Accounting">
                        Finance & Accounting
                    </option>

                    <option value="Sales & Marketing">
                        Sales & Marketing
                    </option>

                    <option value="Operations">
                        Operations
                    </option>

                    <option value="Customer Service">
                        Customer Service
                    </option>

                </select>

            </div>

            <div class="d-flex justify-content-end gap-2">

                <button
                    type="submit"
                    class="btn btn-metal">
                    Save Employee
                </button>

                <a
                    href="index.php"
                    class="btn btn-outline-secondary">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

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
