<?php

namespace Tests\Feature;

use App\Enums\SupervisionStatus;
use App\Livewire\School\SchoolShow;
use App\Livewire\Supervision\SupervisionList;
use App\Livewire\Supervision\SupervisionShow;
use App\Livewire\User\UserList;
use App\Models\School;
use App\Models\Supervision;
use App\Models\User;
use App\Notifications\SupervisionSubmittedNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Runs against the configured DB inside a transaction that is rolled back after each test.
 */
class BuddhistEraDisplayTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private School $school;

    private Supervision $supervision;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();

        $this->admin = User::create([
            'name' => 'test admin ' . Str::random(6),
            'email' => Str::lower(Str::random(12)) . '@test.local',
            'password' => bcrypt('password-for-tests'),
            'role' => 'ADMIN',
            'isActive' => true,
        ]);
        $this->admin->forceFill(['createdAt' => '2021-02-03 20:05:06'])->save();

        $this->school = School::create([
            'code' => 'T' . Str::upper(Str::random(8)),
            'name' => 'โรงเรียนทดสอบ ' . Str::random(4),
            'district' => 'อำเภอทดสอบ',
        ]);

        $this->supervision = Supervision::create([
            'schoolId' => $this->school->id,
            'userId' => $this->admin->id,
            'type' => 'นิเทศทดสอบ',
            'date' => '2019-06-15',
            'academicYear' => '2599',
            'summary' => 'สรุป',
            'suggestions' => 'ข้อเสนอแนะ',
            'status' => SupervisionStatus::DRAFT,
        ]);
    }

    public function test_no_gregorian_display_format_remains(): void
    {
        $offenders = [];
        $files = Finder::create()->files()->in([resource_path('views'), app_path()])->name('*.php');
        foreach ($files as $file) {
            if (str_contains($file->getContents(), 'd/m/Y')) {
                $offenders[] = $file->getRelativePathname();
            }
        }

        $this->assertSame([], $offenders);
    }

    public function test_supervision_pages_show_buddhist_era_dates(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SupervisionList::class)
            ->set('search', $this->school->name)
            ->assertSee('15/06/2562')
            ->assertDontSee('15/06/2019');

        Livewire::actingAs($this->admin)
            ->test(SupervisionShow::class, ['supervision' => $this->supervision])
            ->assertSee('15/06/2562')
            ->assertDontSee('15/06/2019');

        Livewire::actingAs($this->admin)
            ->test(SchoolShow::class, ['school' => $this->school])
            ->assertSee('15/06/2562');
    }

    public function test_user_list_shows_created_date_in_buddhist_era_and_thai_time(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserList::class)
            ->set('search', $this->admin->email)
            ->assertSee('04/02/2564')
            ->assertDontSee('03/02/2564');
    }

    public function test_notification_mail_uses_buddhist_era_date(): void
    {
        $mail = (new SupervisionSubmittedNotification($this->supervision))->toMail($this->admin);

        $this->assertStringContainsString('15/06/2562', implode("\n", $mail->introLines));
    }
}
