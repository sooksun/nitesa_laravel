<?php

namespace App\Livewire\School;

use App\Enums\ExamScope;
use App\Enums\ExamType;
use App\Models\ExamResult;
use App\Models\School;
use Livewire\Component;

class SchoolShow extends Component
{
    public School $school;

    public function mount(School $school)
    {
        if (! auth()->user()->canViewSchool($school)) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงโรงเรียนนี้');
        }

        $this->school = $school->load([
            'networkGroupRelation',
            'supervisors',
            'supervisions' => fn($q) => $q->with('user')
                ->when(auth()->user()->isSchool(), fn($q) => $q->where('status', \App\Enums\SupervisionStatus::PUBLISHED))
                ->latest('date')
                ->limit(10),
        ]);
    }

    /**
     * Latest academic year of national exam results for this school, each with the area average.
     *
     * @return array{year: ?string, rows: \Illuminate\Support\Collection<int, array{result: ExamResult, areaAverage: ?float}>}
     */
    public function getExamResultsProperty(): array
    {
        $year = $this->school->examResults()->max('academicYear');
        if ($year === null) {
            return ['year' => null, 'rows' => collect()];
        }

        $results = $this->school->examResults()->where('academicYear', $year)->get();
        $rows = collect(ExamType::cases())
            ->map(fn(ExamType $type) => $results->first(fn(ExamResult $r) => $r->examType === $type))
            ->filter()
            ->map(fn(ExamResult $result) => [
                'result' => $result,
                'areaAverage' => ExamResult::benchmarks($result->examType, $year)[ExamScope::AREA->value]['average'] ?? null,
            ])
            ->values();

        return ['year' => $year, 'rows' => $rows];
    }

    public function render()
    {
        return view('livewire.school.school-show')
            ->layout('layouts.app', [
                'title' => $this->school->name,
                'header' => 'รายละเอียดโรงเรียน',
            ]);
    }
}
