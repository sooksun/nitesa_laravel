<?php

namespace App\Services\Import;

use App\Models\NetworkGroup;
use App\Models\School;
use Illuminate\Support\Facades\DB;

class NetworkGroupImporter
{
    /**
     * @param array<int, array<string, mixed>> $rows spreadsheet rows keyed by header, in file order
     * @param bool $replace delete every group that is not in the file and detach all schools first
     *
     * @return array{imported: int, deleted: int, detached: int}
     */
    public function import(array $rows, bool $replace): array
    {
        $groups = $this->validated($rows, $replace);

        return DB::transaction(function () use ($groups, $replace) {
            $result = ['imported' => 0, 'deleted' => 0, 'detached' => 0];

            if ($replace) {
                $result['detached'] = School::query()
                    ->whereNotNull('networkGroupId')
                    ->update(['networkGroupId' => null, 'networkGroup' => null]);
                $result['deleted'] = NetworkGroup::query()->whereNotIn('code', array_keys($groups))->delete();
            }

            foreach ($groups as $code => $group) {
                NetworkGroup::updateOrCreate(['code' => $code], $group);
                $result['imported']++;
            }

            return $result;
        });
    }

    /**
     * A replace must never run on a half-readable file, so every row has to be complete.
     *
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array<string, array{name: string, description: ?string}> keyed by code
     */
    private function validated(array $rows, bool $strict): array
    {
        $groups = [];
        foreach (array_values($rows) as $index => $row) {
            $rowNumber = $index + 2;
            $code = trim((string) ($row['code'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));

            if ($code === '' || $name === '') {
                if (! $strict && $code === '' && $name === '') {
                    continue;
                }

                throw new \InvalidArgumentException("แถว {$rowNumber}: ข้อมูลไม่ครบถ้วน (ต้องมี code และ name)");
            }

            $key = mb_strtoupper($code);
            if (isset($groups[$key])) {
                throw new \InvalidArgumentException("แถว {$rowNumber}: รหัสกลุ่มเครือข่ายซ้ำในไฟล์ ({$code})");
            }

            $description = trim((string) ($row['description'] ?? ''));
            $groups[$key] = ['name' => $name, 'description' => $description === '' ? null : $description];
        }

        if ($strict && $groups === []) {
            throw new \InvalidArgumentException('ไม่พบข้อมูลกลุ่มเครือข่ายในไฟล์ จึงไม่ล้างข้อมูลเดิม');
        }

        return $groups;
    }
}
