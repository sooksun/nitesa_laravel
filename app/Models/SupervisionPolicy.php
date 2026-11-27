<?php

namespace App\Models;

use App\Enums\PolicyLevel;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $supervisionId
 * @property string $policyId
 * @property PolicyLevel $level
 * @property int $position
 */
class SupervisionPolicy extends Pivot
{
    protected $table = 'supervisionpolicy';
    protected $keyType = 'string';
    public $incrementing = false;

    public const CREATED_AT = 'createdAt';
    public const UPDATED_AT = null;

    protected $casts = [
        'level' => PolicyLevel::class,
        'position' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::ulid();
            }
        });
    }
}
