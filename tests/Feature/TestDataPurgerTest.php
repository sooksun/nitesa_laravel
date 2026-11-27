<?php

namespace Tests\Feature;

use App\Models\Improvement;
use App\Models\NetworkGroup;
use App\Models\School;
use App\Models\User;
use App\Services\TestDataPurger;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Runs against the configured DB inside a transaction that is rolled back after each test.
 */
class TestDataPurgerTest extends TestCase
{
    use DatabaseTransactions;

    private function school(string $code, string $name): School
    {
        return School::create(['code' => $code, 'name' => $name, 'district' => 'ทดสอบ']);
    }

    private function user(string $email, string $role = 'SUPERVISOR'): User
    {
        return User::create([
            'name' => 'purger test',
            'email' => $email,
            'password' => bcrypt(Str::random(20)),
            'role' => $role,
            'isActive' => true,
        ]);
    }

    public function test_preview_counts_without_deleting(): void
    {
        $seeded = $this->school('1044999901', 'โรงเรียนบ้านทดสอบ 9901');
        $schools = School::count();

        $preview = app(TestDataPurger::class)->preview();

        $this->assertGreaterThanOrEqual(1, $preview['schools']);
        $this->assertSame($schools, School::count());
        $this->assertNotNull($seeded->fresh());
    }

    public function test_purge_deletes_only_seeded_schools_and_what_hangs_off_them(): void
    {
        $seeded = $this->school('1044999901', 'โรงเรียนบ้านทดสอบ 9901');
        $real = $this->school('47999901', 'บ้านจริงวิทยา');
        $realNamedLikeTest = $this->school('47999902', 'บ้านทดสอบวิทยา');
        $seedCodeRealName = $this->school('1044999903', 'บ้านจริงอีกแห่ง');
        $supervisor = $this->user(Str::lower(Str::random(10)) . '@example.org');
        $supervisor->assignedSchools()->attach([$seeded->id, $real->id]);

        $result = app(TestDataPurger::class)->purge(false);

        $this->assertGreaterThanOrEqual(1, $result['schools']);
        $this->assertNull($seeded->fresh());
        $this->assertNotNull($real->fresh());
        $this->assertNotNull($realNamedLikeTest->fresh());
        $this->assertNotNull($seedCodeRealName->fresh());
        $this->assertSame(0, School::where('code', 'like', '1044%')->where('name', 'like', '%ทดสอบ%')->count());
        $this->assertSame([$real->id], DB::table('_supervisorschools')->where('B', $supervisor->id)->pluck('A')->all());
        $this->assertNotNull($supervisor->fresh());
    }

    public function test_purge_deletes_seeded_network_groups_but_keeps_others(): void
    {
        $seededName = TestDataPurger::SEEDED_GROUP_NAMES[0];
        $seeded = NetworkGroup::firstOrCreate(['name' => $seededName], ['code' => 'TPG' . Str::upper(Str::random(5))]);
        $real = NetworkGroup::create(['code' => 'TPG' . Str::upper(Str::random(5)), 'name' => 'ศูนย์อำนวยการเครือข่ายสำหรับทดสอบตัวล้าง']);
        $linked = $this->school('47999903', 'บ้านผูกกลุ่ม');
        $linked->update(['networkGroupId' => $seeded->id]);

        app(TestDataPurger::class)->purge(false);

        $this->assertNull($seeded->fresh());
        $this->assertNotNull($real->fresh());
        $this->assertNull($linked->fresh()->networkGroupId);
    }

    public function test_users_are_kept_unless_asked_for(): void
    {
        $seedUser = $this->user('purger-' . Str::lower(Str::random(8)) . '@nitesa.local');

        app(TestDataPurger::class)->purge(false);

        $this->assertNotNull($seedUser->fresh());
    }

    public function test_purge_with_users_deletes_seed_accounts_only(): void
    {
        $this->user(Str::lower(Str::random(10)) . '@example.org', 'ADMIN');
        $seedUser = $this->user('purger-' . Str::lower(Str::random(8)) . '@nitesa.local');
        $realUser = $this->user(Str::lower(Str::random(10)) . '@example.org');

        $result = app(TestDataPurger::class)->purge(true);

        $this->assertGreaterThanOrEqual(1, $result['users']);
        $this->assertNull($seedUser->fresh());
        $this->assertNotNull($realUser->fresh());
        $this->assertSame(0, User::where('email', 'like', '%@nitesa.local')->count());
    }

    public function test_purge_with_users_refuses_when_a_seed_account_owns_an_improvement_of_a_real_school(): void
    {
        $this->user(Str::lower(Str::random(10)) . '@example.org', 'ADMIN');
        $seedUser = $this->user('purger-' . Str::lower(Str::random(8)) . '@nitesa.local', 'SCHOOL');
        $real = $this->school('47999904', 'บ้านจริงมีแผนพัฒนา');
        $seeded = $this->school('1044999901', 'โรงเรียนบ้านทดสอบ 9901');
        $improvement = Improvement::create(['schoolId' => $real->id, 'userId' => $seedUser->id, 'title' => 'แผนพัฒนาของโรงเรียนจริง']);

        try {
            app(TestDataPurger::class)->purge(true);
            $this->fail('expected an exception');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('แผนพัฒนา', $e->getMessage());
        }

        $this->assertNotNull($improvement->fresh());
        $this->assertNotNull($seedUser->fresh());
        $this->assertNotNull($seeded->fresh());
    }

    public function test_improvements_of_seeded_schools_do_not_block_removing_seed_accounts(): void
    {
        $this->user(Str::lower(Str::random(10)) . '@example.org', 'ADMIN');
        $seedUser = $this->user('purger-' . Str::lower(Str::random(8)) . '@nitesa.local', 'SCHOOL');
        $seeded = $this->school('1044999901', 'โรงเรียนบ้านทดสอบ 9901');
        Improvement::create(['schoolId' => $seeded->id, 'userId' => $seedUser->id, 'title' => 'แผนพัฒนาทดสอบ']);

        app(TestDataPurger::class)->purge(true);

        $this->assertNull($seedUser->fresh());
        $this->assertNull($seeded->fresh());
    }

    public function test_purge_with_users_refuses_when_no_other_active_admin_would_remain(): void
    {
        User::where('role', 'ADMIN')->where('email', 'not like', '%@nitesa.local')->update(['isActive' => false]);
        $seedUser = $this->user('purger-' . Str::lower(Str::random(8)) . '@nitesa.local', 'ADMIN');
        $seeded = $this->school('1044999901', 'โรงเรียนบ้านทดสอบ 9901');

        try {
            app(TestDataPurger::class)->purge(true);
            $this->fail('expected an exception');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('ADMIN', $e->getMessage());
        }

        $this->assertNotNull($seedUser->fresh());
        $this->assertNotNull($seeded->fresh());
    }
}
