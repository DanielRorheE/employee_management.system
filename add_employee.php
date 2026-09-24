<?php
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST["full_name"]);
    $position = trim($_POST["position"]);
    $email = trim($_POST["email"]);
    $department = trim($_POST["department"]);

    if (
        empty($full_name) ||
        empty($position) ||
        empty($email) ||
        empty($department)
    ) {

        $error = "Please fill in all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO employees
            (full_name, position, email, department)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssss",
            $full_name,
            $position,
            $email,
            $department
        );

        if ($stmt->execute()) {

            header(
                "Location: index.php?message=" .
                urlencode("Employee added successfully!")
            );

            exit();

        } else {

            $error = "Something went wrong. Please try again.";

        }

        $stmt->close();
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

    <div class="container">

        <a
            href="index.php"
            class="navbar-brand">
            Employee Management System
        </a>

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

            <div class="d-flex gap-2">

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

</body>
</html>
