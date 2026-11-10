<?php

namespace Tests\Feature;

use App\Enums\SupervisionStatus;
use App\Livewire\NetworkGroup\NetworkGroupIndex;
use App\Livewire\Policy\PolicyList;
use App\Livewire\Report\ReportIndex;
use App\Livewire\School\SchoolList;
use App\Livewire\Supervision\SupervisionForm;
use App\Livewire\Supervision\SupervisionList;
use App\Livewire\Supervision\SupervisionShow;
use App\Models\NetworkGroup;
use App\Models\School;
use App\Models\Supervision;
use App\Models\User;
use App\Services\SupervisionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Runs against the configured DB inside a transaction that is rolled back after each test.
 */
class AccessAndWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    private function makeUser(string $role, array $schools = []): User
    {
        $user = User::create([
            'name' => 'test ' . $role,
            'email' => Str::lower(Str::random(12)) . '@test.local',
            'password' => bcrypt('password-for-tests'),
            'role' => $role,
            'isActive' => true,
        ]);
        if ($schools) {
            $user->assignedSchools()->sync(array_map(fn (School $s) => $s->id, $schools));
        }

        return $user;
    }

    private function makeSchool(): School
    {
        return School::create([
            'code' => 'T' . Str::upper(Str::random(8)),
            'name' => 'โรงเรียนทดสอบ ' . Str::random(4),
            'district' => 'อำเภอทดสอบ',
        ]);
    }

    private function makeSupervision(School $school, User $owner, SupervisionStatus $status, bool $complete = true): Supervision
    {
        $s = Supervision::create([
            'schoolId' => $school->id,
            'userId' => $owner->id,
            'type' => 'นิเทศทดสอบ',
            'date' => now(),
            'academicYear' => '2599',
            'summary' => $complete ? 'สรุป' : '',
            'suggestions' => $complete ? 'ข้อเสนอแนะ' : '',
            'status' => $status,
        ]);
        if ($complete) {
            $s->indicators()->create(['supervisionId' => $s->id, 'name' => 'ตัวชี้วัด 1', 'level' => 'GOOD']);
        }

        return $s;
    }

    public function test_school_user_only_sees_published_supervisions_of_own_school(): void
    {
        $own = $this->makeSchool();
        $other = $this->makeSchool();
        $sup = $this->makeUser('SUPERVISOR', [$own, $other]);
        $schoolUser = $this->makeUser('SCHOOL', [$own]);

        $ownPublished = $this->makeSupervision($own, $sup, SupervisionStatus::PUBLISHED);
        $otherPublished = $this->makeSupervision($other, $sup, SupervisionStatus::PUBLISHED);
        $ownDraft = $this->makeSupervision($own, $sup, SupervisionStatus::DRAFT);

        $this->assertTrue($schoolUser->canAccessSupervision($ownPublished));
        $this->assertFalse($schoolUser->canAccessSupervision($otherPublished));
        $this->assertFalse($schoolUser->canAccessSupervision($ownDraft));

        $this->actingAs($schoolUser)->get(route('supervisions.show', $otherPublished))->assertForbidden();
        $this->actingAs($schoolUser)->get(route('supervisions.acknowledge', $otherPublished))->assertForbidden();
        $this->actingAs($schoolUser)->get(route('schools.show', $other))->assertForbidden();

        $ids = Livewire::actingAs($schoolUser)->test(SupervisionList::class)
            ->viewData('supervisions')->pluck('id')->all();
        $this->assertContains($ownPublished->id, $ids);
        $this->assertNotContains($otherPublished->id, $ids);
        $this->assertNotContains($ownDraft->id, $ids);
    }

    public function test_publish_notifies_only_users_of_that_school(): void
    {
        $own = $this->makeSchool();
        $other = $this->makeSchool();
        $sup = $this->makeUser('SUPERVISOR', [$own]);
        $ownSchoolUser = $this->makeUser('SCHOOL', [$own]);
        $otherSchoolUser = $this->makeUser('SCHOOL', [$other]);
        $s = $this->makeSupervision($own, $sup, SupervisionStatus::APPROVED);

        Livewire::actingAs($sup)->test(SupervisionShow::class, ['supervision' => $s])->call('publish');

        $this->assertSame(SupervisionStatus::PUBLISHED, $s->fresh()->status);
        Notification::assertSentTo($ownSchoolUser, \App\Notifications\SupervisionPublishedNotification::class);
        Notification::assertNotSentTo($otherSchoolUser, \App\Notifications\SupervisionPublishedNotification::class);
    }

    public function test_non_admin_cannot_delete_school_via_livewire(): void
    {
        $school = $this->makeSchool();
        $exec = $this->makeUser('EXECUTIVE');

        Livewire::actingAs($exec)->test(SchoolList::class)
            ->set('deleteId', $school->id)
            ->call('deleteSchool')
            ->assertForbidden();

        $this->assertNotNull(School::find($school->id));
    }

    public function test_supervisor_cannot_skip_approval_through_form_save(): void
    {
        $school = $this->makeSchool();
        $sup = $this->makeUser('SUPERVISOR', [$school]);

        Livewire::actingAs($sup)->test(SupervisionForm::class)
            ->set('schoolId', $school->id)
            ->set('type', 'นิเทศทดสอบ')
            ->set('summary', 'สรุป')
            ->set('suggestions', 'ข้อเสนอแนะ')
            ->call('save', 'PUBLISHED');

        $created = Supervision::where('userId', $sup->id)->latest('createdAt')->first();
        $this->assertNotNull($created);
        $this->assertSame(SupervisionStatus::DRAFT, $created->status);
    }

    public function test_supervisor_creates_and_submits_supervision_via_form(): void
    {
        $school = $this->makeSchool();
        $sup = $this->makeUser('SUPERVISOR', [$school]);
        $exec = $this->makeUser('EXECUTIVE');

        Livewire::actingAs($sup)->test(SupervisionForm::class)
            ->set('schoolId', $school->id)
            ->set('type', 'นิเทศทดสอบ')
            ->set('summary', 'สรุป')
            ->set('suggestions', 'ข้อเสนอแนะ')
            ->call('saveAndSubmit')
            ->assertHasNoErrors()
            ->assertRedirect();

        $created = Supervision::where('userId', $sup->id)->latest('createdAt')->first();
        $this->assertNotNull($created);
        $this->assertSame(SupervisionStatus::SUBMITTED, $created->status);
        $this->assertGreaterThan(0, $created->indicators()->count());
        Notification::assertSentTo($exec, \App\Notifications\SupervisionSubmittedNotification::class);
    }

    public function test_supervisor_cannot_create_supervision_for_unassigned_school(): void
    {
        $assigned = $this->makeSchool();
        $foreign = $this->makeSchool();
        $sup = $this->makeUser('SUPERVISOR', [$assigned]);

        Livewire::actingAs($sup)->test(SupervisionForm::class)
            ->set('schoolId', $foreign->id)
            ->set('type', 'นิเทศทดสอบ')
            ->set('summary', 'สรุป')
            ->set('suggestions', 'ข้อเสนอแนะ')
            ->call('save')
            ->assertForbidden();
    }

    public function test_submit_from_show_page_requires_complete_fields(): void
    {
        $school = $this->makeSchool();
        $sup = $this->makeUser('SUPERVISOR', [$school]);
        $s = $this->makeSupervision($school, $sup, SupervisionStatus::DRAFT, complete: false);

        Livewire::actingAs($sup)->test(SupervisionShow::class, ['supervision' => $s])
            ->call('submit')
            ->assertDispatched('swal:error');

        $this->assertSame(SupervisionStatus::DRAFT, $s->fresh()->status);
    }

    public function test_reject_requires_and_records_reason(): void
    {
        $school = $this->makeSchool();
        $sup = $this->makeUser('SUPERVISOR', [$school]);
        $exec = $this->makeUser('EXECUTIVE');
        $s = $this->makeSupervision($school, $sup, SupervisionStatus::SUBMITTED);

        Livewire::actingAs($exec)->test(SupervisionShow::class, ['supervision' => $s])
            ->call('reject')
            ->assertHasErrors('rejectReason');
        $this->assertSame(SupervisionStatus::SUBMITTED, $s->fresh()->status);

        Livewire::actingAs($exec)->test(SupervisionShow::class, ['supervision' => $s])
            ->set('rejectReason', 'ข้อมูลตัวชี้วัดไม่ครบ')
            ->call('reject');

        $s->refresh();
        $this->assertSame(SupervisionStatus::NEEDS_IMPROVEMENT, $s->status);
        $this->assertSame('ข้อมูลตัวชี้วัดไม่ครบ', app(SupervisionService::class)->latestRejectionReason($s));
    }

    public function test_supervisor_can_publish_own_approved_supervision_but_not_others(): void
    {
        $school = $this->makeSchool();
        $owner = $this->makeUser('SUPERVISOR', [$school]);
        $peer = $this->makeUser('SUPERVISOR', [$school]);
        $s = $this->makeSupervision($school, $owner, SupervisionStatus::APPROVED);

        Livewire::actingAs($peer)->test(SupervisionShow::class, ['supervision' => $s])->call('publish');
        $this->assertSame(SupervisionStatus::APPROVED, $s->fresh()->status);

        Livewire::actingAs($owner)->test(SupervisionShow::class, ['supervision' => $s])->call('publish');
        $this->assertSame(SupervisionStatus::PUBLISHED, $s->fresh()->status);
    }

    public function test_policies_are_viewable_by_all_roles_but_only_admin_can_change(): void
    {
        $sup = $this->makeUser('SUPERVISOR');
        $this->actingAs($sup)->get(route('policies.index'))->assertOk();
        $this->actingAs($sup)->get(route('policies.create'))->assertRedirect(route('dashboard'));

        $policy = \App\Models\Policy::query()->first();
        if ($policy) {
            Livewire::actingAs($sup)->test(PolicyList::class)
                ->call('toggleActive', $policy->id)
                ->assertForbidden();
        }
    }

    public function test_admin_manages_network_groups(): void
    {
        $admin = $this->makeUser('ADMIN');
        $code = 'NG' . Str::upper(Str::random(6));

        $this->actingAs($admin)->get(route('network-groups.index'))->assertOk();
        $this->actingAs($this->makeUser('EXECUTIVE'))->get(route('network-groups.index'))->assertRedirect(route('dashboard'));

        Livewire::actingAs($admin)->test(NetworkGroupIndex::class)
            ->call('create')
            ->set('code', $code)
            ->set('name', 'เครือข่ายทดสอบ')
            ->call('save')
            ->assertHasNoErrors();

        $group = NetworkGroup::where('code', $code)->first();
        $this->assertNotNull($group);

        Livewire::actingAs($admin)->test(NetworkGroupIndex::class)
            ->call('edit', $group->id)
            ->set('name', 'เครือข่ายแก้ไข')
            ->call('save');
        $this->assertSame('เครือข่ายแก้ไข', $group->fresh()->name);

        Livewire::actingAs($admin)->test(NetworkGroupIndex::class)
            ->set('deleteId', $group->id)
            ->call('delete');
        $this->assertNull(NetworkGroup::find($group->id));
    }

    public function test_report_export_is_admin_only_and_returns_xlsx(): void
    {
        $admin = $this->makeUser('ADMIN');
        $exec = $this->makeUser('EXECUTIVE');

        foreach (['summary', 'district', 'indicator'] as $type) {
            Livewire::actingAs($admin)->test(ReportIndex::class)
                ->set('reportType', $type)
                ->call('export')
                ->assertFileDownloaded();
        }

        Livewire::actingAs($exec)->test(ReportIndex::class)->call('export')->assertForbidden();
    }

    public function test_manual_page_and_dashboard_render(): void
    {
        $school = $this->makeSchool();
        $schoolUser = $this->makeUser('SCHOOL', [$school]);

        $this->actingAs($schoolUser)->get(route('manual'))->assertOk()->assertSee('คู่มือการใช้งานระบบ NITESA');
        $this->actingAs($schoolUser)->get(route('dashboard'))->assertOk();
        $this->actingAs($schoolUser)->get(route('reports.index'))->assertOk();

        \App\Livewire\Dashboard\DashboardSummary::clearCache('2599');
        $this->assertTrue(true);
    }

    public function test_api_school_write_and_scoped_reads(): void
    {
        $admin = $this->makeUser('ADMIN');
        $code = 'API' . Str::upper(Str::random(6));

        \Laravel\Sanctum\Sanctum::actingAs($admin);
        $this->postJson('/api/schools', ['code' => $code, 'name' => 'โรงเรียน API', 'district' => 'อำเภอ'])
            ->assertCreated();
        $school = School::where('code', $code)->firstOrFail();
        $this->putJson('/api/schools/' . $school->id, ['code' => $code, 'name' => 'ชื่อใหม่', 'district' => 'อำเภอ'])
            ->assertOk();
        $this->assertSame('ชื่อใหม่', $school->fresh()->name);

        $other = $this->makeSchool();
        \Laravel\Sanctum\Sanctum::actingAs($this->makeUser('SCHOOL', [$school]));
        $this->getJson('/api/schools/' . $other->id)->assertForbidden();
        $this->getJson('/api/schools/' . $school->id)->assertOk();
    }

    public function test_api_reject_requires_reason(): void
    {
        $school = $this->makeSchool();
        $sup = $this->makeUser('SUPERVISOR', [$school]);
        $s = $this->makeSupervision($school, $sup, SupervisionStatus::SUBMITTED);

        \Laravel\Sanctum\Sanctum::actingAs($this->makeUser('EXECUTIVE'));
        $this->postJson("/api/supervisions/{$s->id}/reject", [])->assertUnprocessable();
        $this->postJson("/api/supervisions/{$s->id}/reject", ['reason' => 'แก้ไขสรุป'])->assertOk();
        $this->assertSame('แก้ไขสรุป', app(SupervisionService::class)->latestRejectionReason($s->fresh()));
    }

    public function test_activity_log_is_admin_only(): void
    {
        $this->actingAs($this->makeUser('ADMIN'))->get(route('activity-log.index'))->assertOk();
        $this->actingAs($this->makeUser('EXECUTIVE'))->get(route('activity-log.index'))->assertRedirect(route('dashboard'));

        \Laravel\Sanctum\Sanctum::actingAs($this->makeUser('EXECUTIVE'));
        $this->getJson('/api/activity-log')->assertForbidden();
    }

    public function test_user_relations_use_camel_case_foreign_keys(): void
    {
        $school = $this->makeSchool();
        $sup = $this->makeUser('SUPERVISOR', [$school]);
        $this->makeSupervision($school, $sup, SupervisionStatus::DRAFT);

        $this->assertSame(1, $sup->supervisions()->count());
    }
}
