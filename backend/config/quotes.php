<?php

return [
    // A data comercial deve acompanhar o dia civil brasileiro, não o timestamp UTC do banco.
    'timezone' => env('QUOTE_TIMEZONE', 'America/Sao_Paulo'),
];
