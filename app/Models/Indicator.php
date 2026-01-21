<?php

namespace App\Models;

use App\Enums\IndicatorLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Indicator extends Model
{
    use HasFactory;

    protected $table = 'indicator';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'supervisionId',
        'name',
        'level',
        'comment',
    ];

    protected $casts = [
        'level' => IndicatorLevel::class,
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

    public function getScoreAttribute(): int
    {
        return $this->level->score();
    }
}
