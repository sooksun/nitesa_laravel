<?php

namespace App\Livewire\Supervision;

use App\Enums\IndicatorLevel;
use App\Enums\SupervisionStatus;
use App\Models\Indicator;
use App\Models\Policy;
use App\Models\School;
use App\Models\Supervision;
use App\Services\SupervisionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Livewire Component for Supervision Form (Create/Edit)
 *
 * @package App\Livewire\Supervision
 */
class SupervisionForm extends Component
{
    use WithFileUploads;

    public ?Supervision $supervision = null;
    public bool $editing = false;

    public string $schoolId = '';
    public string $type = 'นิเทศติดตาม';
    public string $date = '';
    public string $academicYear = '';
    public ?string $ministerPolicyId = null;
    public ?string $obecPolicyId = null;
    public ?string $areaPolicyId = null;
    public string $summary = '';
    public string $suggestions = '';

    public array $indicators = [];
    public array $uploads = [];

    public array $supervisionTypes = [
        'นิเทศติดตาม',
        'นิเทศกำกับ',
        'นิเทศให้คำปรึกษา',
        'นิเทศประเมินผล',
    ];

    public array $defaultIndicators = [
        'ด้านการบริหารจัดการ',
        'ด้านหลักสูตรและการจัดการเรียนรู้',
        'ด้านการวัดและประเมินผล',
        'ด้านคุณภาพผู้เรียน',
        'ด้านสภาพแวดล้อมและแหล่งเรียนรู้',
    ];

    protected function rules(): array
    {
        return [
            'schoolId' => ['required', 'exists:school,id'],
            'type' => ['required', 'string', 'max:100'],
            'date' => ['required', 'date'],
            'academicYear' => ['nullable', 'string', 'max:10'],
            'ministerPolicyId' => ['nullable', 'exists:policy,id'],
            'obecPolicyId' => ['nullable', 'exists:policy,id'],
            'areaPolicyId' => ['nullable', 'exists:policy,id'],
            'summary' => ['required', 'string'],
            'suggestions' => ['required', 'string'],
            'indicators' => ['required', 'array', 'min:1'],
            'indicators.*.name' => ['required', 'string'],
            'indicators.*.level' => ['required', 'in:EXCELLENT,GOOD,FAIR,NEEDS_WORK'],
            'indicators.*.comment' => ['nullable', 'string'],
        ];
    }

    protected $messages = [
        'schoolId.required' => 'กรุณาเลือกโรงเรียน',
        'type.required' => 'กรุณาระบุประเภทการนิเทศ',
        'date.required' => 'กรุณาระบุวันที่',
        'summary.required' => 'กรุณาระบุสรุปผลการนิเทศ',
        'suggestions.required' => 'กรุณาระบุข้อเสนอแนะ',
        'indicators.required' => 'กรุณาเพิ่มตัวชี้วัดอย่างน้อย 1 รายการ',
        'indicators.min' => 'กรุณาเพิ่มตัวชี้วัดอย่างน้อย 1 รายการ',
    ];

    public function mount(?Supervision $supervision = null)
    {
        if ($supervision && $supervision->exists) {
            $user = auth()->user();
            $canEdit = $user->isAdmin()
                || ($user->isSupervisor() && $supervision->userId === $user->id);
            $editableStatus = in_array($supervision->status, [
                \App\Enums\SupervisionStatus::DRAFT,
                \App\Enums\SupervisionStatus::NEEDS_IMPROVEMENT,
            ], true);

            if (! $canEdit || ! $editableStatus) {
                abort(403, 'คุณไม่มีสิทธิ์แก้ไขการนิเทศนี้');
            }

            $this->supervision = $supervision;
            $this->editing = true;

            $this->schoolId = $supervision->schoolId;
            $this->type = $supervision->type;
            $this->date = $supervision->date->format('Y-m-d');
            $this->academicYear = $supervision->academicYear ?? '';
            $this->ministerPolicyId = $supervision->ministerPolicyId;
            $this->obecPolicyId = $supervision->obecPolicyId;
            $this->areaPolicyId = $supervision->areaPolicyId;
            $this->summary = $supervision->summary;
            $this->suggestions = $supervision->suggestions;

            $this->indicators = $supervision->indicators->map(fn($i) => [
                'id' => $i->id,
                'name' => $i->name,
                'level' => $i->level->value,
                'comment' => $i->comment ?? '',
            ])->toArray();
        } else {
            $this->date = now()->format('Y-m-d');
            $this->academicYear = (string) (now()->year + 543);

            // Initialize default indicators
            foreach ($this->defaultIndicators as $name) {
                $this->indicators[] = [
                    'id' => null,
                    'name' => $name,
                    'level' => 'GOOD',
                    'comment' => '',
                ];
            }
        }
    }

    /**
     * Add a new indicator row
     *
     * @return void
     */
    public function addIndicator(): void
    {
        $this->indicators[] = [
            'id' => null,
            'name' => '',
            'level' => 'GOOD',
            'comment' => '',
        ];
    }

    /**
     * Remove an indicator by index
     *
     * @param int $index
     * @return void
     */
    public function removeIndicator(int $index): void
    {
        if (isset($this->indicators[$index])) {
            unset($this->indicators[$index]);
            $this->indicators = array_values($this->indicators);
        }
    }

    /**
     * Save supervision (create or update)
     *
     * @param string $status Status to save (default: DRAFT)
     * @return RedirectResponse
     */
    public function save(string $status = 'DRAFT'): RedirectResponse
    {
        $this->validate();

        try {
            $data = [
                'schoolId' => $this->schoolId,
                'userId' => auth()->id(),
                'type' => $this->type,
                'date' => $this->date,
                'academicYear' => $this->academicYear ?: null,
                'ministerPolicyId' => $this->ministerPolicyId ?: null,
                'obecPolicyId' => $this->obecPolicyId ?: null,
                'areaPolicyId' => $this->areaPolicyId ?: null,
                'summary' => $this->summary,
                'suggestions' => $this->suggestions,
                'status' => SupervisionStatus::from($status),
            ];

            $service = app(SupervisionService::class);

            if ($this->editing && $this->supervision !== null) {
                $supervision = $service->updateSupervision(
                    $this->supervision,
                    $data,
                    $this->indicators
                );
                $message = 'อัปเดตการนิเทศเรียบร้อยแล้ว';
            } else {
                $supervision = $service->createSupervision($data, $this->indicators);
                $message = 'บันทึกการนิเทศเรียบร้อยแล้ว';
            }

            // Handle file uploads
            $this->handleFileUploads($supervision);

            // Clear dashboard cache
            \App\Livewire\Dashboard\DashboardSummary::clearCache($supervision->academicYear);

            return redirect()
                ->route('supervisions.show', $supervision)
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to save supervision', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->dispatch('swal:error', [
                'title' => 'เกิดข้อผิดพลาด',
                'text' => 'ไม่สามารถบันทึกการนิเทศได้: ' . $e->getMessage(),
            ]);

            return redirect()->back();
        }
    }

    /**
     * Save and submit supervision for approval
     *
     * @return RedirectResponse
     */
    public function saveAndSubmit(): RedirectResponse
    {
        return $this->save('SUBMITTED');
    }

    /**
     * Handle file uploads for supervision
     *
     * @param Supervision $supervision
     * @return void
     */
    protected function handleFileUploads(Supervision $supervision): void
    {
        foreach ($this->uploads as $file) {
            try {
                $path = $file->store('attachments', 'local');
                $supervision->attachments()->create([
                    'filename' => $file->getClientOriginalName(),
                    'fileUrl' => $path,
                    'fileType' => $file->getMimeType(),
                    'fileSize' => $file->getSize(),
                    'uploadedAt' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to upload file', [
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get schools available for selection
     *
     * @return Collection<int, School>
     */
    public function getSchoolsProperty(): Collection
    {
        $query = School::orderBy('name');

        $user = auth()->user();
        if ($user !== null && $user->isSupervisor()) {
            $assignedSchoolIds = $user->assignedSchools()->pluck('school.id');
            $query->whereIn('id', $assignedSchoolIds);
        }

        return $query->get();
    }

    /**
     * Get active policies
     *
     * @return Collection<int, Policy>
     */
    public function getPoliciesProperty(): Collection
    {
        return Policy::where('isActive', true)
            ->orderBy('code')
            ->get();
    }

    /**
     * Get indicator levels
     *
     * @return array<int, IndicatorLevel>
     */
    public function getIndicatorLevelsProperty(): array
    {
        return IndicatorLevel::cases();
    }

    /**
     * Render the component
     *
     * @return View
     */
    public function render(): View
    {
        $title = $this->editing ? 'แก้ไขการนิเทศ' : 'บันทึกการนิเทศใหม่';

        return view('livewire.supervision.supervision-form')
            ->layout('layouts.app', [
                'title' => $title,
                'header' => $title,
            ]);
    }
}
