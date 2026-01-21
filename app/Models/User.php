<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'image',
        'googleId',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'role' => Role::class,
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

    public function assignedSchools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, '_supervisorschools', 'B', 'A');
    }

    public function supervisions(): HasMany
    {
        return $this->hasMany(Supervision::class);
    }

    public function improvements(): HasMany
    {
        return $this->hasMany(Improvement::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::ADMIN;
    }

    public function isSupervisor(): bool
    {
        return $this->role === Role::SUPERVISOR;
    }

    public function isSchool(): bool
    {
        return $this->role === Role::SCHOOL;
    }

    public function isExecutive(): bool
    {
        return $this->role === Role::EXECUTIVE;
    }

    public function canManageSchool(School $school): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isSupervisor()) {
            return $this->assignedSchools()->where('schools.id', $school->id)->exists();
        }

        return false;
    }
}
