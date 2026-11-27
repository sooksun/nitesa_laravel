<?php

namespace Tests\Unit\Exam;

use App\Enums\ExamScope;
use App\Enums\ExamType;
use App\Services\Exam\ExamFileParser;
use App\Services\Exam\InvalidExamFileException;
use PHPUnit\Framework\TestCase;

class ExamFileParserTest extends TestCase
{
    private function rtRows(): array
    {
        $blank = array_fill(0, 16, null);

        return [
            ['รายงานผลการประเมินความสามารถด้านการอ่าน', null, null, null, null, null, null, null, null],
            ['โรงเรียนในสังกัด', null, null, null, null, null, null, null, null],
            ['ที่', 'รหัสโรงเรียน', 'ชื่อโรงเรียน', 'อำเภอ', 'จำนวน', 'ผลการประเมินแต่ละด้าน', null, 'เฉลี่ยรวม', 'ระดับผล'],
            [null, null, null, null, 'นักเรียน', 'อ่านออกเสียง', 'อ่านรู้เรื่อง', 'ทั้ง 2 ด้าน', 'การประเมิน'],
            [null, null, null, null, 'ที่เข้าสอบ', '(ร้อยละ)', '(ร้อยละ)', '(ร้อยละ)', 'รวม 2 ด้าน'],
            [1, 1047540001, 'อนุบาลสกลนคร', 'เมืองสกลนคร', 306, 93.28, 82.01, 87.64, 'ดีมาก'],
            [2, 1047540002, 'เชิงชุมราษฎร์นุกูล', 'เมืองสกลนคร', 164, 87.74, 75.81, 81.775, 'ดีมาก'],
            ['ระดับเขตพื้นที่(สพป.สกลนคร เขต 1)', null, null, null, 2965, 83.86, 76.54, 80.2, 'ดีมาก'],
            ['ระดับจังหวัดสกลนคร', null, null, null, null, 82.72, 77.14, 79.93, 'ดีมาก'],
            ['ระดับสังกัด(สพฐ.)', null, null, null, null, 79.86, 75.35, 77.16, 'ดีมาก'],
            ['ระดับประเทศ', null, null, null, null, 80.26, 76.86, 78.57, 'ดีมาก'],
            $blank, $blank, $blank, $blank, $blank,
        ];
    }

    private function ntRows(): array
    {
        $header = [
            [null, 'ลำดับ', 'ชื่อโรงเรียน', 'อำเภอ/เขต', "จำนวน\nผู้เข้าสอบ", 'คะแนนเฉลี่ยร้อยละ(รายด้าน)', null, null],
            [null, null, null, null, null, ' คณิตศาสตร์', ' ภาษไทย', 'เฉลี่ยร้อยละ ทั้ง 2 ด้าน'],
            [null, null, null, null, null, null, null, null],
        ];

        return [
            [null, null, null, null, null, null, null, null],
            [null, ' ค่าสถิติผลการประเมินคุณภาพผู้เรียน (NT)', null, null, null, null, null, null],
            ...$header,
            [null, 1, 'อนุบาลสกลนคร', 'เมืองสกลนคร', 314, 49.93, 56.84, 53.39],
            [null, 2, 'เชิงชุมราษฎร์นุกูล', 'เมืองสกลนคร', 188, 41.59, 54.57, 48.08],
            ...$header,
            [null, 3, 'บ้านเล็ก', 'ภูพาน', 4, 20, 30, 25],
            [null, 'หมายเหตุ : กรณีพบคะแนนเป็นเครื่องหมาย * หรือ x กรุณาติดต่อ สพฐ', null, null, null, null, null, null],
        ];
    }

    private function onetRows(): array
    {
        return [
            [null, null, null, null, null, null, null, null, null],
            ['ค่าสถิติผลการทดสอบทางการศึกษาระดับชาติขั้นพื้นฐาน (O-NET) ชั้นประถมศึกษาปีที่ 6 ปีการศึกษา 2568', null, null, null, null, null, null, null, null],
            ['(รายงานผลตามเลขรหัสโรงเรียน)', null, null, null, null, null, null, null, null],
            ['ที่', 'รหัสโรงเรียน', 'ชื่อโรงเรียน', 'ภาษาไทย', 'ภาษาอังกฤษ', 'คณิตศาสตร์', 'วิทยาศาสตร์', 'เฉลี่ยรวม', null],
            [1, 1047010001, 'บ้านพานสหราษฎร์บำรุง', 34.74, 29.6, 15.29, 23.24, 25.72, null],
            [2, 1047010002, 'บ้านประชาสุขสันติ์', 41.43, 25.94, 21.76, 37.25, 31.595, null],
            ['ระดับเขตพื้นที่ (สพป.สกลนคร เขต 1)', null, null, 46.15, 29.06, 23.64, 34.71, 33.39, null],
            ['ระดับสังกัด (สพฐ.)', null, null, 47.49, 33.44, 24.98, 35.51, 35.355, null],
            ['ระดับประเทศ', null, null, 47.6, 33.58, 24.9, 35.43, 35.3775, null],
        ];
    }

    public function test_parses_rt_rows_and_four_benchmarks(): void
    {
        $result = (new ExamFileParser())->parse(ExamType::RT, $this->rtRows());

        $this->assertCount(2, $result['schools']);
        $this->assertSame([
            'row' => 6,
            'code' => '1047540001',
            'name' => 'อนุบาลสกลนคร',
            'district' => 'เมืองสกลนคร',
            'examinees' => 306,
            'scores' => ['readAloud' => 93.28, 'readComprehension' => 82.01],
            'average' => 87.64,
            'qualityLevel' => 'ดีมาก',
        ], $result['schools'][0]);
        $this->assertSame(81.78, $result['schools'][1]['average']);

        $this->assertSame(
            [ExamScope::AREA, ExamScope::PROVINCE, ExamScope::OBEC, ExamScope::NATIONAL],
            array_column($result['benchmarks'], 'scope')
        );
        $this->assertSame(2965, $result['benchmarks'][0]['examinees']);
        $this->assertSame(80.2, $result['benchmarks'][0]['average']);
        $this->assertNull($result['benchmarks'][1]['examinees']);
    }

    public function test_parses_nt_with_repeated_header(): void
    {
        $result = (new ExamFileParser())->parse(ExamType::NT, $this->ntRows());

        $this->assertCount(3, $result['schools']);
        $this->assertSame([], $result['benchmarks']);
        $first = $result['schools'][0];
        $this->assertNull($first['code']);
        $this->assertSame('อนุบาลสกลนคร', $first['name']);
        $this->assertSame('เมืองสกลนคร', $first['district']);
        $this->assertSame(314, $first['examinees']);
        $this->assertSame(['math' => 49.93, 'thai' => 56.84], $first['scores']);
        $this->assertSame(53.39, $first['average']);
        $this->assertSame('ดี', $first['qualityLevel']);
        $this->assertSame('ปรับปรุง', $result['schools'][2]['qualityLevel']);
        $this->assertSame(11, $result['schools'][2]['row']);
    }

    public function test_parses_onet_rows_and_three_benchmarks(): void
    {
        $result = (new ExamFileParser())->parse(ExamType::ONET_P6, $this->onetRows());

        $this->assertCount(2, $result['schools']);
        $first = $result['schools'][0];
        $this->assertSame('1047010001', $first['code']);
        $this->assertNull($first['district']);
        $this->assertNull($first['examinees']);
        $this->assertSame(['thai' => 34.74, 'english' => 29.6, 'math' => 15.29, 'science' => 23.24], $first['scores']);
        $this->assertSame(25.72, $first['average']);
        $this->assertNull($first['qualityLevel']);

        $this->assertSame(
            [ExamScope::AREA, ExamScope::OBEC, ExamScope::NATIONAL],
            array_column($result['benchmarks'], 'scope')
        );
        $this->assertSame(33.39, $result['benchmarks'][0]['average']);
        $this->assertSame(47.6, $result['benchmarks'][2]['scores']['thai']);
    }

    public function test_non_numeric_scores_become_null(): void
    {
        $rows = $this->onetRows();
        $rows[4] = [1, 1047010001, 'บ้านพานสหราษฎร์บำรุง', '*', 'x', '', null, ' - ', null];

        $result = (new ExamFileParser())->parse(ExamType::ONET_P6, $rows);

        $this->assertCount(2, $result['schools']);
        $this->assertSame(
            ['thai' => null, 'english' => null, 'math' => null, 'science' => null],
            $result['schools'][0]['scores']
        );
        $this->assertNull($result['schools'][0]['average']);
    }

    public function test_numeric_strings_are_read_as_numbers(): void
    {
        $rows = $this->onetRows();
        $rows[4] = ['1', '1047010001', ' บ้านพานสหราษฎร์บำรุง ', '34.74', '29.6', '15.29', '23.24', '25.72', null];

        $first = (new ExamFileParser())->parse(ExamType::ONET_P6, $rows)['schools'][0];

        $this->assertSame('บ้านพานสหราษฎร์บำรุง', $first['name']);
        $this->assertSame(34.74, $first['scores']['thai']);
    }

    public function test_rejects_file_of_another_exam_type(): void
    {
        $this->expectException(InvalidExamFileException::class);
        (new ExamFileParser())->parse(ExamType::NT, $this->onetRows());
    }

    public function test_rejects_onet_file_when_rt_was_selected(): void
    {
        $this->expectException(InvalidExamFileException::class);
        (new ExamFileParser())->parse(ExamType::RT, $this->onetRows());
    }

    public function test_rejects_rt_file_when_onet_was_selected(): void
    {
        $this->expectException(InvalidExamFileException::class);
        (new ExamFileParser())->parse(ExamType::ONET_M3, $this->rtRows());
    }

    public function test_rejects_grade_6_onet_file_when_grade_9_was_selected(): void
    {
        $this->expectException(InvalidExamFileException::class);
        $this->expectExceptionMessage('ประถมศึกษาปีที่ 6');
        (new ExamFileParser())->parse(ExamType::ONET_M3, $this->onetRows());
    }

    public function test_rejects_onet_file_that_does_not_state_its_grade(): void
    {
        $rows = $this->onetRows();
        $rows[1][0] = 'ค่าสถิติผลการทดสอบ (O-NET)';

        $this->expectException(InvalidExamFileException::class);
        (new ExamFileParser())->parse(ExamType::ONET_P6, $rows);
    }

    public function test_rejects_rt_file_stating_another_grade(): void
    {
        $rows = $this->rtRows();
        $rows[0][0] = 'รายงานผล ชั้นประถมศึกษาปีที่ 3 ปีการศึกษา 2568';

        $this->expectException(InvalidExamFileException::class);
        (new ExamFileParser())->parse(ExamType::RT, $rows);
    }

    public function test_rejects_file_of_another_academic_year(): void
    {
        $this->expectException(InvalidExamFileException::class);
        $this->expectExceptionMessage('2568');
        (new ExamFileParser())->parse(ExamType::ONET_P6, $this->onetRows(), '2567');
    }

    public function test_accepts_file_of_the_selected_academic_year(): void
    {
        $result = (new ExamFileParser())->parse(ExamType::ONET_P6, $this->onetRows(), '2568');

        $this->assertCount(2, $result['schools']);
    }

    public function test_reports_score_rows_that_have_no_sequence_number(): void
    {
        $rows = $this->onetRows();
        array_splice($rows, 6, 0, [[null, 1047010009, 'บ้านไม่มีลำดับ', 40, 30, 20, 30, 30, null]]);

        $result = (new ExamFileParser())->parse(ExamType::ONET_P6, $rows);

        $this->assertCount(2, $result['schools']);
        $this->assertSame([['row' => 7, 'name' => 'บ้านไม่มีลำดับ']], $result['skipped']);
        $this->assertCount(3, $result['benchmarks']);
    }

    public function test_headers_notes_and_benchmarks_are_not_reported_as_skipped(): void
    {
        $parser = new ExamFileParser();

        $this->assertSame([], $parser->parse(ExamType::NT, $this->ntRows())['skipped']);
        $this->assertSame([], $parser->parse(ExamType::RT, $this->rtRows())['skipped']);
        $this->assertSame([], $parser->parse(ExamType::ONET_P6, $this->onetRows())['skipped']);
    }

    public function test_rejects_rows_without_header(): void
    {
        $this->expectException(InvalidExamFileException::class);
        (new ExamFileParser())->parse(ExamType::RT, [['a', 'b'], [1, 2]]);
    }

    public function test_rejects_empty_file(): void
    {
        $this->expectException(InvalidExamFileException::class);
        (new ExamFileParser())->parse(ExamType::RT, []);
    }
}
