<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;

class AttachmentController extends Controller
{
    private const INLINE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
    ];

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

    public function download(Attachment $attachment)
    {
        return $this->respond($attachment, false);
    }

    public function view(Attachment $attachment)
    {
        return $this->respond($attachment, true);
    }

    protected function respond(Attachment $attachment, bool $inline)
    {
        $this->authorizeAttachment($attachment);

        $disk = $attachment->getStorageDisk();
        $filePath = $attachment->fileUrl;

        if (empty($filePath) || ! Storage::disk($disk)->exists($filePath)) {
            abort(404, 'ไฟล์ไม่พบในระบบ');
        }

        try {
            $mimeType = Storage::disk($disk)->mimeType($filePath) ?: 'application/octet-stream';
            // Anything that is not a plain image/PDF (e.g. HTML, SVG, scripts) is never rendered by the browser.
            $inline = $inline && in_array($mimeType, self::INLINE_MIME_TYPES, true);

            $filename = Attachment::sanitizeFilename($attachment->filename);
            $disposition = HeaderUtils::makeDisposition(
                $inline ? HeaderUtils::DISPOSITION_INLINE : HeaderUtils::DISPOSITION_ATTACHMENT,
                $filename,
                preg_replace('/[^\x20-\x7E]/', '_', $filename),
            );

            return response(Storage::disk($disk)->get($filePath), 200, [
                'Content-Type' => $inline ? $mimeType : 'application/octet-stream',
                'Content-Disposition' => $disposition,
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to serve attachment {$attachment->id}: " . $e->getMessage());
            abort(500, 'เกิดข้อผิดพลาดในการเปิดไฟล์');
        }
    }
}
