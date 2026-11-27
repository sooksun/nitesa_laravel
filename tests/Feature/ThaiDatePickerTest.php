<?php

namespace Tests\Feature;

use App\Enums\SupervisionStatus;
use App\Livewire\ActivityLog\ActivityLogIndex;
use App\Livewire\Supervision\SupervisionForm;
use App\Models\School;
use App\Models\Supervision;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Runs against the configured DB inside a transaction that is rolled back after each test.
 */
class ThaiDatePickerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    private function makeAdmin(): User
    {
        return User::create([
            'name' => 'test admin',
            'email' => Str::lower(Str::random(12)) . '@test.local',
            'password' => bcrypt('password-for-tests'),
            'role' => 'ADMIN',
            'isActive' => true,
        ]);
    }

    private function makeSchool(): School
    {
        return School::create([
            'code' => 'T' . Str::upper(Str::random(8)),
            'name' => 'โรงเรียนทดสอบ ' . Str::random(4),
            'district' => 'อำเภอทดสอบ',
        ]);
    }

    public function test_views_have_no_native_date_inputs(): void
    {
        $offenders = [];
        foreach (Finder::create()->files()->in(resource_path('views'))->name('*.blade.php') as $file) {
            if (preg_match('/type=["\'](date|datetime-local|month)["\']/', $file->getContents())) {
                $offenders[] = $file->getRelativePathname();
            }
        }

        $this->assertSame([], $offenders);
    }

    public function test_activity_log_filters_use_thai_picker_and_keep_gregorian_values(): void
    {
        $admin = $this->makeAdmin();
        $marker = 'picker-test-' . Str::random(8);
        $old = activity()->causedBy($admin)->log($marker . '-old');
        $old->forceFill(['created_at' => '2020-03-10 09:00:00'])->save();
        activity()->causedBy($admin)->log($marker . '-new');

        Livewire::actingAs($admin)
            ->test(ActivityLogIndex::class)
            ->assertSeeHtml('x-data="thaiDatePicker"')
            ->assertSeeHtml('wire:model.live="dateFrom"')
            ->assertSeeHtml('wire:model.live="dateTo"')
            ->set('search', $marker)
            ->assertSee($marker . '-old')
            ->assertSee('10/03/2563')
            ->set('dateFrom', '2020-03-11')
            ->assertSet('dateFrom', '2020-03-11')
            ->assertDontSee($marker . '-old')
            ->assertSee($marker . '-new')
            ->set('dateFrom', '')
            ->set('dateTo', '2020-03-10')
            ->assertSee($marker . '-old')
            ->assertDontSee($marker . '-new')
            ->call('clearFilters')
            ->assertSet('dateFrom', '')
            ->assertSet('dateTo', '');
    }

    public function test_supervision_form_loads_and_saves_gregorian_date(): void
    {
        $admin = $this->makeAdmin();
        $school = $this->makeSchool();
        $supervision = Supervision::create([
            'schoolId' => $school->id,
            'userId' => $admin->id,
            'type' => 'นิเทศทดสอบ',
            'date' => '2026-11-27',
            'academicYear' => '2599',
            'summary' => 'สรุป',
            'suggestions' => 'ข้อเสนอแนะ',
            'status' => SupervisionStatus::DRAFT,
        ]);
        $supervision->indicators()->create(['supervisionId' => $supervision->id, 'name' => 'ตัวชี้วัด 1', 'level' => 'GOOD']);

        Livewire::actingAs($admin)
            ->test(SupervisionForm::class, ['supervision' => $supervision])
            ->assertSet('date', '2026-11-27')
            ->assertSeeHtml('x-data="thaiDatePicker"')
            ->assertSeeHtml('wire:model="date"')
            ->assertSeeHtml('id="date"')
            ->set('date', '2026-12-05')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('2026-12-05', $supervision->fresh()->date->format('Y-m-d'));
    }
}
