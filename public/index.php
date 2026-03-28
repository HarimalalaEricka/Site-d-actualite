<?php

declare(strict_types=1);

use App\Controllers\Admin\RoleController;

require_once dirname(__DIR__) . '/app/Core/Database.php';
require_once dirname(__DIR__) . '/app/Models/Role.php';
require_once dirname(__DIR__) . '/app/Services/RoleService.php';
require_once dirname(__DIR__) . '/app/Controllers/Admin/RoleController.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $controller = new RoleController();
    $roles = $controller->index();

    echo json_encode([
        'success' => true,
        'count' => count($roles),
        'data' => $roles,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
