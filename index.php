<?php

include "db.php";

$totalEmployees = $conn->query(
    "SELECT COUNT(*) AS total FROM employees"
)->fetch_assoc()['total'];

$totalDepartments = $conn->query(
    "SELECT COUNT(DISTINCT department) AS total FROM employees"
)->fetch_assoc()['total'];

$totalPositions = $conn->query(
    "SELECT COUNT(DISTINCT position) AS total FROM employees"
)->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Dashboard</title>

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
    <div class="text-center mb-5"> 
        <h1>Dashboard</h1> 
        
        <p class="text-secondary"> 
            Overview of employee records, departments, and positions.
        </p> 
    </div> 
    

    <div class="row g-4 mb-5"> 

            <div class="col-md-4"> 
                <div class="card metallic-card h-100 text-center"> 
                    <div class="card-body"> 
                       <h5 class="card-title"> Total Employees </h5> 
                       <h2 class="display-5 fw-bold"> <?= $totalEmployees ?> </h2> 
                    </div> 
                </div> 
            </div> 
    

            <div class="col-md-4"> 
                <div class="card metallic-card h-100 text-center"> 
                    <div class="card-body"> <h5 class="card-title"> Departments </h5> 
                    <h2 class="display-5 fw-bold"> <?= $totalDepartments ?> </h2> 
                </div> 
            </div> 
        </div> 


            <div class="col-md-4"> 
                <div class="card metallic-card h-100 text-center"> 
                    <div class="card-body"> 
                        <h5 class="card-title"> Positions </h5> 
                        <h2 class="display-5 fw-bold"> <?= $totalPositions ?> </h2> 
                    </div> 
                </div> 
            </div> 
        </div> 


            <div class="text-center"> 
                <h3 class="mb-3"> Quick Actions </h3>
                    <div class="d-flex justify-content-center flex-wrap gap-3"> 
                        <a href="add_employee.php" class="btn btn-metal px-4"> + Add Employee </a> 
                        <a href="edit_employee.php" class="btn btn-outline-dark px-4"> Edit Employee </a> 
                        <a href="records_employee.php" class="btn btn-outline-dark px-4"> View Employees </a> 
                    </div> 
                </div> 
            </div>

<footer class="text-center mt-5 mb-4 text-secondary">
    Employee Management System &copy; 2026
</footer>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

