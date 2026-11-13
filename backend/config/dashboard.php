<?php

declare(strict_types=1);

return [
    // Referência de acompanhamento editável por MEI/ano; não representa enquadramento legal.
    'default_annual_ceiling' => env('MEI_DEFAULT_ANNUAL_CEILING', '81000.00'),
    'warning_percent' => 80,
    'danger_percent' => 95,
];
