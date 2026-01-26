<?php

namespace Database\Seeders;

use App\Enums\IndicatorLevel;
use App\Enums\PolicyType;
use App\Enums\Role;
use App\Enums\SupervisionStatus;
use App\Models\Indicator;
use App\Models\NetworkGroup;
use App\Models\Policy;
use App\Models\School;
use App\Models\Supervision;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ตรวจสอบว่ามีข้อมูลหรือไม่
        $hasData = User::count() > 0 || School::count() > 0 || Supervision::count() > 0;

        if ($hasData) {
            $this->command->info('Database already has data. Adding demo data...');
            $this->addDemoData();
        } else {
            $this->command->info('Database is empty. Creating initial data...');
            $this->createInitialData();
        }
    }

    private function createInitialData(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'ผู้ดูแลระบบ',
            'email' => 'admin@nitesa.local',
            'password' => Hash::make('password'),
            'role' => Role::ADMIN,
        ]);

        // Create supervisor users
        $supervisors = [];
        for ($i = 1; $i <= 3; $i++) {
            $supervisors[] = User::create([
                'name' => "ศึกษานิเทศก์ {$i}",
                'email' => "supervisor{$i}@nitesa.local",
                'password' => Hash::make('password'),
                'role' => Role::SUPERVISOR,
            ]);
        }

        // Create executive user
        User::create([
            'name' => 'ผู้บริหารระดับสูง',
            'email' => 'executive@nitesa.local',
            'password' => Hash::make('password'),
            'role' => Role::EXECUTIVE,
        ]);

        // Create school users
        $schoolUsers = [];
        for ($i = 1; $i <= 5; $i++) {
            $schoolUsers[] = User::create([
                'name' => "โรงเรียนทดสอบ {$i}",
                'email' => "school{$i}@nitesa.local",
                'password' => Hash::make('password'),
                'role' => Role::SCHOOL,
            ]);
        }

        // Create network groups
        $networkGroups = [];
        $networkGroupNames = [
            'กลุ่มเครือข่ายพัฒนาคุณภาพการศึกษา',
            'กลุ่มเครือข่ายส่งเสริมการอ่าน',
            'กลุ่มเครือข่ายนวัตกรรมและเทคโนโลยี',
            'กลุ่มเครือข่ายความปลอดภัยในสถานศึกษา',
            'กลุ่มเครือข่ายพัฒนาครูและบุคลากร',
        ];

        for ($i = 0; $i < count($networkGroupNames); $i++) {
            $networkGroups[] = NetworkGroup::create([
                'code' => sprintf('NG%03d', $i + 1),
                'name' => $networkGroupNames[$i],
                'description' => "กลุ่มเครือข่าย{$networkGroupNames[$i]}",
            ]);
        }

        // Create policies
        $policyData = [
            ['type' => PolicyType::STUDENT_DEVELOPMENT, 'code' => 'POL001', 'title' => 'การพัฒนาผู้เรียนให้มีคุณภาพตามมาตรฐานการศึกษา', 'description' => 'ส่งเสริมการพัฒนาผู้เรียนให้มีความรู้ความสามารถตามมาตรฐานการศึกษา'],
            ['type' => PolicyType::READING_CULTURE, 'code' => 'POL002', 'title' => 'การส่งเสริมการอ่านและวัฒนธรรมการอ่าน', 'description' => 'สร้างวัฒนธรรมการอ่านในสถานศึกษา'],
            ['type' => PolicyType::EDU_INNOV_TECH, 'code' => 'POL003', 'title' => 'การศึกษาเพื่อการพัฒนาทักษะในศตวรรษที่ 21', 'description' => 'ใช้เทคโนโลยีและนวัตกรรมในการจัดการเรียนรู้'],
            ['type' => PolicyType::SCHOOL_SAFETY, 'code' => 'POL004', 'title' => 'ความปลอดภัยในสถานศึกษา', 'description' => 'สร้างสภาพแวดล้อมที่ปลอดภัยสำหรับผู้เรียน'],
            ['type' => PolicyType::TEACHER_UPSKILL, 'code' => 'POL005', 'title' => 'การพัฒนาครูและบุคลากรทางการศึกษา', 'description' => 'ส่งเสริมการพัฒนาตนเองของครูและบุคลากร'],
            ['type' => PolicyType::SMART_GOVERNANCE, 'code' => 'POL006', 'title' => 'การบริหารจัดการสถานศึกษาอย่างมีประสิทธิภาพ', 'description' => 'บริหารจัดการด้วยระบบดิจิทัลและข้อมูล'],
            ['type' => PolicyType::SPECIAL_NEEDS_EDU, 'code' => 'POL007', 'title' => 'การศึกษาพิเศษและการศึกษาสำหรับผู้เรียนที่มีความต้องการพิเศษ', 'description' => 'ส่งเสริมการศึกษาที่เท่าเทียมสำหรับทุกคน'],
        ];

        $policies = [];
        foreach ($policyData as $data) {
            $policies[] = Policy::create([
                'type' => $data['type'],
                'code' => $data['code'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'isActive' => true,
            ]);
        }

        // Create schools
        $districts = ['เมืองสกลนคร', 'กุสุมาลย์', 'กุดบาก', 'คำตากล้า', 'ดงมะไฟ', 'นิคมน้ำอูน', 'บ้านม่วง', 'พรรณานิคม', 'พังโคน', 'ภูพาน', 'วานรนิวาส', 'วาริชภูมิ', 'สว่างแดนดิน', 'ส่องดาว', 'อากาศอำนวย'];
        $schools = [];

        for ($i = 1; $i <= 50; $i++) {
            $district = $districts[array_rand($districts)];
            $networkGroup = $networkGroups[array_rand($networkGroups)];

            $schools[] = School::create([
                'code' => sprintf('1044%06d', $i),
                'name' => "โรงเรียนบ้านทดสอบ {$i}",
                'province' => 'สกลนคร',
                'district' => $district,
                'subDistrict' => 'ตำบลทดสอบ',
                'address' => "หมู่ {$i} ตำบลทดสอบ อำเภอ{$district} จังหวัดสกลนคร",
                'phone' => sprintf('042-%04d', rand(1000, 9999)),
                'email' => "school{$i}@example.com",
                'principalName' => "ผอ.ทดสอบ {$i}",
                'studentCount' => rand(50, 800),
                'teacherCount' => rand(5, 40),
                'networkGroupId' => $networkGroup->id,
            ]);
        }

        // Assign schools to supervisors
        $schoolsPerSupervisor = ceil(count($schools) / count($supervisors));
        foreach ($supervisors as $index => $supervisor) {
            $startIndex = $index * $schoolsPerSupervisor;
            $endIndex = min($startIndex + $schoolsPerSupervisor, count($schools));
            $assignedSchools = array_slice($schools, $startIndex, $endIndex - $startIndex);
            $supervisor->assignedSchools()->attach(
                array_map(fn($s) => $s->id, $assignedSchools)
            );
        }

        // Create supervisions with indicators
        $supervisionTypes = ['นิเทศติดตาม', 'นิเทศกำกับ', 'นิเทศให้คำปรึกษา', 'นิเทศประเมินผล'];
        $indicatorNames = [
            'ด้านการบริหารจัดการ',
            'ด้านหลักสูตรและการจัดการเรียนรู้',
            'ด้านการวัดและประเมินผล',
            'ด้านคุณภาพผู้เรียน',
            'ด้านสภาพแวดล้อมและแหล่งเรียนรู้',
        ];
        $levels = [IndicatorLevel::EXCELLENT, IndicatorLevel::GOOD, IndicatorLevel::FAIR, IndicatorLevel::NEEDS_WORK];
        $statuses = [SupervisionStatus::DRAFT, SupervisionStatus::SUBMITTED, SupervisionStatus::APPROVED, SupervisionStatus::PUBLISHED];

        $academicYears = ['2567', '2568', '2569'];

        for ($i = 0; $i < 30; $i++) {
            $supervisor = $supervisors[array_rand($supervisors)];
            $school = $schools[array_rand($schools)];
            $status = $statuses[array_rand($statuses)];
            $academicYear = $academicYears[array_rand($academicYears)];

            $supervision = Supervision::create([
                'schoolId' => $school->id,
                'userId' => $supervisor->id,
                'type' => $supervisionTypes[array_rand($supervisionTypes)],
                'date' => now()->subDays(rand(1, 365)),
                'academicYear' => $academicYear,
                'ministerPolicyId' => rand(0, 1) ? $policies[array_rand($policies)]->id : null,
                'obecPolicyId' => rand(0, 1) ? $policies[array_rand($policies)]->id : null,
                'areaPolicyId' => rand(0, 1) ? $policies[array_rand($policies)]->id : null,
                'summary' => "สรุปผลการนิเทศ: โรงเรียน{$school->name} มีการจัดการเรียนการสอนที่ดี มีการพัฒนาคุณภาพการศึกษาอย่างต่อเนื่อง มีจุดเด่นในการจัดการเรียนรู้ที่เน้นผู้เรียนเป็นสำคัญ",
                'suggestions' => "ข้อเสนอแนะ: ควรส่งเสริมการใช้นวัตกรรมในการจัดการเรียนรู้เพิ่มเติม และพัฒนาระบบการประเมินผลให้มีความหลากหลายมากขึ้น",
                'status' => $status,
            ]);

            // Create indicators for this supervision
            $selectedIndicators = array_slice($indicatorNames, 0, rand(3, 5));
            foreach ($selectedIndicators as $indicatorName) {
                Indicator::create([
                    'supervisionId' => $supervision->id,
                    'name' => $indicatorName,
                    'level' => $levels[array_rand($levels)],
                    'comment' => rand(0, 1) ? "หมายเหตุ: {$indicatorName} มีการพัฒนาอย่างต่อเนื่อง" : null,
                ]);
            }
        }

        $this->command->info('Initial data created successfully!');
        $this->command->info('Users: ' . User::count());
        $this->command->info('Schools: ' . School::count());
        $this->command->info('Policies: ' . Policy::count());
        $this->command->info('Supervisions: ' . Supervision::count());
        $this->command->info('Indicators: ' . Indicator::count());
    }

    private function addDemoData(): void
    {
        // เพิ่มข้อมูล demo ถ้ายังมีน้อย
        $currentSupervisions = Supervision::count();
        $targetSupervisions = 50; // เป้าหมาย 50 รายการ

        if ($currentSupervisions < $targetSupervisions) {
            $this->command->info("Current supervisions: {$currentSupervisions}. Adding more to reach {$targetSupervisions}...");

            $supervisors = User::where('role', Role::SUPERVISOR)->get();
            $schools = School::all();
            $policies = Policy::where('isActive', true)->get();

            if ($supervisors->isEmpty()) {
                $this->command->info('Creating supervisor users...');
                for ($i = 1; $i <= 3; $i++) {
                    User::create([
                        'name' => "ศึกษานิเทศก์ {$i}",
                        'email' => "supervisor{$i}@nitesa.local",
                        'password' => Hash::make('password'),
                        'role' => Role::SUPERVISOR,
                    ]);
                }
                $supervisors = User::where('role', Role::SUPERVISOR)->get();
            }

            if ($schools->isEmpty()) {
                $this->command->info('Creating schools...');
                $networkGroups = NetworkGroup::all();
                if ($networkGroups->isEmpty()) {
                    for ($i = 1; $i <= 5; $i++) {
                        NetworkGroup::create([
                            'code' => sprintf('NG%03d', $i),
                            'name' => "กลุ่มเครือข่ายที่ {$i}",
                            'description' => "กลุ่มเครือข่ายโรงเรียนกลุ่มที่ {$i}",
                        ]);
                    }
                    $networkGroups = NetworkGroup::all();
                }

                $districts = ['เมืองสกลนคร', 'กุสุมาลย์', 'กุดบาก', 'คำตากล้า', 'ดงมะไฟ'];
                for ($i = 1; $i <= 50; $i++) {
                    School::create([
                        'code' => sprintf('1044%06d', $i),
                        'name' => "โรงเรียนบ้านทดสอบ {$i}",
                        'province' => 'สกลนคร',
                        'district' => $districts[array_rand($districts)],
                        'subDistrict' => 'ตำบลทดสอบ',
                        'principalName' => "ผอ.ทดสอบ {$i}",
                        'studentCount' => rand(50, 800),
                        'teacherCount' => rand(5, 40),
                        'networkGroupId' => $networkGroups->random()->id,
                        'isActive' => true,
                    ]);
                }
                $schools = School::all();
            }

            $supervisionTypes = ['นิเทศติดตาม', 'นิเทศกำกับ', 'นิเทศให้คำปรึกษา', 'นิเทศประเมินผล'];
            $indicatorNames = [
                'ด้านการบริหารจัดการ',
                'ด้านหลักสูตรและการจัดการเรียนรู้',
                'ด้านการวัดและประเมินผล',
                'ด้านคุณภาพผู้เรียน',
                'ด้านสภาพแวดล้อมและแหล่งเรียนรู้',
            ];
            $levels = [IndicatorLevel::EXCELLENT, IndicatorLevel::GOOD, IndicatorLevel::FAIR, IndicatorLevel::NEEDS_WORK];
            $statuses = [SupervisionStatus::DRAFT, SupervisionStatus::SUBMITTED, SupervisionStatus::APPROVED, SupervisionStatus::PUBLISHED];
            $academicYears = ['2567', '2568', '2569'];

            $toCreate = $targetSupervisions - $currentSupervisions;
            $this->command->info("Creating {$toCreate} more supervisions...");

            for ($i = 0; $i < $toCreate; $i++) {
                $supervisor = $supervisors->random();
                $school = $schools->random();
                $status = $statuses[array_rand($statuses)];
                $academicYear = $academicYears[array_rand($academicYears)];

                $supervision = Supervision::create([
                    'schoolId' => $school->id,
                    'userId' => $supervisor->id,
                    'type' => $supervisionTypes[array_rand($supervisionTypes)],
                    'date' => now()->subDays(rand(1, 365)),
                    'academicYear' => $academicYear,
                    'ministerPolicyId' => $policies->isNotEmpty() && rand(0, 1) ? $policies->random()->id : null,
                    'obecPolicyId' => $policies->isNotEmpty() && rand(0, 1) ? $policies->random()->id : null,
                    'areaPolicyId' => $policies->isNotEmpty() && rand(0, 1) ? $policies->random()->id : null,
                    'summary' => "สรุปผลการนิเทศ: โรงเรียน{$school->name} มีการจัดการเรียนการสอนที่ดี มีการพัฒนาคุณภาพการศึกษาอย่างต่อเนื่อง มีจุดเด่นในการจัดการเรียนรู้ที่เน้นผู้เรียนเป็นสำคัญ",
                    'suggestions' => "ข้อเสนอแนะ: ควรส่งเสริมการใช้นวัตกรรมในการจัดการเรียนรู้เพิ่มเติม และพัฒนาระบบการประเมินผลให้มีความหลากหลายมากขึ้น",
                    'status' => $status,
                ]);

                // Create indicators
                $selectedIndicators = array_slice($indicatorNames, 0, rand(3, 5));
                foreach ($selectedIndicators as $indicatorName) {
                    Indicator::create([
                        'supervisionId' => $supervision->id,
                        'name' => $indicatorName,
                        'level' => $levels[array_rand($levels)],
                        'comment' => rand(0, 1) ? "หมายเหตุ: {$indicatorName} มีการพัฒนาอย่างต่อเนื่อง" : null,
                    ]);
                }
            }

            $this->command->info("Created {$toCreate} supervisions with indicators!");
        }

        // เพิ่มโรงเรียนถ้ายังมีน้อย
        $currentSchools = School::count();
        $targetSchools = 200; // เป้าหมาย 200 โรงเรียน

        if ($currentSchools < $targetSchools) {
            $this->command->info("Current schools: {$currentSchools}. Adding more to reach {$targetSchools}...");
            $networkGroups = NetworkGroup::all();
            $districts = ['เมืองสกลนคร', 'กุสุมาลย์', 'กุดบาก', 'คำตากล้า', 'ดงมะไฟ', 'นิคมน้ำอูน', 'บ้านม่วง', 'พรรณานิคม', 'พังโคน', 'ภูพาน', 'วานรนิวาส', 'วาริชภูมิ', 'สว่างแดนดิน', 'ส่องดาว', 'อากาศอำนวย'];

            if ($networkGroups->isEmpty()) {
                $networkGroupNames = [
                    'กลุ่มเครือข่ายพัฒนาคุณภาพการศึกษา',
                    'กลุ่มเครือข่ายส่งเสริมการอ่าน',
                    'กลุ่มเครือข่ายนวัตกรรมและเทคโนโลยี',
                    'กลุ่มเครือข่ายความปลอดภัยในสถานศึกษา',
                    'กลุ่มเครือข่ายพัฒนาครูและบุคลากร',
                ];
                for ($i = 0; $i < count($networkGroupNames); $i++) {
                    NetworkGroup::create([
                        'code' => sprintf('NG%03d', $i + 1),
                        'name' => $networkGroupNames[$i],
                        'description' => "กลุ่มเครือข่าย{$networkGroupNames[$i]}",
                    ]);
                }
                $networkGroups = NetworkGroup::all();
            }

            $toCreate = $targetSchools - $currentSchools;
            $this->command->info("Creating {$toCreate} more schools...");

            $maxCode = School::max('code') ? (int) substr(School::max('code'), -6) : 0;
            for ($i = 1; $i <= $toCreate; $i++) {
                $newCode = $maxCode + $i;
                School::create([
                    'code' => sprintf('1044%06d', $newCode),
                    'name' => "โรงเรียนบ้านทดสอบ {$newCode}",
                    'province' => 'สกลนคร',
                    'district' => $districts[array_rand($districts)],
                    'subDistrict' => 'ตำบลทดสอบ',
                    'address' => "หมู่ {$i} ตำบลทดสอบ อำเภอ{$districts[array_rand($districts)]} จังหวัดสกลนคร",
                    'phone' => sprintf('042-%04d', rand(1000, 9999)),
                    'email' => "school{$newCode}@example.com",
                    'principalName' => "ผอ.ทดสอบ {$newCode}",
                    'studentCount' => rand(50, 800),
                    'teacherCount' => rand(5, 40),
                    'networkGroupId' => $networkGroups->random()->id,
                ]);
            }
            $this->command->info("Created {$toCreate} schools!");
        }

        // Assign schools to supervisors if not assigned
        $supervisors = User::where('role', Role::SUPERVISOR)->get();
        foreach ($supervisors as $supervisor) {
            $assignedCount = $supervisor->assignedSchools()->count();
            if ($assignedCount === 0) {
                $this->command->info("Assigning schools to supervisor: {$supervisor->name}...");
                $schools = School::all();
                $schoolsPerSupervisor = ceil($schools->count() / $supervisors->count());
                $startIndex = ($supervisors->search($supervisor)) * $schoolsPerSupervisor;
                $assignedSchools = $schools->slice($startIndex, $schoolsPerSupervisor);
                $supervisor->assignedSchools()->attach($assignedSchools->pluck('id'));
            }
        }
    }
}
