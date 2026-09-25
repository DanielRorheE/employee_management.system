<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';

function jsonResponse(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $pdo = getPDO();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $employeeId = (int) ($input['employee_id'] ?? 0);
        $leaveType = trim((string) ($input['leave_type'] ?? ''));
        $startDate = trim((string) ($input['start_date'] ?? ''));
        $endDate = trim((string) ($input['end_date'] ?? ''));
        $reason = trim((string) ($input['reason'] ?? ''));

        $allowedTypes = ['Sick', 'Vacation', 'Casual', 'Unpaid'];

        if ($employeeId <= 0 || !in_array($leaveType, $allowedTypes, true) || $startDate === '' || $endDate === '') {
            jsonResponse(400, ['success' => false, 'message' => 'Invalid leave request data.']);
        }

        if (strtotime($endDate) < strtotime($startDate)) {
            jsonResponse(400, ['success' => false, 'message' => 'End date cannot be earlier than start date.']);
        }

        $employeeExists = $pdo->prepare('SELECT id FROM employees WHERE id = ? AND deleted_at IS NULL LIMIT 1');
        $employeeExists->execute([$employeeId]);
        if (!$employeeExists->fetch()) {
            jsonResponse(404, ['success' => false, 'message' => 'Employee not found.']);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO leaves (employee_id, leave_type, start_date, end_date, reason, status, applied_at) VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$employeeId, $leaveType, $startDate, $endDate, $reason, 'Pending']);

        jsonResponse(201, ['success' => true, 'message' => 'Leave request submitted successfully.']);
    }

    if ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $leaveId = (int) ($input['leave_id'] ?? 0);
        $status = trim((string) ($input['status'] ?? ''));

        if ($leaveId <= 0 || !in_array($status, ['Approved', 'Rejected'], true)) {
            jsonResponse(400, ['success' => false, 'message' => 'Invalid leave approval request.']);
        }

        $stmt = $pdo->prepare('UPDATE leaves SET status = ? WHERE id = ?');
        $stmt->execute([$status, $leaveId]);

        if ($stmt->rowCount() === 0) {
            jsonResponse(404, ['success' => false, 'message' => 'Leave request not found.']);
        }

        jsonResponse(200, ['success' => true, 'message' => 'Leave request updated successfully.']);
    }

    jsonResponse(405, ['success' => false, 'message' => 'Method not allowed.']);
} catch (PDOException $e) {
    jsonResponse(500, ['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    jsonResponse(500, ['success' => false, 'message' => 'Unexpected error: ' . $e->getMessage()]);
}
