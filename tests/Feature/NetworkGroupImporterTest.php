<?php

namespace Tests\Feature;

use App\Models\NetworkGroup;
use App\Models\School;
use App\Services\Import\NetworkGroupImporter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Runs against the configured DB inside a transaction that is rolled back after each test.
 */
class NetworkGroupImporterTest extends TestCase
{
    use DatabaseTransactions;

    private function group(string $code, string $name): NetworkGroup
    {
        return NetworkGroup::create(['code' => $code, 'name' => $name]);
    }

    private function school(?string $groupId): School
    {
        return School::create([
            'code' => 'T' . Str::upper(Str::random(10)),
            'name' => 'โรงเรียนสำหรับทดสอบ ' . Str::random(6),
            'district' => 'ทดสอบ',
            'networkGroupId' => $groupId,
        ]);
    }

    public function test_plain_import_adds_and_updates_without_deleting(): void
    {
        $prefix = 'TG' . Str::upper(Str::random(5));
        $kept = $this->group("{$prefix}1", 'กลุ่มเดิม');
        $other = $this->group("{$prefix}9", 'กลุ่มที่ไม่อยู่ในไฟล์');
        $school = $this->school($kept->id);

        $result = app(NetworkGroupImporter::class)->import([
            ['code' => "{$prefix}1", 'name' => 'ชื่อใหม่', 'description' => 'อำเภอทดสอบ'],
            ['code' => "{$prefix}2", 'name' => 'กลุ่มใหม่', 'description' => null],
        ], false);

        $this->assertSame(['imported' => 2, 'deleted' => 0, 'detached' => 0], $result);
        $this->assertSame('ชื่อใหม่', $kept->fresh()->name);
        $this->assertNotNull($other->fresh());
        $this->assertSame($kept->id, $school->fresh()->networkGroupId);
    }

    public function test_replace_deletes_other_groups_and_detaches_every_school(): void
    {
        $prefix = 'TG' . Str::upper(Str::random(5));
        $reused = $this->group("{$prefix}1", 'กลุ่มทดสอบที่รหัสซ้ำกับของจริง');
        $schoolOfReused = $this->school($reused->id);
        $before = NetworkGroup::count();
        $linkedBefore = School::whereNotNull('networkGroupId')->count();

        $result = app(NetworkGroupImporter::class)->import([
            ['code' => "{$prefix}1", 'name' => 'ศูนย์จริง 1', 'description' => 'อำเภอ ก'],
            ['code' => "{$prefix}2", 'name' => 'ศูนย์จริง 2', 'description' => 'อำเภอ ข'],
        ], true);

        $this->assertSame(2, $result['imported']);
        $this->assertSame($before - 1, $result['deleted']);
        $this->assertSame($linkedBefore, $result['detached']);
        $this->assertSame(2, NetworkGroup::count());
        $this->assertSame('ศูนย์จริง 1', $reused->fresh()->name);
        $this->assertNull($schoolOfReused->fresh()->networkGroupId);
        $this->assertSame(0, School::whereNotNull('networkGroupId')->count());
    }

    public function test_replace_refuses_a_file_with_an_incomplete_row_and_changes_nothing(): void
    {
        $before = NetworkGroup::count();
        $linkedBefore = School::whereNotNull('networkGroupId')->count();

        try {
            app(NetworkGroupImporter::class)->import([
                ['code' => 'TGX1', 'name' => 'ศูนย์จริง 1'],
                ['code' => '', 'name' => 'ไม่มีรหัส'],
            ], true);
            $this->fail('expected an exception');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('แถว 3', $e->getMessage());
        }

        $this->assertSame($before, NetworkGroup::count());
        $this->assertSame($linkedBefore, School::whereNotNull('networkGroupId')->count());
    }

    public function test_replace_refuses_an_empty_file(): void
    {
        $before = NetworkGroup::count();

        try {
            app(NetworkGroupImporter::class)->import([], true);
            $this->fail('expected an exception');
        } catch (\InvalidArgumentException) {
            $this->assertSame($before, NetworkGroup::count());
        }
    }

    public function test_duplicate_codes_in_the_file_are_refused(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        app(NetworkGroupImporter::class)->import([
            ['code' => 'TGX1', 'name' => 'ก'],
            ['code' => 'tgx1 ', 'name' => 'ข'],
        ], true);
    }
}
