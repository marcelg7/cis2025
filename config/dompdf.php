<?php

return [
    'options' => [
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
        'dpi' => 150,
        'defaultFont' => 'sans-serif',
        'memory_limit' => '512M',
        'chroot' => base_path(),
        'isPhpEnabled' => true,
        'tempDir' => storage_path('app/temp'),
    ],
];