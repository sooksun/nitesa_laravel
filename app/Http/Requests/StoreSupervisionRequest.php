<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request for storing a new supervision
 *
 * @package App\Http\Requests
 */
class StoreSupervisionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && ($user->isAdmin() || $user->isSupervisor());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'schoolId' => ['required', 'string', 'exists:school,id'],
            'type' => ['required', 'string', 'max:100'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'academicYear' => ['nullable', 'string', 'max:10'],
            'ministerPolicyId' => ['nullable', 'string', 'exists:policy,id'],
            'obecPolicyId' => ['nullable', 'string', 'exists:policy,id'],
            'areaPolicyId' => ['nullable', 'string', 'exists:policy,id'],
            'summary' => ['required', 'string', 'min:10', 'max:5000'],
            'suggestions' => ['required', 'string', 'min:10', 'max:5000'],
            'indicators' => ['required', 'array', 'min:1'],
            'indicators.*.name' => ['required', 'string', 'max:255'],
            'indicators.*.level' => [
                'required',
                'string',
                Rule::in(['EXCELLENT', 'GOOD', 'FAIR', 'NEEDS_WORK']),
            ],
            'indicators.*.comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'schoolId.required' => 'กรุณาเลือกโรงเรียน',
            'schoolId.exists' => 'โรงเรียนที่เลือกไม่พบในระบบ',
            'type.required' => 'กรุณาระบุประเภทการนิเทศ',
            'type.max' => 'ประเภทการนิเทศต้องไม่เกิน 100 ตัวอักษร',
            'date.required' => 'กรุณาระบุวันที่',
            'date.date' => 'รูปแบบวันที่ไม่ถูกต้อง',
            'date.before_or_equal' => 'วันที่ต้องไม่เกินวันนี้',
            'summary.required' => 'กรุณาระบุสรุปผลการนิเทศ',
            'summary.min' => 'สรุปผลการนิเทศต้องมีอย่างน้อย 10 ตัวอักษร',
            'summary.max' => 'สรุปผลการนิเทศต้องไม่เกิน 5000 ตัวอักษร',
            'suggestions.required' => 'กรุณาระบุข้อเสนอแนะ',
            'suggestions.min' => 'ข้อเสนอแนะต้องมีอย่างน้อย 10 ตัวอักษร',
            'suggestions.max' => 'ข้อเสนอแนะต้องไม่เกิน 5000 ตัวอักษร',
            'indicators.required' => 'กรุณาเพิ่มตัวชี้วัดอย่างน้อย 1 รายการ',
            'indicators.min' => 'กรุณาเพิ่มตัวชี้วัดอย่างน้อย 1 รายการ',
            'indicators.*.name.required' => 'กรุณาระบุชื่อตัวชี้วัด',
            'indicators.*.level.required' => 'กรุณาเลือกระดับตัวชี้วัด',
            'indicators.*.level.in' => 'ระดับตัวชี้วัดไม่ถูกต้อง',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'schoolId' => 'โรงเรียน',
            'type' => 'ประเภทการนิเทศ',
            'date' => 'วันที่',
            'academicYear' => 'ปีการศึกษา',
            'summary' => 'สรุปผลการนิเทศ',
            'suggestions' => 'ข้อเสนอแนะ',
            'indicators' => 'ตัวชี้วัด',
            'indicators.*.name' => 'ชื่อตัวชี้วัด',
            'indicators.*.level' => 'ระดับตัวชี้วัด',
        ];
    }
}
