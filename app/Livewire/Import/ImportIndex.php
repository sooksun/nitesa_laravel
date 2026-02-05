<?php

namespace App\Livewire\Import;

use App\Models\NetworkGroup;
use App\Models\Policy;
use App\Models\School;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ImportIndex extends Component
{
    use WithFileUploads;

    public $file;
    public string $importType = 'schools';
    public array $importErrors = [];
    public array $results = [];
    public bool $importing = false;

    public function import()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            'importType' => 'required|in:schools,policies,network_groups',
        ]);

        $this->importErrors = [];
        $this->results = [];
        $this->importing = true;

        try {
            /** @var array<int, array<int, mixed>> $sheets */
            $sheets = Excel::toArray(new class {}, $this->file->getRealPath());
            $data = $sheets[0] ?? [];

            // Remove header row
            /** @var array<int, string> $headers */
            $headers = array_shift($data) ?? [];

            $imported = 0;
            $failed = 0;

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 because of header and 0-index

                try {
                    match ($this->importType) {
                        'schools' => $this->importSchool($row, $headers),
                        'policies' => $this->importPolicy($row, $headers),
                        'network_groups' => $this->importNetworkGroup($row, $headers),
                        default => throw new \Exception("ประเภทการนำเข้าไม่ถูกต้อง: {$this->importType}"),
                    };
                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                    $this->importErrors[] = "แถว {$rowNumber}: {$e->getMessage()}";
                }
            }

            $this->results = [
                'total' => count($data),
                'imported' => $imported,
                'failed' => $failed,
            ];

            if ($imported > 0) {
                $this->dispatch('swal:success', [
                    'title' => 'สำเร็จ!',
                    'text' => "นำเข้าข้อมูลเรียบร้อย: {$imported} รายการ",
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());
            $this->importErrors[] = "เกิดข้อผิดพลาด: {$e->getMessage()}";
        }

        $this->importing = false;
        $this->file = null;
    }

    private function importSchool(array $row, array $headers): void
    {
        $data = array_combine($headers, $row);

        if (empty($data['code']) || empty($data['name'])) {
            throw new \Exception('ข้อมูลไม่ครบถ้วน (ต้องมี code และ name)');
        }

        School::updateOrCreate(
            ['code' => $data['code']],
            [
                'name' => $data['name'],
                'province' => $data['province'] ?? null,
                'district' => $data['district'] ?? '',
                'subDistrict' => $data['subDistrict'] ?? $data['sub_district'] ?? null,
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'principalName' => $data['principalName'] ?? $data['principal_name'] ?? null,
                'studentCount' => isset($data['studentCount']) ? (int) $data['studentCount'] : (isset($data['student_count']) ? (int) $data['student_count'] : null),
                'teacherCount' => isset($data['teacherCount']) ? (int) $data['teacherCount'] : (isset($data['teacher_count']) ? (int) $data['teacher_count'] : null),
            ]
        );
    }

    private function importPolicy(array $row, array $headers): void
    {
        $data = array_combine($headers, $row);

        if (empty($data['code']) || empty($data['title']) || empty($data['type'])) {
            throw new \Exception('ข้อมูลไม่ครบถ้วน (ต้องมี code, title และ type)');
        }

        // Handle isActive - convert string TRUE/FALSE to boolean
        $isActive = true;
        if (isset($data['isActive'])) {
            $isActive = $this->parseBoolean($data['isActive']);
        } elseif (isset($data['is_active'])) {
            $isActive = $this->parseBoolean($data['is_active']);
        }

        Policy::updateOrCreate(
            ['code' => $data['code'], 'type' => $data['type']],
            [
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'isActive' => $isActive,
            ]
        );
    }

    private function parseBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_string($value)) {
            return in_array(strtoupper(trim($value)), ['TRUE', '1', 'YES', 'Y'], true);
        }
        return (bool) $value;
    }

    private function importNetworkGroup(array $row, array $headers): void
    {
        $data = array_combine($headers, $row);

        if (empty($data['code']) || empty($data['name'])) {
            throw new \Exception('ข้อมูลไม่ครบถ้วน (ต้องมี code และ name)');
        }

        NetworkGroup::updateOrCreate(
            ['code' => $data['code']],
            [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]
        );
    }

    public function render()
    {
        return view('livewire.import.import-index')
            ->layout('layouts.app', ['title' => 'นำเข้าข้อมูล', 'header' => 'นำเข้าข้อมูล']);
    }
}
