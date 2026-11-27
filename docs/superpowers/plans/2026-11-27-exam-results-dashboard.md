# ผลการทดสอบระดับชาติ (RT / NT / O-NET) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** นำผลสอบ RT/NT/O-NET เข้าตาราง `examresult` ผูกกับ `school` แล้วแสดงบน dashboard และหน้าโรงเรียน โดย ADMIN อัปโหลดไฟล์เองได้

**Architecture:** ตัวอ่านไฟล์ (pure, รับ array ของแถว) → ตัวจับคู่โรงเรียน (pure, รับรายชื่อโรงเรียน) → `ExamResultImporter` (เขียน DB ใน transaction, มี dry run) ซึ่งถูกเรียกจากคำสั่ง artisan และหน้า Livewire นำเข้าเดิม หน้า dashboard เป็น Livewire full-page component ใหม่ที่ query ตาราง `examresult` ตามขอบเขตของผู้ใช้

**Tech Stack:** Laravel 10, PHP 8.1+, Livewire 3, Tailwind, Chart.js, maatwebsite/excel, MySQL, PHPUnit 10

**Spec:** `docs/superpowers/specs/2026-11-27-exam-results-dashboard-design.md`

## Global Constraints

- แบบแผน schema ของ repo: ตารางตัวเล็กเอกพจน์, PK เป็น ULID 26 ตัวสร้างใน `boot()`, คอลัมน์ camelCase, `createdAt`/`updatedAt`, model ตั้ง `$table`, `$keyType = 'string'`, `$incrementing = false`
- route ที่จำกัด role ใช้ middleware `role:` เท่านั้น
- ข้อความ UI ภาษาไทย ปีเป็น พ.ศ.
- test อัตโนมัติห้ามเขียนฐานข้อมูล (PHPUnit ชี้ไปที่ DB ตาม `.env`) — ใช้ `PHPUnit\Framework\TestCase` ตรง ๆ กับ class ที่ pure
- ไม่ลบและไม่แก้ `code`/`name` ของโรงเรียนที่มีอยู่
- ไม่ commit ไฟล์ใน `doc/`
- ไม่มี `console.log`, ไม่มี comment ที่ไม่จำเป็น, ไม่มี emoji
- commit เมื่อผู้ใช้สั่งเท่านั้น (ขั้น "Commit" ในแผนนี้คือการ stage + รอคำสั่ง)

## Review Focus

1. อัปโหลดไฟล์ผิดชนิด (เช่น เลือก NT แต่ส่งไฟล์ O-NET) → ปฏิเสธทั้งไฟล์พร้อมข้อความ ไม่เขียนอะไร (test ใน Task 2)
2. คะแนนเป็น `*`, `x`, ว่าง → เก็บ null ไม่ใช่ 0 และไม่ทำให้ทั้งไฟล์ล้ม (test ใน Task 2)
3. โรงเรียนในระบบสองแห่งชื่อ normalize แล้วเหมือนกัน → กำกวม ไม่นำเข้า ไม่สร้าง (test ใน Task 3)
4. หัวตารางซ้ำกลางไฟล์ NT และแถวว่างท้ายไฟล์ RT → ไม่ถูกนับเป็นแถวข้อมูล (test ใน Task 2)
5. ชื่อในระบบมีคำว่า "โรงเรียน" นำหน้าหรือเว้นวรรค/วงเล็บต่างจากไฟล์ → ยังจับคู่ได้ (test ใน Task 3)

---

### Task 1: Schema และ model

**Files:**
- Create: `app/Enums/ExamType.php`, `app/Enums/ExamScope.php`
- Create: `database/migrations/2026_11_27_000001_create_examresult_table.php`
- Create: `app/Models/ExamResult.php`
- Modify: `app/Models/School.php` (เพิ่ม relation)

**Interfaces:**
- Produces:
  - `enum ExamType: string { RT='RT'; NT='NT'; ONET_P6='ONET_P6'; ONET_M3='ONET_M3'; }` พร้อม `label(): string` (`RT ป.1`, `NT ป.3`, `O-NET ป.6`, `O-NET ม.3`), `subjects(): array<string,string>` (คีย์คอลัมน์ → ป้ายไทย ตามลำดับแสดงผล: RT `readAloud`,`readComprehension`; NT `math`,`thai`; O-NET `thai`,`english`,`math`,`science`), `hasExaminees(): bool` (RT, NT), `hasQualityLevel(): bool` (RT, NT), `static fromSlug(string): self` (`rt|nt|onet-p6|onet-m3`)
  - `enum ExamScope: string { SCHOOL; AREA; PROVINCE; OBEC; NATIONAL }` พร้อม `label()`
  - `ExamResult` — fillable ทุกคอลัมน์ในสเปก, casts `examType`→`ExamType`, `scope`→`ExamScope`, คะแนน→`float`, `examinees`→`integer`; `school(): BelongsTo`
  - `School::examResults(): HasMany` (FK `schoolId`)

- [ ] **Step 1:** เขียน enum ทั้งสอง
- [ ] **Step 2:** เขียน migration ตามตารางในสเปก (หัวข้อ "ตาราง `examresult`") — index (`examType`,`academicYear`,`scope`) และ (`schoolId`); FK `schoolId` → `school.id` `cascadeOnDelete`
- [ ] **Step 3:** เขียน model และ relation
- [ ] **Step 4:** Run `php artisan migrate` — Expected: ตาราง `examresult` ถูกสร้าง; `php artisan tinker --execute="echo App\Models\ExamResult::count();"` พิมพ์ `0`

### Task 2: ตัวอ่านไฟล์

**Files:**
- Create: `app/Services/Exam/SchoolNameNormalizer.php`
- Create: `app/Services/Exam/ExamFileParser.php`
- Create: `app/Services/Exam/NtQualityLevel.php`
- Test: `tests/Unit/Exam/ExamFileParserTest.php`, `tests/Unit/Exam/SchoolNameNormalizerTest.php`, `tests/Unit/Exam/NtQualityLevelTest.php`

**Interfaces:**
- Produces:
  - `SchoolNameNormalizer::normalize(?string $name): string` — ตัด whitespace ทุกชนิด, `"` `'` `“` `”` `(` `)` `.` และคำว่า `โรงเรียน`
  - `NtQualityLevel::fromAverage(?float $avg): ?string` — ≥68.5 `ดีมาก`, ≥47 `ดี`, ≥27.5 `พอใช้`, ต่ำกว่า `ปรับปรุง`, null → null
  - `ExamFileParser::parse(ExamType $type, array $rows): array{schools: list<array>, benchmarks: list<array>}`
    - แต่ละ school row: `['row'=>int (เลขแถวใน Excel เริ่ม 1), 'code'=>?string, 'name'=>string, 'district'=>?string, 'examinees'=>?int, 'scores'=>array<string,?float> (คีย์ตาม `ExamType::subjects()`), 'average'=>?float, 'qualityLevel'=>?string]`
    - แต่ละ benchmark row: `['scope'=>ExamScope, 'examinees'=>?int, 'scores'=>..., 'average'=>?float, 'qualityLevel'=>?string, 'name'=>string]`
    - โยน `App\Services\Exam\InvalidExamFileException` (ข้อความไทย) เมื่อหาแถวหัวตารางไม่เจอหรือหัวคอลัมน์ไม่ตรงชนิด
  - ตำแหน่งคอลัมน์ (0-based) หลังหาแถวหัวตารางที่มีช่อง `ชื่อโรงเรียน`:
    - RT: 0 ที่, 1 รหัส, 2 ชื่อ, 3 อำเภอ, 4 ผู้เข้าสอบ, 5 readAloud, 6 readComprehension, 7 เฉลี่ย, 8 ระดับ — ตรวจชนิดด้วยคำว่า `อ่านออกเสียง` ใน 3 แถวหัว
    - NT: 1 ลำดับ, 2 ชื่อ, 3 อำเภอ, 4 ผู้เข้าสอบ, 5 math, 6 thai, 7 เฉลี่ย — ตรวจด้วยหัวตารางที่ `ชื่อโรงเรียน` อยู่คอลัมน์ 2 และไม่มีช่อง `รหัสโรงเรียน`; `qualityLevel` = `NtQualityLevel::fromAverage`
    - O-NET: 0 ที่, 1 รหัส, 2 ชื่อ, 3 thai, 4 english, 5 math, 6 science, 7 เฉลี่ย — ตรวจด้วยช่อง `ภาษาอังกฤษ`
  - แถวข้อมูล = ช่องลำดับเป็นตัวเลข; แถว benchmark = ช่องแรกที่ไม่ว่างขึ้นต้นด้วย `ระดับเขตพื้นที่` (AREA), `ระดับจังหวัด` (PROVINCE), `ระดับสังกัด` (OBEC), `ระดับประเทศ` (NATIONAL)
  - ค่าคะแนนที่ไม่ใช่ตัวเลข → null; ตัวเลขปัด 2 ตำแหน่ง; `code` cast เป็น string

- [ ] **Step 1: เขียน test ที่ล้มเหลว** — fixtures เป็น array ของแถวใน test เอง

```php
// SchoolNameNormalizerTest
assertSame('บ้านนาคุรุราษฎรอุทิศวิทยายน', SchoolNameNormalizer::normalize('โรงเรียนบ้านนา (คุรุราษฎรอุทิศวิทยายน)'));
assertSame('บ้านกลางผดุงราษฎร์วิทยา', SchoolNameNormalizer::normalize(' บ้านกลาง "ผดุงราษฎร์วิทยา" '));
assertSame('', SchoolNameNormalizer::normalize(null));

// NtQualityLevelTest
assertSame('ดีมาก', NtQualityLevel::fromAverage(68.5));
assertSame('ดี', NtQualityLevel::fromAverage(68.49));
assertSame('ดี', NtQualityLevel::fromAverage(47.0));
assertSame('พอใช้', NtQualityLevel::fromAverage(27.5));
assertSame('ปรับปรุง', NtQualityLevel::fromAverage(27.49));
assertNull(NtQualityLevel::fromAverage(null));

// ExamFileParserTest
test_parses_rt_rows_and_four_benchmarks      // 2 โรงเรียน + 4 แถวระดับ + แถวว่าง 5 แถว → schools 2, benchmarks 4 (AREA examinees 2965, average 80.2, PROVINCE มี)
test_parses_nt_with_repeated_header          // หัวตาราง 3 แถว, 2 โรงเรียน, หัวตารางซ้ำ, 1 โรงเรียน, แถวหมายเหตุ → schools 3, code null, qualityLevel คำนวณ (53.39 → 'ดี')
test_parses_onet_rows_and_three_benchmarks   // row 1047010001 thai 34.74 english 29.6 math 15.29 science 23.24 average 25.72; AREA average 33.39
test_non_numeric_scores_become_null          // '*', 'x', '' → null และแถวยังถูกคืน
test_rejects_file_of_another_exam_type       // parse(NT, แถว O-NET) → InvalidExamFileException
test_rejects_rows_without_header             // [] และแถวมั่ว → InvalidExamFileException
```

- [ ] **Step 2:** Run `php artisan test tests/Unit/Exam` — Expected: FAIL (class not found)
- [ ] **Step 3:** เขียน class ทั้งสามและ exception
- [ ] **Step 4:** Run `php artisan test tests/Unit/Exam` — Expected: PASS
- [ ] **Step 5:** ตรวจกับไฟล์จริงด้วย tinker: `Excel::toArray` sheet แรกของแต่ละไฟล์ใน `doc/` แล้ว `parse` — Expected จำนวน schools/benchmarks: RT 174/4, NT 174/0, O-NET ป.6 173/3, O-NET ม.3 65/3

### Task 3: ตัวจับคู่, importer, คำสั่ง artisan

**Files:**
- Create: `config/exam.php`
- Create: `app/Services/Exam/SchoolMatcher.php`
- Create: `app/Services/ExamResultImporter.php`
- Create: `app/Console/Commands/ImportExamResults.php`
- Test: `tests/Unit/Exam/SchoolMatcherTest.php`

**Interfaces:**
- Consumes: `ExamFileParser::parse`, `SchoolNameNormalizer::normalize`, `ExamResult`, `ExamType`, `ExamScope`
- Produces:
  - `config('exam.aliases')`: `array<string,string>` — คีย์และค่าเป็นชื่อที่ **normalize แล้ว** (ชื่อในไฟล์ → ชื่อโรงเรียนในระบบ)
  - `new SchoolMatcher(iterable $schools, array $aliases)` — `$schools` คือรายการ `['id'=>string,'code'=>string,'name'=>string,'district'=>?string]`
  - `SchoolMatcher::match(?string $code, string $name, ?string $district): array{status:'matched'|'none'|'ambiguous', schoolId:?string, by:?string}` — `by` ∈ `code|name|alias`; ลำดับ: รหัส → ชื่อ normalize (ถ้าทั้งสองฝั่งมีอำเภอต้องตรง) → alias (แปลงชื่อแล้วจับคู่ชื่อซ้ำอีกรอบ); เจอ >1 = `ambiguous`
  - `SchoolMatcher::add(array $school): void` — ให้ importer เพิ่มโรงเรียนที่เพิ่งสร้าง
  - `ExamResultImporter::import(ExamType $type, string $path, string $academicYear, bool $syncSchools = false, bool $dryRun = false): array`
    คืน `['total'=>int, 'imported'=>int, 'matchedBy'=>['code'=>int,'name'=>int,'alias'=>int], 'schoolsCreated'=>list<string>, 'unmatched'=>list<array{row:int,name:string,reason:string}>, 'benchmarks'=>int, 'dryRun'=>bool]`
    - `$syncSchools` มีผลเฉพาะ `ExamType::RT`: แถว `none` → สร้าง `School` (code RT, name ตามไฟล์, district, province `สกลนคร`) แล้วนำเข้า; แถวที่ matched และโรงเรียนเดิม `district` ว่าง → เติม district
    - แถว school: `ExamResult::updateOrCreate(['examType','academicYear','scope'=>SCHOOL,'schoolId'], [...])`; แถว benchmark: คีย์เดียวกันโดย `schoolId` null
    - ทั้งหมดใน `DB::transaction`; dry run ทำทุกขั้นแต่ไม่เขียน (ใช้ทางแยกในโค้ด ไม่ใช่ rollback เพื่อให้ `schoolsCreated` รายงานได้โดยไม่แตะ DB)
    - `InvalidExamFileException` ปล่อยให้ผู้เรียกจับ
  - `php artisan exam:import {type} {file} {--year=} {--sync-schools} {--dry-run}` — `--year` ค่าเริ่มต้น = พ.ศ. ปัจจุบัน − 1; พิมพ์สรุปและรายชื่อ `schoolsCreated`/`unmatched`; exit 1 เมื่อไฟล์ไม่ถูกชนิดหรือไม่พบไฟล์

- [ ] **Step 1: เขียน test ที่ล้มเหลว**

```php
// SchoolMatcherTest — schools: A(code 1047540001, 'อนุบาลสกลนคร', 'เมืองสกลนคร'), B(code X1, 'โรงเรียนบ้านนา (คุรุราษฎร)', null),
//                     C(code X2, 'บ้านสะพานสาม', 'ภูพาน'), D1/D2 ชื่อ 'บ้านซ้ำ' สองแห่ง อำเภอเดียวกัน
test_matches_by_code_first                 // match('1047540001','ชื่ออื่น',null) → matched A by code
test_matches_by_name_ignoring_prefix_and_punctuation  // match('999','บ้านนา คุรุราษฎร',null) → matched B by name
test_district_must_agree_when_both_present // match(null,'บ้านสะพานสาม','กุดบาก') → none
test_matches_by_alias                      // aliases ['บัานสะพานสาม'=>'บ้านสะพานสาม'] → match(null,'บัานสะพานสาม','ภูพาน') → matched C by alias
test_duplicate_names_are_ambiguous         // match(null,'บ้านซ้ำ',null) → ambiguous, schoolId null
test_unknown_is_none
test_added_school_is_matchable
```

- [ ] **Step 2:** Run `php artisan test tests/Unit/Exam/SchoolMatcherTest.php` — Expected: FAIL
- [ ] **Step 3:** เขียน `SchoolMatcher`, `config/exam.php` (aliases ว่างไว้ก่อน)
- [ ] **Step 4:** Run test — Expected: PASS
- [ ] **Step 5:** เขียน `ExamResultImporter` และคำสั่ง artisan
- [ ] **Step 6:** Dry run RT: `php artisan exam:import rt "doc/ผล RT 2568.xlsx" --year=2568 --sync-schools --dry-run` — Expected: total 174, schoolsCreated 174 รายการ, `School::count()` ยังเป็น 50, `ExamResult::count()` 0
- [ ] **Step 7:** นำเข้าจริง RT พร้อม `--sync-schools` แล้ว dry run NT, O-NET ป.6, O-NET ม.3 — Expected unmatched ตรงกับรายชื่อในสเปก (NT 3, ป.6 4, ม.3 2)
- [ ] **Step 8:** หาคู่ของชื่อที่ไม่ตรงจากรายชื่อ RT ในฐานข้อมูล (ค้นด้วย `like` ส่วนของชื่อ + อำเภอ) ใส่ `config/exam.php` เฉพาะรายที่มีผู้สมัครเดียวและชื่อต่างกันแค่การสะกด/คำต่อท้าย รายที่ไม่ชัดให้บันทึกไว้รายงานผู้ใช้
- [ ] **Step 9:** นำเข้าจริง NT, O-NET ป.6, O-NET ม.3 แล้วรันทั้ง 4 คำสั่งซ้ำอีกรอบ — Expected: `ExamResult::count()` เท่าเดิมทั้งสองรอบ, `School::count()` = 224
- [ ] **Step 10:** จำลองเครื่องจริง: แก้ `code` ของโรงเรียนจริง 3 แห่งเป็นค่าอื่นด้วย tinker แล้วรัน RT `--sync-schools --dry-run` — Expected: schoolsCreated ว่าง, matchedBy name = 3; คืนค่า `code` เดิม

### Task 4: หน้าอัปโหลด

**Files:**
- Modify: `app/Livewire/Import/ImportIndex.php`
- Modify: `resources/views/livewire/import/import-index.blade.php`

**Interfaces:**
- Consumes: `ExamResultImporter::import`, `ExamType::fromSlug`, `InvalidExamFileException`

- [ ] **Step 1:** ใน `ImportIndex` เพิ่ม property `public string $academicYear` (mount: พ.ศ. ปัจจุบัน − 1), `public bool $syncSchools = false`, `public array $examSummary = []`; ขยาย validation `importType` ให้รับ `exam_rt,exam_nt,exam_onet_p6,exam_onet_m3`; `academicYear` `required_if` ชนิด exam, `digits:4`
- [ ] **Step 2:** เพิ่ม method `preview()` (dry run) และแยกทางใน `import()` เมื่อ `importType` ขึ้นต้นด้วย `exam_` ให้เรียก importer; แปลงผลเป็น `$results` (`total`,`imported`,`failed` = จำนวน unmatched), `$importErrors` (`แถว N: ชื่อ — เหตุผล`), `$examSummary` (`schoolsCreated`, `matchedBy`, `dryRun`); จับ `InvalidExamFileException` ใส่ `$importErrors`
- [ ] **Step 3:** ใน view เพิ่มตัวเลือก 4 ชนิดในกลุ่ม "ผลการทดสอบระดับชาติ", ช่องปีการศึกษา, ช่องติ๊ก "เพิ่มโรงเรียนที่ยังไม่มีจากไฟล์นี้" (เฉพาะ `exam_rt`), ปุ่ม "ตรวจสอบก่อนนำเข้า", กล่องสรุปที่แสดงรายชื่อโรงเรียนที่จะสร้าง/สร้างแล้ว และป้าย "ยังไม่ได้บันทึก" เมื่อเป็น dry run ลิงก์ดาวน์โหลด template เดิมซ่อนเมื่อเลือกชนิด exam
- [ ] **Step 4:** ตรวจในเบราว์เซอร์ (login ADMIN, `/import`): อัปโหลด `ผลONET_ป.6ปีกศ.2568.xls` ด้วยชนิด NT → เห็นข้อความปฏิเสธ; ชนิด O-NET ป.6 กด "ตรวจสอบก่อนนำเข้า" → เห็นสรุปและป้าย "ยังไม่ได้บันทึก"; กด "นำเข้า" → swal สำเร็จ จำนวนแถวไม่เพิ่ม

### Task 5: หน้า dashboard

**Files:**
- Create: `app/Livewire/ExamResult/ExamResultDashboard.php`
- Create: `resources/views/livewire/exam-result/exam-result-dashboard.blade.php`
- Modify: `routes/web.php` (ในกลุ่ม `auth`: `Route::get('/exam-results', ExamResultDashboard::class)->name('exam-results.index');`)
- Modify: `resources/views/layouts/partials/sidebar.blade.php` (รายการ "ผลการทดสอบระดับชาติ" ถัดจาก "รายงาน")

**Interfaces:**
- Consumes: `ExamResult`, `ExamType`, `ExamScope`, `User::scopedSchoolIds()`
- Produces: route `exam-results.index`; query string `?year=&type=&district=`

- [ ] **Step 1:** Component — public: `academicYear`, `examType` (ค่าเริ่ม `ONET_P6`), `district`, `search`, `sortBy` (ค่าเริ่ม `average`), `sortDir` (`desc`), ใช้ `WithPagination` 25 แถว; `mount` ตั้งปี = ปีล่าสุดที่มีข้อมูล; `sort(string $column)` รับเฉพาะคอลัมน์ใน whitelist (`name`,`district`,`examinees`,`average`,`qualityLevel` + `ExamType::subjects()` ของชนิดปัจจุบัน); เปลี่ยนตัวกรองแล้ว `resetPage`
- [ ] **Step 2:** Query — ฐาน: `ExamResult` scope `SCHOOL` join `school` กรองปี/ชนิด/`scopedSchoolIds()`/อำเภอ/ค้นชื่อ. Computed:
  - `benchmarks`: แถว scope ≠ SCHOOL ของปี/ชนิด keyed ตาม scope; ถ้าไม่มี `AREA` (NT) คำนวณจากทุกโรงเรียนของเขต (ไม่กรองขอบเขต/อำเภอ) ถ่วงน้ำหนัก `examinees` และติดธง `computed`
  - `groupAverages`: เฉลี่ยรายวิชา + เฉลี่ยรวมของโรงเรียนที่กรองแล้ว — ถ่วงน้ำหนัก `examinees` เมื่อ `hasExaminees()` ไม่เช่นนั้นเฉลี่ยธรรมดา
  - `districtAverages`: เฉลี่ยรวมรายอำเภอ (กติกาถ่วงน้ำหนักเดียวกัน)
  - `qualityCounts`: นับตาม `qualityLevel` เมื่อ `hasQualityLevel()`
  - `cards`: จำนวนโรงเรียน, ผลรวมผู้เข้าสอบ (หรือซ่อน), เฉลี่ยรวมของกลุ่ม, ส่วนต่างจาก `NATIONAL` ถ้ามี มิฉะนั้นจาก `AREA`
- [ ] **Step 3:** View ตาม sketch ในสเปก — ตัวกรอง, การ์ด 4 ใบ, กราฟแท่งรายวิชา (กลุ่มที่เลือก / เขต / จังหวัด / สพฐ. / ประเทศ เท่าที่มี), กราฟแท่งรายอำเภอ, โดนัทระดับคุณภาพ (เฉพาะ RT/NT), ตารางเรียงได้พร้อมคอลัมน์ "ส่วนต่างจากเขต" (เขียว/แดง) และชื่อโรงเรียนลิงก์ไป `schools.show`. Chart.js ทำตามแบบ `dashboard-summary.blade.php` และต้องวาดใหม่เมื่อ Livewire อัปเดต (dispatch browser event พร้อมข้อมูลกราฟ). ป้าย "คำนวณจากข้อมูลโรงเรียน" เมื่อ benchmark เป็น `computed`; ป้าย "เฉลี่ยรายโรงเรียน" เมื่อไม่ถ่วงน้ำหนัก. สถานะว่าง: ข้อความ + ลิงก์ `import.index` เฉพาะ ADMIN
- [ ] **Step 4:** เพิ่ม route และเมนูแถบข้าง
- [ ] **Step 5:** ตรวจในเบราว์เซอร์ด้วย ADMIN: O-NET ป.6 การ์ดเฉลี่ยรวมของเขตแสดง 33.39 ในกราฟ benchmark, RT แสดง 80.2 และ 2965 คน, NT แสดงป้าย "คำนวณจากข้อมูลโรงเรียน"; สลับชนิด/อำเภอ/ค้นหา/เรียงลำดับแล้วการ์ด กราฟ ตารางเปลี่ยนตาม; console ไม่มี error
- [ ] **Step 6:** ผูกผู้ใช้ SUPERVISOR ทดสอบหนึ่งคนกับโรงเรียนจริง 3 แห่งด้วย tinker (`assignedSchools()->syncWithoutDetaching`) login แล้วเห็นตาราง 3 แถวเท่านั้น และค่า benchmark เขตยังแสดง

### Task 6: หน้ารายละเอียดโรงเรียน

**Files:**
- Modify: `app/Livewire/School/SchoolShow.php`
- Modify: `resources/views/livewire/school/school-show.blade.php`

**Interfaces:**
- Consumes: `School::examResults()`, `ExamType`, benchmark `AREA` (ถ้าไม่มีให้คำนวณแบบเดียวกับ Task 5 — ย้ายตรรกะนั้นเป็น static method `ExamResult::areaBenchmark(ExamType $type, string $year): ?array` ใน Task 5 แล้วใช้ร่วมกัน)

- [ ] **Step 1:** ใน `SchoolShow` เพิ่ม computed `examResults`: ผลสอบ scope SCHOOL ของโรงเรียนนี้ในปีล่าสุดที่โรงเรียนมีข้อมูล เรียงตามลำดับ case ของ `ExamType` พร้อมค่าเฉลี่ยเขตของแต่ละชนิด
- [ ] **Step 2:** View เพิ่มการ์ด "ผลการทดสอบระดับชาติ ปีการศึกษา N" — ตาราง: การสอบ | คะแนนรายวิชา (ป้าย: ค่า) | เฉลี่ย | ระดับ | ส่วนต่างจากเขต; ไม่มีข้อมูลไม่แสดงการ์ด
- [ ] **Step 3:** ตรวจในเบราว์เซอร์: เปิดโรงเรียน `อนุบาลสกลนคร` เห็น RT (เฉลี่ย 87.64, ดีมาก) และ NT (53.39); เปิดโรงเรียนทดสอบไม่มีการ์ด

### Task 7: ตรวจรับ

- [ ] **Step 1:** Run `composer lint` — Expected: PHP CS Fixer ไม่เหลือ diff, phpstan 0 errors
- [ ] **Step 2:** Run `php artisan test` — Expected: PASS ทั้งหมด
- [ ] **Step 3:** ไล่ข้อ 1–6 ในหัวข้อ "การตรวจรับ" ของสเปก บันทึกผลจริงของแต่ละข้อ และรายชื่อโรงเรียนที่ยังจับคู่ไม่ได้ (ถ้ามี) เพื่อรายงานผู้ใช้
- [ ] **Step 4:** `git status` ยืนยันว่าไม่มีไฟล์ใน `doc/` ถูก stage; สรุปไฟล์ที่เปลี่ยนให้ผู้ใช้และรอคำสั่ง commit
