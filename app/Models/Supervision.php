<?php

namespace App\Models;

use App\Enums\SupervisionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Supervision extends Model
{
    use HasFactory;

    protected $table = 'supervision';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'schoolId',
        'userId',
        'type',
        'date',
        'academicYear',
        'ministerPolicy',
        'obecPolicy',
        'areaPolicy',
        'ministerPolicyId',
        'obecPolicyId',
        'areaPolicyId',
        'summary',
        'suggestions',
        'status',
    ];

    protected $casts = [
        'date' => 'datetime',
        'status' => SupervisionStatus::class,
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


    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'schoolId');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function ministerPolicyRelation(): BelongsTo
    {
        return $this->belongsTo(Policy::class, 'ministerPolicyId');
    }

    public function obecPolicyRelation(): BelongsTo
    {
        return $this->belongsTo(Policy::class, 'obecPolicyId');
    }

    public function areaPolicyRelation(): BelongsTo
    {
        return $this->belongsTo(Policy::class, 'areaPolicyId');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'supervisionId');
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(Indicator::class, 'supervisionId');
    }

    public function acknowledgement(): HasOne
    {
        return $this->hasOne(Acknowledgement::class, 'supervisionId');
    }

    // Workflow methods
    public function canSubmit(): bool
    {
        return $this->status === SupervisionStatus::DRAFT ||
               $this->status === SupervisionStatus::NEEDS_IMPROVEMENT;
    }

    public function canApprove(): bool
    {
        return $this->status === SupervisionStatus::SUBMITTED;
    }

    public function canPublish(): bool
    {
        return $this->status === SupervisionStatus::APPROVED;
    }

    public function canReject(): bool
    {
        return $this->status === SupervisionStatus::SUBMITTED;
    }

    public function submit(): bool
    {
        if (!$this->canSubmit()) {
            return false;
        }
        $this->status = SupervisionStatus::SUBMITTED;
        return $this->save();
    }

    public function approve(): bool
    {
        if (!$this->canApprove()) {
            return false;
        }
        $this->status = SupervisionStatus::APPROVED;
        return $this->save();
    }

    public function publish(): bool
    {
        if (!$this->canPublish()) {
            return false;
        }
        $this->status = SupervisionStatus::PUBLISHED;
        return $this->save();
    }

    public function reject(): bool
    {
        if (!$this->canReject()) {
            return false;
        }
        $this->status = SupervisionStatus::NEEDS_IMPROVEMENT;
        return $this->save();
    }

    // Scopes
    public function scopeByStatus($query, SupervisionStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByAcademicYear($query, string $year)
    {
        return $query->where('academicYear', $year);
    }

    public function scopeByDistrict($query, string $district)
    {
        return $query->whereHas('school', fn($q) => $q->where('district', $district));
    }

    public function getAverageIndicatorScoreAttribute(): float
    {
        $indicators = $this->indicators;
        if ($indicators->isEmpty()) {
            return 0;
        }

        $total = $indicators->sum(fn($i) => $i->level->score());
        return round($total / $indicators->count(), 2);
    }
}
