<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 *
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property Role $role
 * @property string|null $image
 * @property string|null $avatar
 * @property string|null $googleId
 * @property bool $isActive
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property-read Collection<int, School> $assignedSchools
 * @property-read Collection<int, Supervision> $supervisions
 * @property-read Collection<int, Improvement> $improvements
 *
 * @method static Builder|User active()
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    protected $table = 'user';
    protected $keyType = 'string';
    public $incrementing = false;

    public const CREATED_AT = 'createdAt';
    public const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'role',
        'image',
        'avatar',
        'googleId',
        'isActive',
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

    /**
     * Check if user is admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === Role::ADMIN;
    }

    /**
     * Check if user is supervisor.
     *
     * @return bool
     */
    public function isSupervisor(): bool
    {
        return $this->role === Role::SUPERVISOR;
    }

    /**
     * Check if user is school.
     *
     * @return bool
     */
    public function isSchool(): bool
    {
        return $this->role === Role::SCHOOL;
    }

    /**
     * Check if user is executive.
     *
     * @return bool
     */
    public function isExecutive(): bool
    {
        return $this->role === Role::EXECUTIVE;
    }

    /**
     * Check if user can manage a specific school.
     *
     * @param School $school School to check
     * @return bool
     */
    public function canManageSchool(School $school): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isSupervisor()) {
            return $this->assignedSchools()
                ->where('schools.id', $school->id)
                ->exists();
        }

        return false;
    }

    /**
     * Check if user can access (view) a specific supervision.
     *
     * @param \App\Models\Supervision $supervision Supervision to check
     * @return bool
     */
    public function canAccessSupervision(\App\Models\Supervision $supervision): bool
    {
        if ($this->isAdmin() || $this->isExecutive()) {
            return true;
        }

        if ($this->isSupervisor()) {
            return $supervision->userId === $this->id
                || $this->assignedSchools()->where('schools.id', $supervision->schoolId)->exists();
        }

        if ($this->isSchool()) {
            return $supervision->status === \App\Enums\SupervisionStatus::PUBLISHED;
        }

        return false;
    }

    /**
     * Scope a query to only include active users.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('isActive', true);
    }
}
