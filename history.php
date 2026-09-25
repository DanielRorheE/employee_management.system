<?php
include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_history'])) {
    $conn->query("DELETE FROM employee_history");
    header('Location: history.php');
    exit();
}

$history = $conn->query(
    "SELECT * FROM employee_history ORDER BY created_at DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav class="navbar navbar-dark metallic-navbar">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="index.php" class="navbar-brand-wrap navbar-brand mb-0 h1">
            <span class="navbar-logo" aria-label="smile logo">☺</span>
            <span>Employee Management System</span>
        </a>
        <button id="themeToggle" class="theme-toggle" type="button">🌙 Dark</button>
    </div>
</nav>

<div class="container mt-5">
    <div class="mb-4">
        <h2>Employee History</h2>
        <p class="text-secondary mb-0">
            Recent employee additions, deletions, and restoration activity with timestamps.
        </p>
    </div>

    <div class="card metallic-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
                <h3 class="section-title mb-0">Activity Log</h3>

                <?php if ($history->num_rows > 0): ?>
                    <form method="POST" onsubmit="return confirm('Clear all employee history? This cannot be undone.');">
                        <button type="submit" name="clear_history" value="1" class="btn btn-danger btn-sm">
                            Clear History
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($history->num_rows > 0): ?>
                            <?php while ($entry = $history->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($entry['full_name']) ?></strong></td>
                                    <td>
                                        <span class="department-badge">
                                            <?= ucfirst(htmlspecialchars($entry['action'])) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($entry['details']) ?></td>
                                    <td>
                                        <?= date('F j, Y', strtotime($entry['created_at'])) ?><br>
                                        <small class="text-secondary"><?= date('g:i A', strtotime($entry['created_at'])) ?></small>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">No employee activity has been recorded yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="index.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
    </div>
</div>

<footer class="page-footer text-center text-secondary">
    Employee Management System &copy; 2026
</footer>

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
