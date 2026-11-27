<?php

namespace Tests\Feature;

use App\Enums\PolicyLevel;
use App\Enums\PolicyType;
use App\Enums\SupervisionStatus;
use App\Livewire\Dashboard\DashboardSummary;
use App\Livewire\Supervision\SupervisionForm;
use App\Livewire\Supervision\SupervisionShow;
use App\Models\Policy;
use App\Models\School;
use App\Models\Supervision;
use App\Models\User;
use App\Services\SupervisionService;
use App\Services\TestDataPurger;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Runs against the configured DB inside a transaction that is rolled back after each test.
 */
class MultiPolicySupervisionTest extends TestCase
{
    use DatabaseTransactions;

    private const YEAR = '2598';

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

    private function makeSchool(?string $code = null, ?string $name = null): School
    {
        return School::create([
            'code' => $code ?? 'T' . Str::upper(Str::random(8)),
            'name' => $name ?? 'โรงเรียนทดสอบ ' . Str::random(4),
            'district' => 'อำเภอทดสอบ',
        ]);
    }

    private function makePolicy(string $title, PolicyType $type = PolicyType::SCHOOL_SAFETY, bool $active = true): Policy
    {
        return Policy::create([
            'type' => $type,
            'code' => 'TP' . Str::upper(Str::random(6)),
            'title' => $title,
            'isActive' => $active,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function baseData(School $school, User $user): array
    {
        return [
            'schoolId' => $school->id,
            'userId' => $user->id,
            'type' => 'นิเทศทดสอบ',
            'date' => '2055-06-01',
            'academicYear' => self::YEAR,
            'summary' => 'สรุป',
            'suggestions' => 'ข้อเสนอแนะ',
            'status' => SupervisionStatus::DRAFT,
        ];
    }

    /**
     * @return list<string>
     */
    private function pivotIds(Supervision $supervision, PolicyLevel $level): array
    {
        return DB::table('supervisionpolicy')
            ->where('supervisionId', $supervision->id)
            ->where('level', $level->value)
            ->orderBy('position')
            ->pluck('policyId')
            ->all();
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_11_28_000001_create_supervisionpolicy_table.php');
    }

    public function test_form_creates_supervision_with_several_policies_per_level(): void
    {
        $admin = $this->makeAdmin();
        $school = $this->makeSchool();
        [$a, $b, $c] = [$this->makePolicy('นโยบาย ก'), $this->makePolicy('นโยบาย ข'), $this->makePolicy('นโยบาย ค')];

        Livewire::actingAs($admin)->test(SupervisionForm::class)
            ->set('schoolId', $school->id)
            ->set('academicYear', self::YEAR)
            ->set('summary', 'สรุป')
            ->set('suggestions', 'ข้อเสนอแนะ')
            ->set('ministerPolicyIds', [$b->id, $a->id])
            ->set('obecPolicyIds', [$a->id, $b->id, $c->id])
            ->call('save')
            ->assertHasNoErrors();

        $supervision = Supervision::where('schoolId', $school->id)->firstOrFail();

        $this->assertSame([$b->id, $a->id], $this->pivotIds($supervision, PolicyLevel::MINISTER));
        $this->assertSame([$a->id, $b->id, $c->id], $this->pivotIds($supervision, PolicyLevel::OBEC));
        $this->assertSame([], $this->pivotIds($supervision, PolicyLevel::AREA));
        $this->assertSame($b->id, $supervision->ministerPolicyId);
        $this->assertSame($a->id, $supervision->obecPolicyId);
        $this->assertNull($supervision->areaPolicyId);
    }

    public function test_form_saves_without_any_policy(): void
    {
        $admin = $this->makeAdmin();
        $school = $this->makeSchool();

        Livewire::actingAs($admin)->test(SupervisionForm::class)
            ->set('schoolId', $school->id)
            ->set('summary', 'สรุป')
            ->set('suggestions', 'ข้อเสนอแนะ')
            ->call('save')
            ->assertHasNoErrors();

        $supervision = Supervision::where('schoolId', $school->id)->firstOrFail();
        $this->assertSame(0, DB::table('supervisionpolicy')->where('supervisionId', $supervision->id)->count());
    }

    public function test_form_rejects_unknown_policy(): void
    {
        $admin = $this->makeAdmin();
        $school = $this->makeSchool();

        Livewire::actingAs($admin)->test(SupervisionForm::class)
            ->set('schoolId', $school->id)
            ->set('summary', 'สรุป')
            ->set('suggestions', 'ข้อเสนอแนะ')
            ->set('areaPolicyIds', [(string) Str::ulid()])
            ->call('save')
            ->assertHasErrors('areaPolicyIds.0');

        $this->assertSame(0, Supervision::where('schoolId', $school->id)->count());
    }

    public function test_edit_form_loads_selection_and_saves_removals_and_clearing(): void
    {
        $admin = $this->makeAdmin();
        $school = $this->makeSchool();
        [$a, $b, $c] = [$this->makePolicy('นโยบาย ก'), $this->makePolicy('นโยบาย ข'), $this->makePolicy('นโยบาย ค')];
        $inactive = $this->makePolicy('นโยบายที่ปิดใช้งาน', PolicyType::SCHOOL_SAFETY, false);

        $supervision = app(SupervisionService::class)->createSupervision([
            ...$this->baseData($school, $admin),
            'ministerPolicyIds' => [$a->id, $b->id, $c->id],
            'obecPolicyIds' => [$a->id, $inactive->id],
            'areaPolicyIds' => [$c->id],
        ], [['name' => 'ตัวชี้วัด 1', 'level' => 'GOOD']]);

        $form = Livewire::actingAs($admin)->test(SupervisionForm::class, ['supervision' => $supervision])
            ->assertSet('ministerPolicyIds', [$a->id, $b->id, $c->id])
            ->assertSet('obecPolicyIds', [$a->id, $inactive->id])
            ->assertSet('areaPolicyIds', [$c->id]);

        $this->assertContains($inactive->id, array_column($form->instance()->policyOptions, 'id'));

        $form->set('ministerPolicyIds', [$a->id, $c->id])
            ->set('areaPolicyIds', [])
            ->call('save')
            ->assertHasNoErrors();

        $supervision->refresh();
        $this->assertSame([$a->id, $c->id], $this->pivotIds($supervision, PolicyLevel::MINISTER));
        $this->assertSame([$a->id, $inactive->id], $this->pivotIds($supervision, PolicyLevel::OBEC));
        $this->assertSame([], $this->pivotIds($supervision, PolicyLevel::AREA));
        $this->assertNull($supervision->areaPolicyId);
        $this->assertSame(1, $supervision->indicators()->count());
    }

    public function test_show_page_lists_every_policy(): void
    {
        $admin = $this->makeAdmin();
        $school = $this->makeSchool();
        $a = $this->makePolicy('นโยบายแสดงผลหนึ่ง');
        $b = $this->makePolicy('นโยบายแสดงผลสอง');
        $c = $this->makePolicy('นโยบายแสดงผลสาม');

        $supervision = app(SupervisionService::class)->createSupervision([
            ...$this->baseData($school, $admin),
            'ministerPolicyIds' => [$a->id, $b->id],
            'areaPolicyIds' => [$c->id],
        ]);

        Livewire::actingAs($admin)->test(SupervisionShow::class, ['supervision' => $supervision])
            ->assertSee('นโยบายแสดงผลหนึ่ง')
            ->assertSee('นโยบายแสดงผลสอง')
            ->assertSee('นโยบายแสดงผลสาม')
            ->assertSee('นโยบายกระทรวง')
            ->assertSee('นโยบายเขตพื้นที่')
            ->assertDontSee('นโยบาย สพฐ.');
    }

    public function test_service_accepts_single_value_fields_and_leaves_absent_levels_alone(): void
    {
        $admin = $this->makeAdmin();
        $school = $this->makeSchool();
        [$a, $b] = [$this->makePolicy('นโยบาย ก'), $this->makePolicy('นโยบาย ข')];
        $service = app(SupervisionService::class);

        $supervision = $service->createSupervision([
            ...$this->baseData($school, $admin),
            'ministerPolicyId' => $a->id,
            'obecPolicyIds' => [$a->id, $b->id],
        ]);
        $this->assertSame([$a->id], $this->pivotIds($supervision, PolicyLevel::MINISTER));

        $service->updateSupervision($supervision, ['summary' => 'สรุปใหม่', 'ministerPolicyId' => null], null);

        $this->assertSame([], $this->pivotIds($supervision, PolicyLevel::MINISTER));
        $this->assertSame([$a->id, $b->id], $this->pivotIds($supervision, PolicyLevel::OBEC));
        $this->assertSame($a->id, $supervision->fresh()->obecPolicyId);
    }

    public function test_existing_single_policy_rows_are_copied_into_the_pivot_and_back(): void
    {
        $admin = $this->makeAdmin();
        $school = $this->makeSchool();
        [$a, $b] = [$this->makePolicy('นโยบายเดิม ก'), $this->makePolicy('นโยบายเดิม ข')];

        $legacy = Supervision::create([
            ...$this->baseData($school, $admin),
            'ministerPolicyId' => $a->id,
            'areaPolicyId' => $b->id,
        ]);
        $this->assertSame(0, DB::table('supervisionpolicy')->where('supervisionId', $legacy->id)->count());

        $this->migration()->copyLegacyColumns();
        $this->migration()->copyLegacyColumns();

        $this->assertSame([$a->id], $this->pivotIds($legacy, PolicyLevel::MINISTER));
        $this->assertSame([], $this->pivotIds($legacy, PolicyLevel::OBEC));
        $this->assertSame([$b->id], $this->pivotIds($legacy, PolicyLevel::AREA));

        Livewire::actingAs($admin)->test(SupervisionShow::class, ['supervision' => $legacy->fresh()])
            ->assertSee('นโยบายเดิม ก')
            ->assertSee('นโยบายเดิม ข');

        $multi = app(SupervisionService::class)->createSupervision([
            ...$this->baseData($school, $admin),
            'obecPolicyIds' => [$b->id, $a->id],
        ]);
        DB::table('supervision')->where('id', $multi->id)->update(['obecPolicyId' => null]);
        DB::table('supervision')->where('id', $legacy->id)->update(['ministerPolicyId' => null, 'obecPolicyId' => $a->id]);

        $this->migration()->restoreLegacyColumns();

        $this->assertSame($b->id, $multi->fresh()->obecPolicyId);
        $this->assertSame($a->id, $legacy->fresh()->ministerPolicyId);
        $this->assertNull($legacy->fresh()->obecPolicyId);
    }

    public function test_api_accepts_arrays_and_returns_both_shapes(): void
    {
        $admin = $this->makeAdmin();
        $school = $this->makeSchool();
        [$a, $b, $c] = [$this->makePolicy('นโยบาย ก'), $this->makePolicy('นโยบาย ข'), $this->makePolicy('นโยบาย ค')];
        Sanctum::actingAs($admin);

        $payload = [
            'schoolId' => $school->id,
            'type' => 'นิเทศทดสอบ',
            'date' => '2055-06-01',
            'academicYear' => self::YEAR,
            'summary' => 'สรุป',
            'suggestions' => 'ข้อเสนอแนะ',
            'indicators' => [['name' => 'ตัวชี้วัด 1', 'level' => 'GOOD']],
        ];

        $response = $this->postJson('/api/supervisions', [
            ...$payload,
            'ministerPolicyIds' => [$b->id, $a->id],
            'areaPolicyIds' => [$c->id],
        ])->assertCreated();

        $response->assertJsonPath('ministerPolicyIds', [$b->id, $a->id])
            ->assertJsonPath('ministerPolicyId', $b->id)
            ->assertJsonPath('ministerPolicies.1.title', 'นโยบาย ก')
            ->assertJsonPath('obecPolicyIds', [])
            ->assertJsonPath('obecPolicyId', null)
            ->assertJsonPath('areaPolicyId', $c->id);

        $id = $response->json('id');
        $supervision = Supervision::findOrFail($id);

        $this->getJson("/api/supervisions/{$id}")
            ->assertOk()
            ->assertJsonPath('ministerPolicyIds', [$b->id, $a->id])
            ->assertJsonPath('areaPolicies.0.id', $c->id);

        unset($payload['schoolId'], $payload['indicators']);
        $this->putJson("/api/supervisions/{$id}", [...$payload, 'ministerPolicyIds' => [], 'obecPolicyIds' => [$a->id, $c->id]])
            ->assertOk()
            ->assertJsonPath('ministerPolicyIds', [])
            ->assertJsonPath('ministerPolicyId', null)
            ->assertJsonPath('obecPolicyIds', [$a->id, $c->id])
            ->assertJsonPath('areaPolicyIds', [$c->id]);

        $this->assertSame(1, $supervision->indicators()->count());

        $this->putJson("/api/supervisions/{$id}", [...$payload, 'obecPolicyIds' => [(string) Str::ulid()]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('obecPolicyIds.0');
    }

    public function test_api_still_accepts_single_value_fields(): void
    {
        $admin = $this->makeAdmin();
        $school = $this->makeSchool();
        [$a, $b] = [$this->makePolicy('นโยบาย ก'), $this->makePolicy('นโยบาย ข')];
        Sanctum::actingAs($admin);

        $payload = [
            'type' => 'นิเทศทดสอบ',
            'date' => '2055-06-01',
            'summary' => 'สรุป',
            'suggestions' => 'ข้อเสนอแนะ',
        ];

        $response = $this->postJson('/api/supervisions', [
            ...$payload,
            'schoolId' => $school->id,
            'ministerPolicyId' => $a->id,
            'obecPolicyId' => $b->id,
        ])->assertCreated()
            ->assertJsonPath('ministerPolicyId', $a->id)
            ->assertJsonPath('ministerPolicyIds', [$a->id])
            ->assertJsonPath('obecPolicyIds', [$b->id]);

        $id = $response->json('id');

        $this->putJson("/api/supervisions/{$id}", [...$payload, 'ministerPolicyId' => $b->id, 'obecPolicyId' => null])
            ->assertOk()
            ->assertJsonPath('ministerPolicyId', $b->id)
            ->assertJsonPath('ministerPolicyIds', [$b->id])
            ->assertJsonPath('obecPolicyIds', []);

        $this->postJson('/api/supervisions', [...$payload, 'schoolId' => $school->id, 'areaPolicyId' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('areaPolicyId');
    }

    public function test_policy_statistics_count_each_policy_once_per_supervision(): void
    {
        $admin = $this->makeAdmin();
        $school = $this->makeSchool();
        $a = $this->makePolicy('นโยบายสถิติ ก', PolicyType::SCHOOL_SAFETY);
        $b = $this->makePolicy('นโยบายสถิติ ข', PolicyType::READING_CULTURE);
        $service = app(SupervisionService::class);

        $service->createSupervision([
            ...$this->baseData($school, $admin),
            'ministerPolicyIds' => [$a->id, $b->id],
            'obecPolicyIds' => [$a->id],
        ]);
        $service->createSupervision([
            ...$this->baseData($school, $admin),
            'areaPolicyIds' => [$a->id],
        ]);

        Sanctum::actingAs($admin);
        $usage = $this->getJson('/api/analytics/policy-usage?academicYear=' . self::YEAR)->assertOk()->json();
        $byTitle = array_combine($usage['labels'], $usage['data']);
        $this->assertSame(['นโยบายสถิติ ก' => 2, 'นโยบายสถิติ ข' => 1], $byTitle);

        $this->getJson('/api/analytics/stats?academicYear=' . self::YEAR)
            ->assertOk()
            ->assertJsonPath('total_supervisions', 2);

        DashboardSummary::clearCache(self::YEAR);
        $chart = Livewire::actingAs($admin)->test(DashboardSummary::class)
            ->set('academicYear', self::YEAR)
            ->instance()
            ->policyUsage;
        DashboardSummary::clearCache(self::YEAR);

        $this->assertSame([2, 1], [
            $chart['data'][array_search('ความปลอดภัย', $chart['labels'], true)],
            $chart['data'][array_search('ส่งเสริมการอ่าน', $chart['labels'], true)],
        ]);
    }

    public function test_deleting_a_policy_or_supervision_leaves_no_orphan_links(): void
    {
        $admin = $this->makeAdmin();
        $school = $this->makeSchool();
        [$a, $b] = [$this->makePolicy('นโยบาย ก'), $this->makePolicy('นโยบาย ข')];

        $supervision = app(SupervisionService::class)->createSupervision([
            ...$this->baseData($school, $admin),
            'ministerPolicyIds' => [$a->id, $b->id],
        ]);

        $a->delete();
        $this->assertSame([$b->id], $this->pivotIds($supervision, PolicyLevel::MINISTER));
        $this->assertSame(0, DB::table('supervisionpolicy')->where('policyId', $a->id)->count());

        $supervision->delete();
        $this->assertSame(0, DB::table('supervisionpolicy')->where('supervisionId', $supervision->id)->count());
        $this->assertNotNull($b->fresh());
    }

    public function test_purging_test_data_removes_policy_links_of_seeded_schools(): void
    {
        $admin = $this->makeAdmin();
        $seeded = $this->makeSchool(TestDataPurger::SEEDED_SCHOOL_CODE_PREFIX . '999977', 'โรงเรียนบ้านทดสอบ 9977');
        $real = $this->makeSchool('47999977', 'บ้านจริงวิทยา');
        $policy = $this->makePolicy('นโยบาย ก');
        $service = app(SupervisionService::class);

        $seededSupervision = $service->createSupervision([...$this->baseData($seeded, $admin), 'ministerPolicyIds' => [$policy->id]]);
        $realSupervision = $service->createSupervision([...$this->baseData($real, $admin), 'ministerPolicyIds' => [$policy->id]]);

        app(TestDataPurger::class)->purge(false);

        $this->assertSame(0, DB::table('supervisionpolicy')->where('supervisionId', $seededSupervision->id)->count());
        $this->assertSame([$policy->id], $this->pivotIds($realSupervision, PolicyLevel::MINISTER));
    }
}
