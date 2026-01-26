<?php

namespace App\Models;

use App\Enums\SupervisionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * Supervision Model
 *
 * @property string $id
 * @property string $schoolId
 * @property string $userId
 * @property string|null $type
 * @property \Illuminate\Support\Carbon $date
 * @property string|null $academicYear
 * @property string|null $ministerPolicyId
 * @property string|null $obecPolicyId
 * @property string|null $areaPolicyId
 * @property string|null $summary
 * @property string|null $suggestions
 * @property SupervisionStatus $status
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property-read School $school
 * @property-read User $user
 * @property-read Policy|null $ministerPolicyRelation
 * @property-read Policy|null $obecPolicyRelation
 * @property-read Policy|null $areaPolicyRelation
 * @property-read Collection<int, Attachment> $attachments
 * @property-read Collection<int, Indicator> $indicators
 * @property-read Acknowledgement|null $acknowledgement
 * @property-read float $averageIndicatorScore
 *
 * @method static Builder|Supervision byStatus(SupervisionStatus $status)
 * @method static Builder|Supervision byAcademicYear(string $year)
 * @method static Builder|Supervision byDistrict(string $district)
 */
class Supervision extends Model
{
    use HasFactory;

    protected $table = 'supervision';
    protected $keyType = 'string';
    public $incrementing = false;

    public const CREATED_AT = 'createdAt';
    public const UPDATED_AT = 'updatedAt';

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
    /**
     * Check if supervision can be submitted for approval.
     *
     * @return bool
     */
    public function canSubmit(): bool
    {
        return $this->status === SupervisionStatus::DRAFT
               || $this->status === SupervisionStatus::NEEDS_IMPROVEMENT;
    }

    /**
     * Check if supervision can be approved.
     *
     * @return bool
     */
    public function canApprove(): bool
    {
        return $this->status === SupervisionStatus::SUBMITTED;
    }

    /**
     * Check if supervision can be published.
     *
     * @return bool
     */
    public function canPublish(): bool
    {
        return $this->status === SupervisionStatus::APPROVED;
    }

    /**
     * Check if supervision can be rejected.
     *
     * @return bool
     */
    public function canReject(): bool
    {
        return $this->status === SupervisionStatus::SUBMITTED;
    }

    /**
     * Submit supervision for approval.
     *
     * @return bool Success status
     */
    public function submit(): bool
    {
        if (! $this->canSubmit()) {
            return false;
        }

        $this->status = SupervisionStatus::SUBMITTED;

        return $this->save();
    }

    /**
     * Approve supervision.
     *
     * @return bool Success status
     */
    public function approve(): bool
    {
        if (! $this->canApprove()) {
            return false;
        }

        $this->status = SupervisionStatus::APPROVED;

        return $this->save();
    }

    /**
     * Publish supervision.
     *
     * @return bool Success status
     */
    public function publish(): bool
    {
        if (! $this->canPublish()) {
            return false;
        }

        $this->status = SupervisionStatus::PUBLISHED;

        return $this->save();
    }

    /**
     * Reject supervision (send back for improvement).
     *
     * @return bool Success status
     */
    public function reject(): bool
    {
        if (! $this->canReject()) {
            return false;
        }

        $this->status = SupervisionStatus::NEEDS_IMPROVEMENT;

        return $this->save();
    }

    // Scopes
    /**
     * Scope a query to filter by status.
     *
     * @param Builder $query
     * @param SupervisionStatus $status
     * @return Builder
     */
    public function scopeByStatus(Builder $query, SupervisionStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by academic year.
     *
     * @param Builder $query
     * @param string $year
     * @return Builder
     */
    public function scopeByAcademicYear(Builder $query, string $year): Builder
    {
        return $query->where('academicYear', $year);
    }

    /**
     * Scope a query to filter by district.
     *
     * @param Builder $query
     * @param string $district
     * @return Builder
     */
    public function scopeByDistrict(Builder $query, string $district): Builder
    {
        return $query->whereHas('school', fn(Builder $q) => $q->where('district', $district));
    }

    /**
     * Get the average indicator score.
     *
     * @return float Average score (0-4)
     */
    public function getAverageIndicatorScoreAttribute(): float
    {
        $indicators = $this->indicators;

        if ($indicators->isEmpty()) {
            return 0.0;
        }

        $total = $indicators->sum(fn(Indicator $indicator) => $indicator->level->score());

        return round($total / $indicators->count(), 2);
    }
}
