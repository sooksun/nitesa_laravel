<div>
    <!-- Filters -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-5">
        <div>
            <label for="reportType" class="block text-sm font-medium text-gray-700 mb-1">ประเภทรายงาน</label>
            <select wire:model.live="reportType" 
                    id="reportType"
                    class="block w-full rounded-lg border-0 py-2.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                <option value="summary">ภาพรวม</option>
                <option value="district">รายอำเภอ</option>
                <option value="indicator">ตัวชี้วัด</option>
            </select>
        </div>
        <div>
            <label for="academicYear" class="block text-sm font-medium text-gray-700 mb-1">ปีการศึกษา</label>
            <select wire:model.live="academicYear" 
                    id="academicYear"
                    class="block w-full rounded-lg border-0 py-2.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                <option value="">ทุกปี</option>
                @foreach($this->academicYears as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="district" class="block text-sm font-medium text-gray-700 mb-1">อำเภอ</label>
            <select wire:model.live="district" 
                    id="district"
                    class="block w-full rounded-lg border-0 py-2.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                <option value="">ทุกอำเภอ</option>
                @foreach($this->districts as $d)
                    <option value="{{ $d }}">{{ $d }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="networkGroupId" class="block text-sm font-medium text-gray-700 mb-1">กลุ่มเครือข่าย</label>
            <select wire:model.live="networkGroupId" 
                    id="networkGroupId"
                    class="block w-full rounded-lg border-0 py-2.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                <option value="">ทุกกลุ่ม</option>
                @foreach($this->networkGroups as $ng)
                    <option value="{{ $ng->id }}">{{ $ng->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="button" class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                ส่งออก Excel
            </button>
        </div>
    </div>

    @if($reportType === 'summary')
        <!-- Summary Report -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <dt class="text-sm font-medium text-gray-500">โรงเรียนทั้งหมด</dt>
                <dd class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($this->summaryReport['total_schools']) }}</dd>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <dt class="text-sm font-medium text-gray-500">ได้รับการนิเทศ</dt>
                <dd class="mt-2 text-3xl font-bold text-pink-600">{{ number_format($this->summaryReport['supervised_schools']) }}</dd>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <dt class="text-sm font-medium text-gray-500">ยังไม่ได้รับการนิเทศ</dt>
                <dd class="mt-2 text-3xl font-bold text-red-600">{{ number_format($this->summaryReport['not_supervised']) }}</dd>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <dt class="text-sm font-medium text-gray-500">อัตราการนิเทศ</dt>
                <dd class="mt-2 text-3xl font-bold text-blue-600">{{ $this->summaryReport['coverage_rate'] }}%</dd>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-base font-semibold text-gray-900 mb-4">สถานะการนิเทศ</h3>
                <dl class="space-y-3">
                    @php
                        $statusLabels = [
                            'DRAFT' => ['label' => 'ร่าง', 'color' => 'gray'],
                            'SUBMITTED' => ['label' => 'ส่งแล้ว', 'color' => 'yellow'],
                            'APPROVED' => ['label' => 'อนุมัติแล้ว', 'color' => 'blue'],
                            'PUBLISHED' => ['label' => 'เผยแพร่แล้ว', 'color' => 'green'],
                            'NEEDS_IMPROVEMENT' => ['label' => 'ต้องปรับปรุง', 'color' => 'red'],
                        ];
                    @endphp
                    @foreach($statusLabels as $key => $status)
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-600">{{ $status['label'] }}</dt>
                            <dd class="text-sm font-semibold text-gray-900">{{ $this->summaryReport['status_breakdown'][$key] ?? 0 }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-base font-semibold text-gray-900 mb-4">สรุป</h3>
                <div class="prose prose-sm text-gray-600">
                    <p>
                        จากโรงเรียนทั้งหมด {{ number_format($this->summaryReport['total_schools']) }} โรงเรียน
                        ได้รับการนิเทศแล้ว {{ number_format($this->summaryReport['supervised_schools']) }} โรงเรียน
                        คิดเป็น {{ $this->summaryReport['coverage_rate'] }}%
                    </p>
                    <p>
                        มีการนิเทศรวม {{ number_format($this->summaryReport['total_supervisions']) }} ครั้ง
                        @if(isset($this->summaryReport['status_breakdown']['PUBLISHED']))
                            โดยเผยแพร่แล้ว {{ $this->summaryReport['status_breakdown']['PUBLISHED'] }} ครั้ง
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @elseif($reportType === 'district')
        <!-- District Report -->
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 sm:pl-6">อำเภอ</th>
                        <th class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">โรงเรียน</th>
                        <th class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">ได้รับการนิเทศ</th>
                        <th class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">จำนวนการนิเทศ</th>
                        <th class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">อัตรา</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($this->districtReport as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $row->district }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">{{ $row->total_schools }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">{{ $row->supervised_schools }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">{{ $row->total_supervisions }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-right">
                                @php
                                    $rate = $row->total_schools > 0 ? round(($row->supervised_schools / $row->total_schools) * 100, 1) : 0;
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                    {{ $rate >= 80 ? 'bg-green-100 text-green-700' : ($rate >= 50 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $rate }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">ไม่มีข้อมูล</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif($reportType === 'indicator')
        <!-- Indicator Report -->
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 sm:pl-6">ตัวชี้วัด</th>
                        <th class="px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">ดีเยี่ยม</th>
                        <th class="px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">ดี</th>
                        <th class="px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">พอใช้</th>
                        <th class="px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">ต้องพัฒนา</th>
                        <th class="px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">ค่าเฉลี่ย</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($this->indicatorReport as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $row->name }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-center text-green-600 font-medium">{{ $row->excellent }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-center text-blue-600 font-medium">{{ $row->good }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-center text-yellow-600 font-medium">{{ $row->fair }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-center text-red-600 font-medium">{{ $row->needs_work }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    {{ $row->avg_score >= 3.5 ? 'bg-green-100 text-green-800' : ($row->avg_score >= 2.5 ? 'bg-blue-100 text-blue-800' : ($row->avg_score >= 1.5 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                    {{ number_format($row->avg_score, 2) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">ไม่มีข้อมูล</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
