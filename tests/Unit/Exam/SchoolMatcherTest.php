<?php

namespace Tests\Unit\Exam;

use App\Services\Exam\SchoolMatcher;
use PHPUnit\Framework\TestCase;

class SchoolMatcherTest extends TestCase
{
    private function matcher(array $aliases = []): SchoolMatcher
    {
        return new SchoolMatcher([
            ['id' => 'A', 'code' => '1047540001', 'name' => 'อนุบาลสกลนคร', 'district' => 'เมืองสกลนคร'],
            ['id' => 'B', 'code' => 'X1', 'name' => 'โรงเรียนบ้านนา (คุรุราษฎร)', 'district' => null],
            ['id' => 'C', 'code' => 'X2', 'name' => 'บ้านสะพานสาม', 'district' => 'ภูพาน'],
            ['id' => 'D1', 'code' => 'X3', 'name' => 'บ้านซ้ำ', 'district' => 'กุดบาก'],
            ['id' => 'D2', 'code' => 'X4', 'name' => 'โรงเรียนบ้านซ้ำ', 'district' => 'กุดบาก'],
            ['id' => 'E1', 'code' => 'X5', 'name' => 'บ้านโนน', 'district' => 'กุดบาก'],
            ['id' => 'E2', 'code' => 'X6', 'name' => 'บ้านโนน', 'district' => 'ภูพาน'],
        ], $aliases);
    }

    public function test_district_written_short_or_long_still_matches(): void
    {
        $matcher = $this->matcher();
        $matcher->add(['id' => 'F', 'code' => 'X7', 'name' => 'บ้านหนองบัว', 'district' => 'เมือง']);

        $this->assertSame('F', $matcher->match(null, 'บ้านหนองบัว', 'เมืองสกลนคร')['schoolId']);
    }

    public function test_reports_the_system_name_of_a_school(): void
    {
        $this->assertSame('โรงเรียนบ้านนา (คุรุราษฎร)', $this->matcher()->nameOf('B'));
        $this->assertNull($this->matcher()->nameOf('missing'));
    }

    public function test_creation_is_blocked_when_the_name_exists_in_another_district(): void
    {
        $this->assertStringContainsString('อำเภอ', (string) $this->matcher()->creationBlocker('โรงเรียนบ้านสะพานสาม'));
    }

    public function test_creation_is_blocked_when_a_similar_name_exists(): void
    {
        $this->assertStringContainsString('บ้านสะพานสาม', (string) $this->matcher()->creationBlocker('บ้านสะพานสาน'));
        $this->assertStringContainsString('อนุบาลสกลนคร', (string) $this->matcher()->creationBlocker('อนุบาลสกลนค'));
    }

    public function test_creation_is_allowed_for_a_clearly_new_name(): void
    {
        $this->assertNull($this->matcher()->creationBlocker('ชุมชนดงมะไฟเจริญศิลป์'));
    }

    public function test_schools_created_in_the_same_run_do_not_block_creation(): void
    {
        $matcher = $this->matcher();
        $matcher->add(['id' => 'N', 'code' => '555', 'name' => 'บ้านโคกก่องวิทยา', 'district' => 'ภูพาน'], true);

        $this->assertNull($matcher->creationBlocker('บ้านโคกกลางวิทยา'));
        $this->assertNull($matcher->creationBlocker('บ้านโคกก่องวิทยา'));
    }

    public function test_matches_by_code_first(): void
    {
        $this->assertSame(
            ['status' => 'matched', 'schoolId' => 'A', 'by' => 'code'],
            $this->matcher()->match('1047540001', 'ชื่ออื่น', null)
        );
    }

    public function test_code_and_name_pointing_at_different_schools_is_ambiguous(): void
    {
        $this->assertSame('ambiguous', $this->matcher()->match('1047540001', 'บ้านสะพานสาม', null)['status']);
    }

    public function test_district_prefix_is_ignored(): void
    {
        $this->assertSame('C', $this->matcher()->match(null, 'บ้านสะพานสาม', 'อำเภอภูพาน')['schoolId']);
    }

    public function test_matches_by_name_ignoring_prefix_and_punctuation(): void
    {
        $this->assertSame(
            ['status' => 'matched', 'schoolId' => 'B', 'by' => 'name'],
            $this->matcher()->match('999', 'บ้านนา คุรุราษฎร', null)
        );
    }

    public function test_district_must_agree_when_both_present(): void
    {
        $this->assertSame('none', $this->matcher()->match(null, 'บ้านสะพานสาม', 'กุดบาก')['status']);
    }

    public function test_district_picks_between_same_named_schools(): void
    {
        $this->assertSame('E2', $this->matcher()->match(null, 'บ้านโนน', 'ภูพาน')['schoolId']);
    }

    public function test_same_named_schools_without_district_are_ambiguous(): void
    {
        $this->assertSame(
            ['status' => 'ambiguous', 'schoolId' => null, 'by' => null],
            $this->matcher()->match(null, 'บ้านโนน', null)
        );
    }

    public function test_matches_by_alias(): void
    {
        $matcher = $this->matcher(['บัานสะพานสาม' => 'บ้านสะพานสาม']);

        $this->assertSame(
            ['status' => 'matched', 'schoolId' => 'C', 'by' => 'alias'],
            $matcher->match(null, 'บัานสะพานสาม', 'ภูพาน')
        );
    }

    public function test_duplicate_names_are_ambiguous(): void
    {
        $this->assertSame('ambiguous', $this->matcher()->match(null, 'บ้านซ้ำ', 'กุดบาก')['status']);
    }

    public function test_unknown_is_none(): void
    {
        $this->assertSame(
            ['status' => 'none', 'schoolId' => null, 'by' => null],
            $this->matcher()->match('000', 'ไม่มีจริง', null)
        );
    }

    public function test_added_school_is_matchable(): void
    {
        $matcher = $this->matcher();
        $matcher->add(['id' => 'N', 'code' => '555', 'name' => 'บ้านใหม่', 'district' => 'ภูพาน']);

        $this->assertSame('N', $matcher->match(null, 'โรงเรียนบ้านใหม่', 'ภูพาน')['schoolId']);
        $this->assertSame('N', $matcher->match('555', 'อะไรก็ได้', null)['schoolId']);
    }
}
