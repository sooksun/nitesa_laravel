<?php

namespace App\Services;

use App\Models\Indicator;
use App\Models\Supervision;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service class for Supervision business logic
 *
 * @package App\Services
 */
class SupervisionService
{
    /**
     * Create a new supervision with indicators
     *
     * @param array<string, mixed> $data Supervision data
     * @param array<int, array<string, mixed>> $indicators Indicator data
     * @return Supervision Created supervision instance
     * @throws \Exception
     */
    public function createSupervision(array $data, array $indicators = []): Supervision
    {
        return DB::transaction(function () use ($data, $indicators) {
            $supervision = Supervision::create($data);

            if (! empty($indicators)) {
                $this->createIndicators($supervision, $indicators);
            }

            return $supervision->load(['school', 'user', 'indicators']);
        });
    }

    /**
     * Update supervision and its indicators
     *
     * @param Supervision $supervision Supervision to update
     * @param array<string, mixed> $data Supervision data
     * @param array<int, array<string, mixed>> $indicators Indicator data
     * @return Supervision Updated supervision instance
     * @throws \Exception
     */
    public function updateSupervision(Supervision $supervision, array $data, array $indicators = []): Supervision
    {
        return DB::transaction(function () use ($supervision, $data, $indicators) {
            $supervision->update($data);

            if (isset($indicators)) {
                $this->syncIndicators($supervision, $indicators);
            }

            return $supervision->load(['school', 'user', 'indicators']);
        });
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
        $created = collect();

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
    public function reject(Supervision $supervision, ?string $reason = null): bool
    {
        if (! $supervision->canReject()) {
            Log::warning('Cannot reject supervision', [
                'supervision_id' => $supervision->id,
                'current_status' => $supervision->status->value,
            ]);

            return false;
        }

        $result = $supervision->reject();

        if ($result && $reason) {
            // Store rejection reason if needed
            Log::info('Supervision rejected', [
                'supervision_id' => $supervision->id,
                'reason' => $reason,
            ]);
        }

        return $result;
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
