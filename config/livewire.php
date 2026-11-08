<?php

return [

    // Keep temporary uploads on the private disk: with FILESYSTEM_DISK=public they would land under
    // /storage/livewire-tmp with the client's original extension and be reachable from the web.
    'temporary_file_upload' => [
        'disk' => 'local',
        'rules' => [
            'required',
            'file',
            'max:12288',
            'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,csv',
        ],
        'directory' => null,
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 5,
        'cleanup' => true,
    ],

];
