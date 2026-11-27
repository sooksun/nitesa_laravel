<?php

namespace App\Services;

use App\Enums\PolicyLevel;
use App\Enums\Role;
use App\Models\Indicator;
use App\Models\Supervision;
use App\Models\SupervisionPolicy;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

/**
 * Service class for Supervision business logic
 *
 * @package App\Services
 */
class SupervisionService
{
    /**
     * Validation rules for the policy selections accepted by createSupervision/updateSupervision.
     *
     * @return array<string, list<string>>
     */
    public static function policyRules(bool $withLegacyFields = true): array
    {
        $rules = [];
        foreach (PolicyLevel::cases() as $level) {
            if ($withLegacyFields) {
                $rules[$level->legacyColumn()] = ['nullable', 'string', 'exists:policy,id'];
            }
            $rules[$level->idsKey()] = ['nullable', 'array', 'max:100'];
            $rules[$level->idsKey() . '.*'] = ['string', 'distinct', 'exists:policy,id'];
        }

        return $rules;
    }

    /**
     * Create a new supervision with indicators
     *
     * Policies are passed in $data as `ministerPolicyIds` / `obecPolicyIds` / `areaPolicyIds` (arrays);
     * the single-value `ministerPolicyId` / `obecPolicyId` / `areaPolicyId` keys are still accepted.
     *
     * @param array<string, mixed> $data Supervision data
     * @param array<int, array<string, mixed>> $indicators Indicator data
     * @return Supervision Created supervision instance
     * @throws \Exception
     */
    public function createSupervision(array $data, array $indicators = []): Supervision
    {
        return DB::transaction(function () use ($data, $indicators) {
            $policies = $this->pullPolicySelections($data, true);
            $supervision = Supervision::create($data);

            if (! empty($indicators)) {
                $this->createIndicators($supervision, $indicators);
            }

            $this->syncPolicies($supervision, $policies);

            return $supervision->load(['school', 'user', 'indicators', 'policies']);
        });
    }

    /**
     * Update supervision and its indicators
     *
     * @param Supervision $supervision Supervision to update
     * @param array<string, mixed> $data Supervision data
     * @param array<int, array<string, mixed>>|null $indicators Indicator data; null leaves indicators untouched
     * @return Supervision Updated supervision instance
     * @throws \Exception
     */
    public function updateSupervision(Supervision $supervision, array $data, ?array $indicators = []): Supervision
    {
        return DB::transaction(function () use ($supervision, $data, $indicators) {
            $policies = $this->pullPolicySelections($data, false);
            $supervision->update($data);

            if ($indicators !== null) {
                $this->syncIndicators($supervision, $indicators);
            }

            $this->syncPolicies($supervision, $policies);

            return $supervision->load(['school', 'user', 'indicators', 'policies']);
        });
    }

    /**
     * Takes the policy selections out of $data and mirrors the first policy of each level
     * into the single-value column. Levels absent from $data are left untouched on update.
     *
     * @param array<string, mixed> $data
     * @return array<string, list<string>> keyed by PolicyLevel value
     */
    protected function pullPolicySelections(array &$data, bool $creating): array
    {
        $selections = [];

        foreach (PolicyLevel::cases() as $level) {
            $idsKey = $level->idsKey();
            $legacyKey = $level->legacyColumn();

            if (array_key_exists($idsKey, $data)) {
                $ids = (array) ($data[$idsKey] ?? []);
            } elseif (array_key_exists($legacyKey, $data)) {
                $ids = [$data[$legacyKey]];
            } elseif ($creating) {
                $ids = [];
            } else {
                continue;
            }
            unset($data[$idsKey]);

            $ids = array_values(array_unique(array_filter(
                array_map(fn($id) => is_scalar($id) ? (string) $id : '', $ids),
                fn(string $id) => $id !== ''
            )));

            $data[$legacyKey] = $ids[0] ?? null;
            $selections[$level->value] = $ids;
        }

        return $selections;
    }

    /**
     * @param array<string, list<string>> $selections keyed by PolicyLevel value
     */
    protected function syncPolicies(Supervision $supervision, array $selections): void
    {
        foreach ($selections as $level => $policyIds) {
            $current = SupervisionPolicy::query()
                ->where('supervisionId', $supervision->id)
                ->where('level', $level)
                ->orderBy('position')
                ->orderBy('id')
                ->pluck('policyId')
                ->all();

            if ($current === $policyIds) {
                continue;
            }

            SupervisionPolicy::query()
                ->where('supervisionId', $supervision->id)
                ->where('level', $level)
                ->delete();

            foreach ($policyIds as $position => $policyId) {
                SupervisionPolicy::create([
                    'supervisionId' => $supervision->id,
                    'policyId' => $policyId,
                    'level' => $level,
                    'position' => $position,
                ]);
            }
        }
    }

    /**
     * Create indicators for a supervision
     *
     * @param Supervision $supervision Supervision instance
     * @param array<int, array<string, mixed>> $indicators Indicator data
     * @return Collection<int, Indicator> Created indicators
     */
    protected function createIndicators(Supervision $supervision, array $indicators): Collection
    {
        $created = new Collection();

        foreach ($indicators as $indicatorData) {
            if (empty($indicatorData['name'])) {
                continue;
            }

            $indicator = $supervision->indicators()->create([
                'name' => $indicatorData['name'],
                'level' => $indicatorData['level'],
                'comment' => $indicatorData['comment'] ?? null,
            ]);

            $created->push($indicator);
        }

        return $created;
    }

    /**
     * Sync indicators (create, update, delete)
     *
     * @param Supervision $supervision Supervision instance
     * @param array<int, array<string, mixed>> $indicators Indicator data
     * @return void
     */
    protected function syncIndicators(Supervision $supervision, array $indicators): void
    {
        // Empty array = remove all indicators (explicit handling for Laravel whereNotIn([], …))
        if (empty($indicators)) {
            $supervision->indicators()->delete();

            return;
        }

        $existingIds = [];

        foreach ($indicators as $indicatorData) {
            if (empty($indicatorData['name'])) {
                continue;
            }

            if (! empty($indicatorData['id'])) {
                // Update existing indicator
                $indicator = Indicator::find($indicatorData['id']);
                if ($indicator && $indicator->supervisionId === $supervision->id) {
                    $indicator->update([
                        'name' => $indicatorData['name'],
                        'level' => $indicatorData['level'],
                        'comment' => $indicatorData['comment'] ?? null,
                    ]);
                    $existingIds[] = $indicator->id;
                }
            } else {
                // Create new indicator
                $indicator = $supervision->indicators()->create([
                    'name' => $indicatorData['name'],
                    'level' => $indicatorData['level'],
                    'comment' => $indicatorData['comment'] ?? null,
                ]);
                $existingIds[] = $indicator->id;
            }
        }

        // Delete indicators that were removed from the form
        $supervision->indicators()
            ->whereNotIn('id', $existingIds)
            ->delete();
    }

    /**
     * Submit supervision for approval
     *
     * @param Supervision $supervision Supervision to submit
     * @return bool Success status
     */
    public function submit(Supervision $supervision): bool
    {
        if (! $supervision->canSubmit()) {
            Log::warning('Cannot submit supervision', [
                'supervision_id' => $supervision->id,
                'current_status' => $supervision->status->value,
            ]);

            return false;
        }

        return $supervision->submit();
    }

    /**
     * Approve supervision
     *
     * @param Supervision $supervision Supervision to approve
     * @return bool Success status
     */
    public function approve(Supervision $supervision): bool
    {
        if (! $supervision->canApprove()) {
            Log::warning('Cannot approve supervision', [
                'supervision_id' => $supervision->id,
                'current_status' => $supervision->status->value,
            ]);

            return false;
        }

        return $supervision->approve();
    }

    /**
     * Reject supervision (send back for improvement)
     *
     * @param Supervision $supervision Supervision to reject
     * @param string|null $reason Rejection reason
     * @return bool Success status
     */
    public function reject(Supervision $supervision, ?string $reason = null, ?User $causer = null): bool
    {
        if (! $supervision->canReject()) {
            Log::warning('Cannot reject supervision', [
                'supervision_id' => $supervision->id,
                'current_status' => $supervision->status->value,
            ]);

            return false;
        }

        $result = $supervision->reject();

        if ($result) {
            $reason = $reason !== null ? trim($reason) : '';

            activity()
                ->causedBy($causer)
                ->performedOn($supervision)
                ->withProperties(['reason' => $reason !== '' ? $reason : null])
                ->log('ส่งกลับเพื่อปรับปรุง' . ($reason !== '' ? ': ' . $reason : ''));
        }

        return $result;
    }

    /**
     * Latest reason recorded when the supervision was sent back for improvement.
     */
    public function latestRejectionReason(Supervision $supervision): ?string
    {
        $activity = Activity::query()
            ->where('subject_type', $supervision->getMorphClass())
            ->where('subject_id', $supervision->id)
            ->where('description', 'like', 'ส่งกลับเพื่อปรับปรุง%')
            ->latest('id')
            ->first();

        if (! $activity) {
            return null;
        }

        $reason = $activity->getExtraProperty('reason');
        if ($reason) {
            return $reason;
        }

        // Older entries stored the reason only in the description ("ส่งกลับเพื่อปรับปรุง: <reason>")
        $parts = explode(': ', $activity->description, 2);

        return $parts[1] ?? null;
    }

    /**
     * Active SCHOOL-role users bound to the supervision's school.
     *
     * @return \Illuminate\Support\Collection<int, User>
     */
    public function schoolRecipients(Supervision $supervision): \Illuminate\Support\Collection
    {
        return User::query()
            ->where('role', Role::SCHOOL->value)
            ->where('isActive', true)
            ->whereHas('assignedSchools', fn($q) => $q->where('school.id', $supervision->schoolId))
            ->get();
    }

    /**
     * Publish supervision
     *
     * @param Supervision $supervision Supervision to publish
     * @return bool Success status
     */
    public function publish(Supervision $supervision): bool
    {
        if (! $supervision->canPublish()) {
            Log::warning('Cannot publish supervision', [
                'supervision_id' => $supervision->id,
                'current_status' => $supervision->status->value,
            ]);

            return false;
        }

        return $supervision->publish();
    }

    /**
     * Validate supervision can be submitted
     *
     * @param Supervision $supervision Supervision to validate
     * @return array<string, string> Validation errors (empty if valid)
     */
    public function validateForSubmission(Supervision $supervision): array
    {
        $errors = [];

        if (empty($supervision->summary)) {
            $errors['summary'] = 'กรุณาระบุสรุปผลการนิเทศ';
        }

        if (empty($supervision->suggestions)) {
            $errors['suggestions'] = 'กรุณาระบุข้อเสนอแนะ';
        }

        if ($supervision->indicators->isEmpty()) {
            $errors['indicators'] = 'กรุณาเพิ่มตัวชี้วัดอย่างน้อย 1 รายการ';
        }

        return $errors;
    }
}
