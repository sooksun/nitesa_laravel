<?php

namespace App\Console\Commands;

use App\Enums\ExamType;
use App\Services\Exam\InvalidExamFileException;
use App\Services\ExamResultImporter;
use Illuminate\Console\Command;

class ImportExamResults extends Command
{
    protected $signature = 'exam:import
        {type : ประเภทการสอบ: rt, nt, onet-p6, onet-m3}
        {file : path ของไฟล์ Excel}
        {--year= : ปีการศึกษา (พ.ศ.) ค่าเริ่มต้นคือปีที่แล้ว}
        {--sync-schools : เพิ่มโรงเรียนที่ยังไม่มีในระบบจากไฟล์ RT}
        {--dry-run : ตรวจสอบการจับคู่โดยไม่บันทึก}';

    protected $description = 'นำเข้าผลการทดสอบระดับชาติ (RT / NT / O-NET) จากไฟล์ Excel';

    public function handle(ExamResultImporter $importer): int
    {
        try {
            $type = ExamType::fromSlug((string) $this->argument('type'));
        } catch (\ValueError $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $year = (string) ($this->option('year') ?: (int) date('Y') + 542);
        if (! preg_match('/^\d{4}$/', $year)) {
            $this->error('ปีการศึกษาต้องเป็นตัวเลข 4 หลัก (พ.ศ.)');

            return self::FAILURE;
        }

        try {
            $summary = $importer->import(
                $type,
                (string) $this->argument('file'),
                $year,
                (bool) $this->option('sync-schools'),
                (bool) $this->option('dry-run'),
            );
        } catch (InvalidExamFileException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info(($summary['dryRun'] ? '[ตรวจสอบ ยังไม่บันทึก] ' : '') . "{$type->label()} ปีการศึกษา {$year}");
        $this->line("แถวโรงเรียนในไฟล์: {$summary['total']}");
        $this->line("นำเข้า: {$summary['imported']} (จับคู่ด้วยรหัส {$summary['matchedBy']['code']}, ชื่อ {$summary['matchedBy']['name']}, alias {$summary['matchedBy']['alias']})");
        $this->line("ค่าเปรียบเทียบ (เขต/สังกัด/ประเทศ): {$summary['benchmarks']}");

        if ($summary['schoolsCreated'] !== []) {
            $this->warn('โรงเรียนที่' . ($summary['dryRun'] ? 'จะ' : '') . 'สร้างใหม่: ' . count($summary['schoolsCreated']));
            foreach ($summary['schoolsCreated'] as $name) {
                $this->line("  + {$name}");
            }
        }

        if ($summary['nameDiffs'] !== []) {
            $this->warn('จับคู่ได้แต่ชื่อในไฟล์ต่างจากชื่อในระบบ กรุณาตรวจว่าเป็นโรงเรียนเดียวกัน: ' . count($summary['nameDiffs']));
            foreach ($summary['nameDiffs'] as $diff) {
                $this->line("  แถว {$diff['row']}: {$diff['fileName']} -> {$diff['systemName']} (จับคู่ด้วย {$diff['by']})");
            }
        }

        if ($summary['unmatched'] !== []) {
            $this->warn('จับคู่ไม่ได้: ' . count($summary['unmatched']));
            foreach ($summary['unmatched'] as $row) {
                $this->line("  แถว {$row['row']}: {$row['name']} — {$row['reason']}");
            }
        }

        return self::SUCCESS;
    }
}
