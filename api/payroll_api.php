<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';

function jsonResponse(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

function calculateNetPay(float $baseSalary, float $allowances, float $deductions): float {
    return round($baseSalary + $allowances - $deductions, 2);
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
        $payPeriodStart = trim((string) ($input['pay_period_start'] ?? ''));
        $payPeriodEnd = trim((string) ($input['pay_period_end'] ?? ''));
        $baseSalary = (float) ($input['base_salary'] ?? 0);
        $allowances = (float) ($input['allowances'] ?? 0);
        $deductions = (float) ($input['deductions'] ?? 0);

        if ($employeeId <= 0 || $payPeriodStart === '' || $payPeriodEnd === '') {
            jsonResponse(400, ['success' => false, 'message' => 'Invalid payroll payload.']);
        }

        $employeeCheck = $pdo->prepare('SELECT id FROM employees WHERE id = ? AND deleted_at IS NULL LIMIT 1');
        $employeeCheck->execute([$employeeId]);
        if (!$employeeCheck->fetch()) {
            jsonResponse(404, ['success' => false, 'message' => 'Employee not found.']);
        }

        $netPay = calculateNetPay($baseSalary, $allowances, $deductions);

        $check = $pdo->prepare('SELECT id FROM payroll WHERE employee_id = ? AND pay_period_start = ? AND pay_period_end = ? LIMIT 1');
        $check->execute([$employeeId, $payPeriodStart, $payPeriodEnd]);
        if ($check->fetch()) {
            jsonResponse(409, ['success' => false, 'message' => 'Payroll already generated for this period.']);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO payroll (employee_id, pay_period_start, pay_period_end, base_salary, allowances, deductions, net_pay, status, payment_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL)'
        );
        $stmt->execute([$employeeId, $payPeriodStart, $payPeriodEnd, $baseSalary, $allowances, $deductions, $netPay, 'Draft']);

        jsonResponse(201, [
            'success' => true,
            'message' => 'Payroll record created successfully.',
            'net_pay' => number_format($netPay, 2, '.', ''),
        ]);
    }

    if ($method === 'GET') {
        $stmt = $pdo->query(
            'SELECT p.*, e.first_name, e.last_name FROM payroll p LEFT JOIN employees e ON e.id = p.employee_id WHERE e.deleted_at IS NULL ORDER BY p.pay_period_end DESC'
        );
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'payroll' => $records], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return;
    }

    jsonResponse(405, ['success' => false, 'message' => 'Method not allowed.']);
} catch (PDOException $e) {
    jsonResponse(500, ['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    jsonResponse(500, ['success' => false, 'message' => 'Unexpected error: ' . $e->getMessage()]);
}
