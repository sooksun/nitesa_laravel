<?php

namespace Tests\Unit\Import;

use App\Services\Import\SchoolRowMapper;
use PHPUnit\Framework\TestCase;

class SchoolRowMapperTest extends TestCase
{
    private function mapper(): SchoolRowMapper
    {
        return new SchoolRowMapper([
            ['id' => 'G16', 'code' => 'NG016', 'name' => 'ศูนย์อำนวยการเครือข่ายกุดบาก'],
            ['id' => 'G07', 'code' => 'NG007', 'name' => 'ศูนย์อำนวยการเครือข่ายกุสุมาลย์ 1'],
        ]);
    }

    private function row(array $overrides = []): array
    {
        return $overrides + [
            'ลำดับ' => 1,
            'code' => 47020001,
            'name' => ' กุดบากราษฎร์บำรุง ',
            'province' => 'จังหวัดสกลนคร',
            'district' => 'กุดบาก',
            'subDistrict' => 'กุดบาก',
            'address' => 'ศูนย์อำนวยการเครือข่ายกุดบาก',
            'phone' => '088-560-9270',
            'email' => 'ไม่มีข้อมูล',
            'principalName' => 'นายครรชิต วงค์เเต้ม',
            'studentCount' => 439,
            'teacherCount' => 'ไม่มีข้อมูล',
            'networkGroupCode' => 'ศูนย์อำนวยการเครือข่ายกุดบาก',
        ];
    }

    public function test_maps_a_row_of_the_real_school_list(): void
    {
        $this->assertSame([
            'code' => '47020001',
            'attributes' => [
                'name' => 'กุดบากราษฎร์บำรุง',
                'province' => 'สกลนคร',
                'district' => 'กุดบาก',
                'subDistrict' => 'กุดบาก',
                'address' => null,
                'phone' => '088-560-9270',
                'email' => null,
                'principalName' => 'นายครรชิต วงค์เเต้ม',
                'studentCount' => 439,
                'teacherCount' => 0,
                'networkGroupId' => 'G16',
            ],
        ], $this->mapper()->map($this->row()));
    }

    public function test_network_group_is_resolved_by_code_too(): void
    {
        $mapped = $this->mapper()->map($this->row(['networkGroupCode' => ' ng007 ']));

        $this->assertSame('G07', $mapped['attributes']['networkGroupId']);
    }

    public function test_unknown_network_group_is_an_error(): void
    {
        $this->expectExceptionMessage('ไม่พบกลุ่มเครือข่าย');
        $this->mapper()->map($this->row(['networkGroupCode' => 'ศูนย์ที่ไม่มีอยู่จริง']));
    }

    public function test_blank_network_group_clears_the_link(): void
    {
        $mapped = $this->mapper()->map($this->row(['networkGroupCode' => 'ไม่มีข้อมูล']));

        $this->assertNull($mapped['attributes']['networkGroupId']);
    }

    public function test_file_without_network_group_column_leaves_the_link_untouched(): void
    {
        $row = $this->row();
        unset($row['networkGroupCode']);

        $this->assertArrayNotHasKey('networkGroupId', $this->mapper()->map($row)['attributes']);
    }

    public function test_real_address_is_kept(): void
    {
        $mapped = $this->mapper()->map($this->row(['address' => '12 หมู่ 3']));

        $this->assertSame('12 หมู่ 3', $mapped['attributes']['address']);
    }

    public function test_phone_number_read_as_a_number_gets_its_leading_zero_back(): void
    {
        $this->assertSame('0856596665', $this->mapper()->map($this->row(['phone' => 856596665]))['attributes']['phone']);
        $this->assertSame('042711234', $this->mapper()->map($this->row(['phone' => 42711234.0]))['attributes']['phone']);
        $this->assertSame('0856596665', $this->mapper()->map($this->row(['phone' => '0856596665']))['attributes']['phone']);
    }

    public function test_snake_case_headers_of_the_old_template_still_work(): void
    {
        $mapped = $this->mapper()->map([
            'code' => 'S1',
            'name' => 'โรงเรียนเดิม',
            'sub_district' => 'ตำบลเดิม',
            'principal_name' => 'ผอ.เดิม',
            'student_count' => '120',
            'teacher_count' => 9,
        ]);

        $this->assertSame('ตำบลเดิม', $mapped['attributes']['subDistrict']);
        $this->assertSame('ผอ.เดิม', $mapped['attributes']['principalName']);
        $this->assertSame(120, $mapped['attributes']['studentCount']);
        $this->assertSame(9, $mapped['attributes']['teacherCount']);
        $this->assertSame('', $mapped['attributes']['district']);
    }

    public function test_row_without_code_or_name_is_an_error(): void
    {
        $this->expectExceptionMessage('ต้องมี code และ name');
        $this->mapper()->map($this->row(['name' => '  ']));
    }
}
