<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\RoleService;

final class RoleController
{
    private RoleService $roleService;

    public function __construct(?RoleService $roleService = null)
    {
        $this->roleService = $roleService ?? new RoleService();
    }

    public function index(): array
    {
        $roles = $this->roleService->getAllRole();

        return array_map(
            static fn ($role) => $role->toArray(),
            $roles
        );
    }
}
