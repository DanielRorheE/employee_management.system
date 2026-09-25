<?php

include "db.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: manage_employee.php");
    exit();
}

$id = intval($_GET["id"]);

$employeeStmt = $conn->prepare("SELECT full_name FROM employees WHERE id = ? LIMIT 1");
$employeeStmt->bind_param("i", $id);
$employeeStmt->execute();
$employeeResult = $employeeStmt->get_result();
$employeeName = $employeeResult->num_rows > 0 ? $employeeResult->fetch_assoc()['full_name'] : 'Employee';
$employeeStmt->close();

$stmt = $conn->prepare(
    "UPDATE employees SET deleted_at = NULL WHERE id = ? AND deleted_at IS NOT NULL"
);
$stmt->bind_param("i", $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    log_employee_history($id, $employeeName, 'restored', 'Employee was restored from deleted records');
    header("Location: manage_employee.php?message=" . urlencode("Deletion undone successfully!"));
} else {
    header("Location: manage_employee.php?message=" . urlencode("Nothing to undo."));
}

exit();

?>
