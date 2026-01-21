<?php

namespace App\Models;

use App\Enums\PolicyType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Policy extends Model
{
    use HasFactory;

    protected $table = 'policy';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'type',
        'code',
        'title',
        'description',
        'isActive',
    ];

    protected $casts = [
        'type' => PolicyType::class,
        'isActive' => 'boolean',
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


    public function ministerSupervisions(): HasMany
    {
        return $this->hasMany(Supervision::class, 'ministerPolicyId');
    }

    public function obecSupervisions(): HasMany
    {
        return $this->hasMany(Supervision::class, 'obecPolicyId');
    }

    public function areaSupervisions(): HasMany
    {
        return $this->hasMany(Supervision::class, 'areaPolicyId');
    }

    public function scopeActive($query)
    {
        return $query->where('isActive', true);
    }

    public function scopeByType($query, PolicyType $type)
    {
        return $query->where('type', $type);
    }
}
