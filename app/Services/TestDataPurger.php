<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\NetworkGroup;
use App\Models\School;
use App\Models\Supervision;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Removes the demo records created by DatabaseSeeder so real data can be loaded.
 * Only rows that carry the seeder's own markers are touched.
 */
class TestDataPurger
{
    public const SEEDED_SCHOOL_CODE_PREFIX = '1044';
    public const SEEDED_SCHOOL_NAME_MARK = 'ทดสอบ';
    public const SEEDED_USER_EMAIL_DOMAIN = '@nitesa.local';
    public const SEEDED_GROUP_NAMES = [
        'กลุ่มเครือข่ายพัฒนาคุณภาพการศึกษา',
        'กลุ่มเครือข่ายส่งเสริมการอ่าน',
        'กลุ่มเครือข่ายนวัตกรรมและเทคโนโลยี',
        'กลุ่มเครือข่ายความปลอดภัยในสถานศึกษา',
        'กลุ่มเครือข่ายพัฒนาครูและบุคลากร',
    ];

    /**
     * @return array{schools: int, supervisions: int, networkGroups: int, users: int, userEmails: list<string>}
     */
    public function preview(): array
    {
        $schoolIds = $this->seededSchools()->pluck('id');

        return [
            'schools' => $schoolIds->count(),
            'supervisions' => Supervision::query()->whereIn('schoolId', $schoolIds)->count(),
            'networkGroups' => $this->seededGroups()->count(),
            'users' => $this->seededUsers()->count(),
            'userEmails' => $this->seededUsers()->orderBy('email')->pluck('email')->all(),
        ];
    }

    /**
     * Deleting a school cascades to its supervisions, their indicators, attachments and
     * acknowledgements, its improvements, exam results and supervisor assignments.
     *
     * @return array{schools: int, supervisions: int, networkGroups: int, users: int}
     */
    public function purge(bool $withUsers): array
    {
        if ($withUsers) {
            $this->assertUsersCanBeRemoved();
        }

        return DB::transaction(function () use ($withUsers) {
            $schoolIds = $this->seededSchools()->pluck('id');
            $supervisions = Supervision::query()->whereIn('schoolId', $schoolIds)->count();

            return [
                'schools' => $this->seededSchools()->delete(),
                'supervisions' => $supervisions,
                'networkGroups' => $this->seededGroups()->delete(),
                'users' => $withUsers ? $this->seededUsers()->delete() : 0,
            ];
        });
    }

    private function assertUsersCanBeRemoved(): void
    {
        $otherAdmins = User::query()
            ->where('role', Role::ADMIN->value)
            ->where('isActive', true)
            ->where('email', 'not like', '%' . self::SEEDED_USER_EMAIL_DOMAIN)
            ->count();

        if ($otherAdmins === 0) {
            throw new \RuntimeException(
                'ไม่ลบบัญชีทดสอบ เพราะจะไม่เหลือบัญชี ADMIN ที่ใช้งานได้ กรุณาสร้างบัญชี ADMIN จริงก่อน'
            );
        }

        $owned = Supervision::query()
            ->whereIn('userId', $this->seededUsers()->select('id'))
            ->whereNotIn('schoolId', $this->seededSchools()->select('id'))
            ->count();

        if ($owned > 0) {
            throw new \RuntimeException(
                "ไม่ลบบัญชีทดสอบ เพราะมีการนิเทศของโรงเรียนจริง {$owned} รายการที่บันทึกโดยบัญชีทดสอบ"
            );
        }
    }

    /**
     * @return Builder<School>
     */
    private function seededSchools(): Builder
    {
        return School::query()
            ->where('code', 'like', self::SEEDED_SCHOOL_CODE_PREFIX . '%')
            ->where('name', 'like', '%' . self::SEEDED_SCHOOL_NAME_MARK . '%');
    }

    /**
     * @return Builder<NetworkGroup>
     */
    private function seededGroups(): Builder
    {
        return NetworkGroup::query()->whereIn('name', self::SEEDED_GROUP_NAMES);
    }

    /**
     * @return Builder<User>
     */
    private function seededUsers(): Builder
    {
        return User::query()->where('email', 'like', '%' . self::SEEDED_USER_EMAIL_DOMAIN);
    }
}
