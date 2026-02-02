<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Attachment Model
 *
 * @property string $id
 * @property string $supervisionId
 * @property string $filename
 * @property string $fileUrl
 * @property string|null $fileType
 * @property int $fileSize
 * @property \Illuminate\Support\Carbon|null $uploadedAt
 * @property-read Supervision $supervision
 * @property-read string $fileSizeFormatted
 */
class Attachment extends Model
{
    use HasFactory;

    protected $table = 'attachment';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'supervisionId',
        'filename',
        'fileUrl',
        'fileType',
        'fileSize',
        'uploadedAt',
    ];

    protected $casts = [
        'uploadedAt' => 'datetime',
        'fileSize' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::ulid();
            }
        });
    }

    public function supervision(): BelongsTo
    {
        return $this->belongsTo(Supervision::class, 'supervisionId');
    }

    /**
     * Get formatted file size (e.g., "1.5 MB")
     *
     * @return string Formatted file size
     */
    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = (float) $this->fileSize;
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Check if attachment is an image
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return $this->fileType !== null && str_starts_with($this->fileType, 'image/');
    }

    /**
     * Check if attachment is a PDF
     *
     * @return bool
     */
    public function isPdf(): bool
    {
        return $this->fileType === 'application/pdf';
    }

    /**
     * Get the storage disk to use for this attachment
     */
    protected function getStorageDisk(): string
    {
        return config('filesystems.default', 'local');
    }

    /**
     * Check if the file exists in storage
     */
    public function fileExists(): bool
    {
        if (empty($this->fileUrl)) {
            return false;
        }

        try {
            $disk = $this->getStorageDisk();

            return Storage::disk($disk)->exists($this->fileUrl);
        } catch (\Exception $e) {
            Log::warning("Failed to check file existence for attachment {$this->id}", [
                'attachment_id' => $this->id,
                'file_url' => $this->fileUrl,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get the public URL for the file
     * Returns null if file doesn't exist
     */
    public function getUrl(): ?string
    {
        if (! $this->fileExists()) {
            return null;
        }

        try {
            $disk = $this->getStorageDisk();

            // For 'public' disk, use Storage::url()
            if ($disk === 'public') {
                return Storage::disk($disk)->url($this->fileUrl);
            }

            // For 'local' disk, need to use storage:link
            if ($disk === 'local') {
                // Check if symbolic link exists
                $publicPath = public_path('storage');
                if (is_link($publicPath) || file_exists($publicPath)) {
                    return Storage::disk('public')->url($this->fileUrl);
                }
                // Fallback: use asset() if file is in public storage
                if (str_starts_with($this->fileUrl, 'public/')) {
                    return asset('storage/' . str_replace('public/', '', $this->fileUrl));
                }
            }

            // For S3 or other cloud storage
            return Storage::disk($disk)->url($this->fileUrl);
        } catch (\Exception $e) {
            Log::error("Failed to get URL for attachment", [
                'attachment_id' => $this->id,
                'file_url' => $this->fileUrl,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get a safe URL that checks file existence first
     * Returns placeholder URL if file doesn't exist
     */
    public function getSafeUrl(): string
    {
        $url = $this->getUrl();

        if ($url === null) {
            // Return placeholder or default image
            return asset('images/file-not-found.png'); // You can create this placeholder
        }

        return $url;
    }

    /**
     * Get download URL (for force download)
     */
    public function getDownloadUrl(): ?string
    {
        if (! $this->fileExists()) {
            return null;
        }

        try {
            $disk = $this->getStorageDisk();

            return route('attachments.download', $this->id);
        } catch (\Exception $e) {
            Log::error("Failed to get download URL for attachment", [
                'attachment_id' => $this->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
