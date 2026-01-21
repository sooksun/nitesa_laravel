<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class School extends Model
{
    use HasFactory;

    protected $table = 'school';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'code',
        'name',
        'province',
        'district',
        'subDistrict',
        'address',
        'phone',
        'email',
        'principalName',
        'studentCount',
        'teacherCount',
        'networkGroup',
        'networkGroupId',
    ];

    protected $casts = [
        'studentCount' => 'integer',
        'teacherCount' => 'integer',
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


    public function networkGroupRelation(): BelongsTo
    {
        return $this->belongsTo(NetworkGroup::class, 'networkGroupId');
    }

    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, '_supervisorschools', 'A', 'B');
    }

    public function supervisions(): HasMany
    {
        return $this->hasMany(Supervision::class, 'schoolId');
    }

    public function improvements(): HasMany
    {
        return $this->hasMany(Improvement::class, 'schoolId');
    }

    public function getLastSupervisionAttribute()
    {
        return $this->supervisions()->latest('date')->first();
    }

    public function getSupervisionCountAttribute(): int
    {
        return $this->supervisions()->count();
    }
}
