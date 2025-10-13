<?php

return [
    'temporary_file_upload' => [
        // set in KILOBYTES; 1024000 ≈ 1 GB
        'rules' => ['file', 'mimetypes:video/*,image/*', 'max:1024000'],
        'directory' => 'livewire-tmp',
        'disk' => null,
        'preview_mimes' => ['image/*', 'pdf'],
        'max_upload_time' => 5,
    ],
];
