<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';

try {
    $pdo = getPDO();

    $activeEmployees = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'Active' AND deleted_at IS NULL")->fetchColumn();
    $departments = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
    $positions = $pdo->query("SELECT COUNT(*) FROM positions")->fetchColumn();

    $recentEmployees = $pdo->query(
        "SELECT id, employee_code, first_name, last_name, created_at FROM employees WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 5"
    )->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'stats' => [
            'active_employees' => (int) $activeEmployees,
            'departments' => (int) $departments,
            'positions' => (int) $positions,
        ],
        'recent_employees' => $recentEmployees
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unexpected error: ' . $e->getMessage()]);
}
