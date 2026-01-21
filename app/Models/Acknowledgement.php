<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Acknowledgement extends Model
{
    use HasFactory;

    protected $table = 'acknowledgement';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'supervisionId',
        'acknowledgedBy',
        'acknowledgedAt',
        'comment',
    ];

    protected $casts = [
        'acknowledgedAt' => 'datetime',
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
}
