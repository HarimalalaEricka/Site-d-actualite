<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Database.php';
require_once dirname(__DIR__) . '/app/Services/ViewCounterService.php';

use App\Services\ViewCounterService;

ViewCounterService::aggregateViewStats();

echo "Aggregation terminee\n";