<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->fileSize;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isImage(): bool
    {
        return str_starts_with($this->fileType, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->fileType === 'application/pdf';
    }
}
