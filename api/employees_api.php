<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';

function jsonResponse(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function generateEmployeeCode(PDO $pdo): string {
    $prefix = 'EMP';
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees WHERE deleted_at IS NULL");
    $count = (int) $stmt->fetchColumn();
    return $prefix . str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
}

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $pdo = getPDO();

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $firstName = trim((string) ($input['first_name'] ?? ''));
        $lastName = trim((string) ($input['last_name'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $phone = trim((string) ($input['phone'] ?? ''));
        $hireDate = trim((string) ($input['hire_date'] ?? ''));
        $status = trim((string) ($input['status'] ?? 'Active'));
        $departmentId = (int) ($input['department_id'] ?? 0);
        $positionId = (int) ($input['position_id'] ?? 0);

        if ($firstName === '' || $lastName === '' || $email === '' || $hireDate === '' || $departmentId <= 0 || $positionId <= 0) {
            jsonResponse(400, ['success' => false, 'message' => 'Required fields are missing.']);
        }

        if (!isValidEmail($email)) {
            jsonResponse(400, ['success' => false, 'message' => 'Invalid email format.']);
        }

        if (!in_array($status, ['Active', 'Inactive', 'On Leave'], true)) {
            jsonResponse(400, ['success' => false, 'message' => 'Invalid employee status.']);
        }

        $checkEmail = $pdo->prepare('SELECT id FROM employees WHERE email = ? AND deleted_at IS NULL LIMIT 1');
        $checkEmail->execute([$email]);
        if ($checkEmail->fetch()) {
            jsonResponse(409, ['success' => false, 'message' => 'Employee email already exists.']);
        }

        $departmentCheck = $pdo->prepare('SELECT id FROM departments WHERE id = ? LIMIT 1');
        $departmentCheck->execute([$departmentId]);
        if (!$departmentCheck->fetch()) {
            jsonResponse(400, ['success' => false, 'message' => 'Invalid department selected.']);
        }

        $positionCheck = $pdo->prepare('SELECT id FROM positions WHERE id = ? LIMIT 1');
        $positionCheck->execute([$positionId]);
        if (!$positionCheck->fetch()) {
            jsonResponse(400, ['success' => false, 'message' => 'Invalid position selected.']);
        }

        $employeeCode = generateEmployeeCode($pdo);

        $stmt = $pdo->prepare(
            'INSERT INTO employees (employee_code, first_name, last_name, email, phone, hire_date, status, department_id, position_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );

        $stmt->execute([
            $employeeCode,
            $firstName,
            $lastName,
            $email,
            $phone,
            $hireDate,
            $status,
            $departmentId,
            $positionId
        ]);

        jsonResponse(201, [
            'success' => true,
            'message' => 'Employee created successfully.',
            'employee_id' => $pdo->lastInsertId(),
            'employee_code' => $employeeCode,
        ]);
    }

    if ($method === 'GET') {
        $listQuery = "
            SELECT e.*, d.name AS department_name, p.title AS position_title
            FROM employees e
            LEFT JOIN departments d ON d.id = e.department_id
            LEFT JOIN positions p ON p.id = e.position_id
            WHERE e.deleted_at IS NULL
            ORDER BY e.created_at DESC
        ";

        $stmt = $pdo->query($listQuery);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        jsonResponse(200, ['success' => true, 'employees' => $employees]);
    }

    if ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $employeeId = (int) ($input['id'] ?? 0);
        if ($employeeId <= 0) {
            jsonResponse(400, ['success' => false, 'message' => 'Employee ID is required.']);
        }

        $updates = [];
        $params = [];

        foreach (['first_name', 'last_name', 'email', 'phone', 'hire_date', 'status', 'department_id', 'position_id'] as $field) {
            if (array_key_exists($field, $input)) {
                $updates[] = "$field = ?";
                $params[] = $input[$field];
            }
        }

        if (empty($updates)) {
            jsonResponse(400, ['success' => false, 'message' => 'No fields to update.']);
        }

        $params[] = $employeeId;
        $sql = 'UPDATE employees SET ' . implode(', ', $updates) . ' WHERE id = ? AND deleted_at IS NULL';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        jsonResponse(200, ['success' => true, 'message' => 'Employee updated successfully.']);
    }

    if ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $employeeId = (int) ($input['id'] ?? ($_GET['id'] ?? 0));

        if ($employeeId <= 0) {
            jsonResponse(400, ['success' => false, 'message' => 'Employee ID is required.']);
        }

        $stmt = $pdo->prepare('UPDATE employees SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$employeeId]);

        if ($stmt->rowCount() === 0) {
            jsonResponse(404, ['success' => false, 'message' => 'Employee not found.']);
        }

        jsonResponse(200, ['success' => true, 'message' => 'Employee deleted successfully.']);
    }

    jsonResponse(405, ['success' => false, 'message' => 'Method not allowed.']);
} catch (PDOException $e) {
    jsonResponse(500, ['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    jsonResponse(500, ['success' => false, 'message' => 'Unexpected error: ' . $e->getMessage()]);
}
