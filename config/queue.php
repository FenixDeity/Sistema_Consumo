<?php

return [
    'default' => env('QUEUE_CONNECTION', 'sync'),
    'connections' => [
        'sync' => ['driver' => 'sync'],
    ],
    'failed' => ['driver' => 'database-uuids', 'database' => null, 'table' => 'failed_jobs'],
];
