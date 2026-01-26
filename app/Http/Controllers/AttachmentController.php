<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Download an attachment file
     */
    public function download(Attachment $attachment)
    {
        // Check if file exists
        if (! $attachment->fileExists()) {
            abort(404, 'ไฟล์ไม่พบในระบบ');
        }

        try {
            $disk = config('filesystems.default', 'local');
            $filePath = $attachment->fileUrl;

            // Check if file exists in storage
            if (! Storage::disk($disk)->exists($filePath)) {
                abort(404, 'ไฟล์ไม่พบในระบบ');
            }

            // Get file content
            $fileContent = Storage::disk($disk)->get($filePath);
            $mimeType = Storage::disk($disk)->mimeType($filePath) ?? $attachment->fileType ?? 'application/octet-stream';

            // Return file as download
            return response()->streamDownload(function () use ($fileContent) {
                echo $fileContent;
            }, $attachment->filename, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $attachment->filename . '"',
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to download attachment {$attachment->id}: " . $e->getMessage());
            abort(500, 'เกิดข้อผิดพลาดในการดาวน์โหลดไฟล์');
        }
    }

    /**
     * View an attachment file (inline)
     */
    public function view(Attachment $attachment)
    {
        // Check if file exists
        if (! $attachment->fileExists()) {
            abort(404, 'ไฟล์ไม่พบในระบบ');
        }

        try {
            $disk = config('filesystems.default', 'local');
            $filePath = $attachment->fileUrl;

            // Check if file exists in storage
            if (! Storage::disk($disk)->exists($filePath)) {
                abort(404, 'ไฟล์ไม่พบในระบบ');
            }

            // Get file content
            $fileContent = Storage::disk($disk)->get($filePath);
            $mimeType = Storage::disk($disk)->mimeType($filePath) ?? $attachment->fileType ?? 'application/octet-stream';

            // Return file as inline view
            return response($fileContent, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $attachment->filename . '"',
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to view attachment {$attachment->id}: " . $e->getMessage());
            abort(500, 'เกิดข้อผิดพลาดในการแสดงไฟล์');
        }
    }
}
