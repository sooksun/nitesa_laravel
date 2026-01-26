<?php

namespace App\Http\Requests;

use App\Enums\SupervisionStatus;
use App\Models\Supervision;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request for updating a supervision
 *
 * @package App\Http\Requests
 */
class UpdateSupervisionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $supervision = $this->route('supervision');

        if ($user === null) {
            return false;
        }

        // Only allow updates on drafts or needs_improvement
        if (! $supervision instanceof Supervision) {
            return false;
        }

        $allowedStatuses = [SupervisionStatus::DRAFT, SupervisionStatus::NEEDS_IMPROVEMENT];

        if (! in_array($supervision->status, $allowedStatuses)) {
            return false;
        }

        // Admin can edit any, supervisor can only edit their own
        return $user->isAdmin() || ($user->isSupervisor() && $supervision->userId === $user->id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'max:100'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'academicYear' => ['nullable', 'string', 'max:10'],
            'ministerPolicyId' => ['nullable', 'string', 'exists:policy,id'],
            'obecPolicyId' => ['nullable', 'string', 'exists:policy,id'],
            'areaPolicyId' => ['nullable', 'string', 'exists:policy,id'],
            'summary' => ['required', 'string', 'min:10', 'max:5000'],
            'suggestions' => ['required', 'string', 'min:10', 'max:5000'],
            'indicators' => ['required', 'array', 'min:1'],
            'indicators.*.id' => ['nullable', 'string', 'exists:indicator,id'],
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
            'type.required' => 'กรุณาระบุประเภทการนิเทศ',
            'date.required' => 'กรุณาระบุวันที่',
            'date.before_or_equal' => 'วันที่ต้องไม่เกินวันนี้',
            'summary.required' => 'กรุณาระบุสรุปผลการนิเทศ',
            'summary.min' => 'สรุปผลการนิเทศต้องมีอย่างน้อย 10 ตัวอักษร',
            'suggestions.required' => 'กรุณาระบุข้อเสนอแนะ',
            'suggestions.min' => 'ข้อเสนอแนะต้องมีอย่างน้อย 10 ตัวอักษร',
            'indicators.required' => 'กรุณาเพิ่มตัวชี้วัดอย่างน้อย 1 รายการ',
            'indicators.*.name.required' => 'กรุณาระบุชื่อตัวชี้วัด',
            'indicators.*.level.required' => 'กรุณาเลือกระดับตัวชี้วัด',
        ];
    }
}
