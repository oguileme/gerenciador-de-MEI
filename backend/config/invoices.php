<?php

declare(strict_types=1);

return [
    'xml_disk' => env('INVOICE_XML_DISK', 'invoice_xml'),
    'xml_max_kilobytes' => (int) env('INVOICE_XML_MAX_KB', 1024),
];
