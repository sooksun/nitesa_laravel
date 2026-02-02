<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * Ensure the authenticated user can access this attachment's supervision.
     */
    protected function authorizeAttachment(Attachment $attachment): void
    {
        $attachment->loadMissing('supervision');

        if (! $attachment->supervision || ! auth()->user()?->canAccessSupervision($attachment->supervision)) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงไฟล์นี้');
        }
    }

    /**
     * Download an attachment file
     */
    public function download(Attachment $attachment)
    {
        $this->authorizeAttachment($attachment);

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
        $this->authorizeAttachment($attachment);

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
