<?php

namespace App\Console\Commands;

use App\Services\TestDataPurger;
use Illuminate\Console\Command;

class PurgeTestData extends Command
{
    protected $signature = 'nitesa:purge-test-data
        {--with-users : ลบบัญชีผู้ใช้ทดสอบ (@nitesa.local) ด้วย}
        {--force : ลบจริง ถ้าไม่ใส่จะแสดงเฉพาะจำนวนที่จะถูกลบ}';

    protected $description = 'ล้างข้อมูลทดสอบจาก seeder (โรงเรียนบ้านทดสอบ การนิเทศของโรงเรียนเหล่านั้น และกลุ่มเครือข่ายทดสอบ)';

    public function handle(TestDataPurger $purger): int
    {
        $withUsers = (bool) $this->option('with-users');
        $preview = $purger->preview();

        $this->line("โรงเรียนทดสอบ: {$preview['schools']}");
        $this->line("การนิเทศของโรงเรียนทดสอบ (รวมตัวชี้วัด ไฟล์แนบ การรับทราบ): {$preview['supervisions']}");
        $this->line("กลุ่มเครือข่ายทดสอบ: {$preview['networkGroups']}");
        $this->line('บัญชีผู้ใช้ทดสอบ: ' . $preview['users'] . ($withUsers ? '' : ' (ไม่ลบ เพราะไม่ได้ใส่ --with-users)'));
        if ($withUsers) {
            foreach ($preview['userEmails'] as $email) {
                $this->line("  - {$email}");
            }
        }

        if (! $this->option('force')) {
            $this->warn('ยังไม่ได้ลบอะไร ใส่ --force เพื่อลบจริง (ควรสำรองฐานข้อมูลก่อน)');

            return self::SUCCESS;
        }

        try {
            $result = $purger->purge($withUsers);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info(
            "ลบแล้ว: โรงเรียน {$result['schools']}, การนิเทศ {$result['supervisions']}, "
            . "กลุ่มเครือข่าย {$result['networkGroups']}, บัญชีผู้ใช้ {$result['users']}"
        );

        return self::SUCCESS;
    }
}
