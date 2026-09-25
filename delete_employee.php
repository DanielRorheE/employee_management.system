<?php

include "db.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: manage_employee.php");
    exit();
}

$id = intval($_GET["id"]);
$redirect = "manage_employee.php";
if (isset($_GET["return"]) && in_array($_GET["return"], ["manage_employee.php", "records_employee.php"], true)) {
    $redirect = $_GET["return"];
}

$employeeStmt = $conn->prepare("SELECT full_name FROM employees WHERE id = ? AND deleted_at IS NULL LIMIT 1");
$employeeStmt->bind_param("i", $id);
$employeeStmt->execute();
$employeeResult = $employeeStmt->get_result();
$employeeName = $employeeResult->num_rows > 0 ? $employeeResult->fetch_assoc()['full_name'] : 'Employee';
$employeeStmt->close();

$stmt = $conn->prepare(
    "UPDATE employees SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL"
);
$stmt->bind_param("i", $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    log_employee_history($id, $employeeName, 'deleted', 'Employee was removed from active records');
    header("Location: " . $redirect . "?message=" . urlencode("Employee deleted successfully!") . "&deleted_id=" . $id);
} else {
    header("Location: " . $redirect . "?message=" . urlencode("Unable to delete employee."));
}

exit();

?>
