<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImportController extends Controller
{
    /**
     * Download import template file
     */
    public function downloadTemplate(string $type): BinaryFileResponse
    {
        $templates = [
            'school' => 'school-import-template.xlsx',
            'schools' => 'school-import-template.xlsx',
            'policy' => 'policy-import-template.xlsx',
            'policies' => 'policy-import-template.xlsx',
            'network-group' => 'network-group-import-template.xlsx',
            'network_groups' => 'network-group-import-template.xlsx',
            'networkgroup' => 'network-group-import-template.xlsx',
        ];

        if (! isset($templates[$type])) {
            abort(404, 'Template not found');
        }

        $filename = $templates[$type];
        $filePath = public_path($filename);

        if (! file_exists($filePath)) {
            abort(404, 'Template file not found');
        }

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
