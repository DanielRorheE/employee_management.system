<?php
include "db.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET["id"]);

$stmt = $conn->prepare(
    "SELECT * FROM employees WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$employee = $result->fetch_assoc();

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

        $update = $conn->prepare(
            "UPDATE employees
             SET full_name = ?,
                 position = ?,
                 email = ?,
                 department = ?
             WHERE id = ?"
        );

        $update->bind_param(
            "ssssi",
            $full_name,
            $position,
            $email,
            $department,
            $id
        );

        if ($update->execute()) {

            header(
                "Location: index.php?message=" .
                urlencode("Employee updated successfully!")
            );

            exit();

        } else {

            $error = "Unable to update employee.";

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

    <title>Edit Employee</title>

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

        <h2>Edit Employee</h2>

        <p class="text-secondary">
            Update employee information.
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
                    value="<?= htmlspecialchars($employee['full_name']) ?>"
                    required>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Position *
                </label>

                <input
                    type="text"
                    name="position"
                    class="form-control"
                    value="<?= htmlspecialchars($employee['position']) ?>"
                    required>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Email *
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    value="<?= htmlspecialchars($employee['email']) ?>"
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

                    <?php
                    $departments = [
                        "IT",
                        "Human Resources",
                        "Finance",
                        "Marketing",
                        "Operations"
                    ];
                    ?>

                    <?php foreach ($departments as $dept): ?>

                        <option
                            value="<?= $dept ?>"
                            <?= $employee['department'] == $dept ? 'selected' : '' ?>>

                            <?= $dept ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="d-flex gap-2">

                <button
                    type="submit"
                    class="btn btn-metal">
                    Update Employee
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
