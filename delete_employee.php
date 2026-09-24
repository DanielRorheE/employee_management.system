<?php

include "db.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET["id"]);

$stmt = $conn->prepare(
    "DELETE FROM employees WHERE id = ?"
);

$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    header(
        "Location: index.php?message=" .
        urlencode("Employee deleted successfully!")
    );

} else {

    header(
        "Location: index.php?message=" .
        urlencode("Unable to delete employee.")
    );

}

exit();

?>
