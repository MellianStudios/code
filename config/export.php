<?php

return [
    'products' => [
        'export_interval' => env('PRODUCT_EXPORT_INTERVAL', '1440'),
        'namespace_of_exports' => '\App\Exports\Products\ThirdParty\\',
        'count_per_chunk' => env('PRODUCT_EXPORT_COUNT_PER_CHUNK', 50000),
    ],
];
