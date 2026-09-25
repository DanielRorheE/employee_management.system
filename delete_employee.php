<?php

include "db.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: records_employee.php");
    exit();
}

$id = intval($_GET["id"]);

$stmt = $conn->prepare(
    "UPDATE employees SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL"
);
$stmt->bind_param("i", $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    header("Location: records_employee.php?message=" . urlencode("Employee deleted successfully!"));
} else {
    header("Location: records_employee.php?message=" . urlencode("Unable to delete employee."));
}

exit();

?>
