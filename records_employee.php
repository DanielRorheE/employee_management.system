<?php
include "db.php";

$result = $conn->query("SELECT * FROM employees ORDER BY id DESC");

$message = "";

if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>View Employee Records</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<nav class="navbar navbar-dark metallic-navbar">
    <div class="container">
        <span class="navbar-brand mb-0 h1">
            Employee Management System
        </span>
    </div>
</nav>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Employee Records</h2>
            <p class="text-secondary mb-0">
                View employee information and details.
            </p>
        </div>
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

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="text-center py-4">
                                No employee records found.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <div class="mt-4">
    <a href="index.php" class="btn btn-outline-secondary">
        ← Back to Dashboard
    </a>
</div>

</div>



<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>
