<?php

namespace App\Livewire\Supervision;

use App\Enums\IndicatorLevel;
use App\Enums\SupervisionStatus;
use App\Models\Indicator;
use App\Models\Policy;
use App\Models\School;
use App\Models\Supervision;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

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
            'indicators' => ['array'],
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
    ];

    public function mount(?Supervision $supervision = null)
    {
        if ($supervision && $supervision->exists) {
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
            $this->academicYear = (string)(now()->year + 543);
            
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

    public function addIndicator()
    {
        $this->indicators[] = [
            'id' => null,
            'name' => '',
            'level' => 'GOOD',
            'comment' => '',
        ];
    }

    public function removeIndicator($index)
    {
        unset($this->indicators[$index]);
        $this->indicators = array_values($this->indicators);
    }

    public function save($status = 'DRAFT')
    {
        $this->validate();

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
            'status' => $status,
        ];

        if ($this->editing) {
            $this->supervision->update($data);
            $supervision = $this->supervision;
            
            // Update or create indicators
            $existingIds = [];
            foreach ($this->indicators as $ind) {
                if (!empty($ind['name'])) {
                    if (!empty($ind['id'])) {
                        $indicator = Indicator::find($ind['id']);
                        if ($indicator) {
                            $indicator->update([
                                'name' => $ind['name'],
                                'level' => $ind['level'],
                                'comment' => $ind['comment'] ?: null,
                            ]);
                            $existingIds[] = $ind['id'];
                        }
                    } else {
                        $indicator = $supervision->indicators()->create([
                            'name' => $ind['name'],
                            'level' => $ind['level'],
                            'comment' => $ind['comment'] ?: null,
                        ]);
                        $existingIds[] = $indicator->id;
                    }
                }
            }
            
            // Delete removed indicators
            $supervision->indicators()->whereNotIn('id', $existingIds)->delete();
            
            $message = 'อัปเดตการนิเทศเรียบร้อยแล้ว';
        } else {
            $supervision = Supervision::create($data);
            
            // Create indicators
            foreach ($this->indicators as $ind) {
                if (!empty($ind['name'])) {
                    $supervision->indicators()->create([
                        'name' => $ind['name'],
                        'level' => $ind['level'],
                        'comment' => $ind['comment'] ?: null,
                    ]);
                }
            }
            
            $message = 'บันทึกการนิเทศเรียบร้อยแล้ว';
        }

        // Handle file uploads
        foreach ($this->uploads as $file) {
            $path = $file->store('attachments', 'local');
            $supervision->attachments()->create([
                'filename' => $file->getClientOriginalName(),
                'fileUrl' => $path,
                'fileType' => $file->getMimeType(),
                'fileSize' => $file->getSize(),
                'uploadedAt' => now(),
            ]);
        }

        return redirect()->route('supervisions.show', $supervision)->with('success', $message);
    }

    public function saveAndSubmit()
    {
        $this->save('SUBMITTED');
    }

    public function getSchoolsProperty()
    {
        $query = School::orderBy('name');
        
        if (auth()->user()->isSupervisor()) {
            $assignedSchoolIds = auth()->user()->assignedSchools()->pluck('school.id');
            $query->whereIn('id', $assignedSchoolIds);
        }

        return $query->get();
    }

    public function getPoliciesProperty()
    {
        return Policy::where('isActive', true)->orderBy('code')->get();
    }

    public function getIndicatorLevelsProperty()
    {
        return IndicatorLevel::cases();
    }

    public function render()
    {
        $title = $this->editing ? 'แก้ไขการนิเทศ' : 'บันทึกการนิเทศใหม่';
        
        return view('livewire.supervision.supervision-form')
            ->layout('layouts.app', ['title' => $title, 'header' => $title]);
    }
}
