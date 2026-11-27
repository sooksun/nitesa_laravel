@php
    $subjects = $type->subjects();
    $group = $this->group;
    $benchmarks = $this->benchmarks;
    $reference = $this->reference;
    $delta = ($group['average'] !== null && $reference !== null) ? round($group['average'] - $reference['average'], 2) : null;
    $weighted = $type->hasExaminees();
    $areaComputed = $benchmarks['AREA']['computed'] ?? false;
    $score = fn ($value) => $value === null ? '–' : number_format((float) $value, 2);
    $levelClass = fn ($level) => match ($level) {
        'ดีมาก' => 'bg-green-100 text-green-800',
        'ดี' => 'bg-blue-100 text-blue-800',
        'พอใช้' => 'bg-yellow-100 text-yellow-800',
        'ปรับปรุง' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<div>
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">ผลการทดสอบระดับชาติ</h1>
            <p class="text-gray-500 mt-1">RT, NT และ O-NET รายโรงเรียน เทียบกับค่าเฉลี่ยระดับเขตพื้นที่ สังกัด และประเทศ</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="academicYear" aria-label="ปีการศึกษา"
                    class="rounded-xl border-2 border-pink-200 py-2.5 px-4 text-gray-700 font-medium focus:border-pink-500 focus:ring-pink-500 bg-white shadow-sm">
                @forelse($this->years as $year)
                    <option value="{{ $year }}">ปีการศึกษา {{ $year }}</option>
                @empty
                    <option value="{{ $academicYear }}">ปีการศึกษา {{ $academicYear }}</option>
                @endforelse
            </select>
            <select wire:model.live="district" aria-label="อำเภอ"
                    class="rounded-xl border-2 border-gray-200 py-2.5 px-4 text-gray-700 focus:border-pink-500 focus:ring-pink-500 bg-white shadow-sm">
                <option value="">ทุกอำเภอ</option>
                @foreach($this->districts as $name)
                    <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
            </select>
            <input wire:model.live.debounce.400ms="search" type="search" placeholder="ค้นหาโรงเรียน" aria-label="ค้นหาโรงเรียน"
                   class="rounded-xl border-2 border-gray-200 py-2.5 px-4 text-gray-700 focus:border-pink-500 focus:ring-pink-500 bg-white shadow-sm w-48">
        </div>
    </div>

    <div class="mb-6 inline-flex flex-wrap rounded-xl bg-white p-1 shadow-sm ring-1 ring-gray-900/5" role="tablist" aria-label="ประเภทการทดสอบ">
        @foreach($types as $option)
            <button type="button" role="tab" aria-selected="{{ $examType === $option->value ? 'true' : 'false' }}"
                    wire:click="$set('examType', '{{ $option->value }}')"
                    class="rounded-lg px-4 py-2 text-sm font-semibold transition-colors {{ $examType === $option->value ? 'bg-pink-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                {{ $option->label() }}
            </button>
        @endforeach
    </div>

    @if($group['schools'] === 0)
        <div class="rounded-2xl bg-white p-12 text-center shadow-sm ring-1 ring-gray-900/5">
            <p class="text-base font-semibold text-gray-900">
                {{ $this->isFiltered() ? 'ไม่พบโรงเรียนตามเงื่อนไขที่เลือก' : 'ยังไม่มีผลการทดสอบ ' . $type->label() . ' ปีการศึกษา ' . $academicYear }}
            </p>
            @if(! $this->isFiltered() && auth()->user()->isAdmin())
                <a href="{{ route('import.index') }}" class="mt-4 inline-flex items-center rounded-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-pink-500">
                    ไปหน้านำเข้าข้อมูล
                </a>
            @elseif(! $this->isFiltered())
                <p class="mt-2 text-sm text-gray-500">ข้อมูลจะแสดงเมื่อผู้ดูแลระบบนำเข้าผลการทดสอบของโรงเรียนในความรับผิดชอบของท่าน</p>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
                <p class="text-sm font-medium text-gray-500">โรงเรียนที่มีผลสอบ</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($group['schools']) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $this->groupLabel() }}</p>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
                @if($weighted)
                    <p class="text-sm font-medium text-gray-500">ผู้เข้าสอบ</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($group['examinees']) }}</p>
                    <p class="text-xs text-gray-400 mt-1">คน</p>
                @else
                    <p class="text-sm font-medium text-gray-500">ค่าเฉลี่ยรวมระดับเขตพื้นที่</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $score($benchmarks['AREA']['average'] ?? null) }}</p>
                    <p class="text-xs text-gray-400 mt-1">ตามรายงานผลการทดสอบ</p>
                @endif
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
                <p class="text-sm font-medium text-gray-500">คะแนนเฉลี่ยรวม</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $score($group['average']) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $weighted ? 'ถ่วงน้ำหนักด้วยจำนวนผู้เข้าสอบ' : 'เฉลี่ยรายโรงเรียน' }}</p>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
                <p class="text-sm font-medium text-gray-500">เทียบกับระดับ{{ $reference['label'] ?? 'เขตพื้นที่' }}</p>
                @if($delta === null)
                    <p class="text-3xl font-bold text-gray-900 mt-2">–</p>
                @else
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $delta > 0 ? '+' : '' }}{{ number_format($delta, 2) }}</p>
                    <p class="text-xs mt-1 font-medium {{ $delta >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        {{ $delta > 0 ? '▲ สูงกว่า' : ($delta < 0 ? '▼ ต่ำกว่า' : 'เท่ากับ') }} {{ $score($reference['average']) }}
                    </p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 lg:col-span-2">
                <h3 class="text-base font-semibold text-gray-900">คะแนนเฉลี่ยร้อยละรายด้าน</h3>
                <p class="text-sm text-gray-500 mb-4">
                    {{ $this->groupLabel() }} เทียบกับค่าเฉลี่ยระดับต่าง ๆ
                    @if($areaComputed)
                        <span class="ml-1 inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs text-gray-600">ระดับเขตพื้นที่: คำนวณจากข้อมูลโรงเรียน</span>
                    @endif
                </p>
                <div class="relative h-72" wire:ignore>
                    <canvas id="examSubjectChart" role="img" aria-label="กราฟแท่งคะแนนเฉลี่ยรายด้าน"></canvas>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                @if($type->hasQualityLevel())
                    <h3 class="text-base font-semibold text-gray-900">ระดับคุณภาพ</h3>
                    <p class="text-sm text-gray-500 mb-4">จำนวนโรงเรียนในแต่ละระดับ</p>
                    <div class="relative h-72" wire:ignore>
                        <canvas id="examQualityChart" role="img" aria-label="กราฟแท่งจำนวนโรงเรียนตามระดับคุณภาพ"></canvas>
                    </div>
                @else
                    <h3 class="text-base font-semibold text-gray-900">ค่าเฉลี่ยตามระดับ</h3>
                    <p class="text-sm text-gray-500 mb-4">คะแนนเฉลี่ยรวมตามรายงานผลการทดสอบ</p>
                    <dl class="divide-y divide-gray-100">
                        @foreach($benchmarks as $benchmark)
                            <div class="flex items-center justify-between py-3">
                                <dt class="text-sm text-gray-600">ระดับ{{ $benchmark['label'] }}</dt>
                                <dd class="text-sm font-semibold text-gray-900 tabular-nums">{{ $score($benchmark['average']) }}</dd>
                            </div>
                        @endforeach
                        <div class="flex items-center justify-between py-3">
                            <dt class="text-sm text-gray-600">{{ $this->groupLabel() }} <span class="text-xs text-gray-400">(เฉลี่ยรายโรงเรียน)</span></dt>
                            <dd class="text-sm font-semibold text-gray-900 tabular-nums">{{ $score($group['average']) }}</dd>
                        </div>
                    </dl>
                @endif
            </div>
        </div>

        @if($this->districtAverages->count() > 1)
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 mb-6">
                <h3 class="text-base font-semibold text-gray-900">คะแนนเฉลี่ยรวมรายอำเภอ</h3>
                <p class="text-sm text-gray-500 mb-4">
                    {{ $weighted ? 'ถ่วงน้ำหนักด้วยจำนวนผู้เข้าสอบ' : 'เฉลี่ยรายโรงเรียน' }}
                    @if($areaAverage !== null) · เส้นประคือค่าเฉลี่ยระดับเขตพื้นที่ {{ $score($areaAverage) }} @endif
                </p>
                <div class="relative h-64" wire:ignore>
                    <canvas id="examDistrictChart" role="img" aria-label="กราฟแท่งคะแนนเฉลี่ยรวมรายอำเภอ"></canvas>
                </div>
            </div>
        @endif

        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold text-gray-500 sm:pl-6 w-12">ที่</th>
                            @php
                                $columns = ['name' => ['โรงเรียน', 'left'], 'district' => ['อำเภอ', 'left']];
                                if ($weighted) { $columns['examinees'] = ['ผู้เข้าสอบ', 'right']; }
                                foreach ($subjects as $key => $label) { $columns[$key] = [$label, 'right']; }
                                $columns['average'] = ['เฉลี่ยรวม', 'right'];
                            @endphp
                            @foreach($columns as $key => [$label, $align])
                                <th class="px-3 py-3.5 text-{{ $align }} text-xs font-semibold text-gray-500"
                                    aria-sort="{{ $sortBy === $key ? ($sortDir === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                    <button type="button" wire:click="sort('{{ $key }}')" class="inline-flex items-center gap-1 hover:text-gray-900">
                                        {{ $label }}
                                        <span class="{{ $sortBy === $key ? 'text-pink-600' : 'text-gray-300' }}">{{ $sortBy === $key && $sortDir === 'asc' ? '▲' : '▼' }}</span>
                                    </button>
                                </th>
                            @endforeach
                            @if($type->hasQualityLevel())
                                <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500">ระดับ</th>
                            @endif
                            <th class="px-3 py-3.5 pr-4 text-right text-xs font-semibold text-gray-500 sm:pr-6">เทียบเขต</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($rows as $row)
                            @php $diff = ($row->average !== null && $areaAverage !== null) ? round($row->average - $areaAverage, 2) : null; @endphp
                            <tr class="hover:bg-gray-50" wire:key="exam-row-{{ $row->id }}">
                                <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm text-gray-500 sm:pl-6 tabular-nums">{{ $rows->firstItem() + $loop->index }}</td>
                                <td class="px-3 py-3 text-sm font-medium">
                                    <a href="{{ route('schools.show', $row->schoolId) }}" class="text-pink-700 hover:text-pink-500">{{ $row->schoolName }}</a>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-600">{{ $row->schoolDistrict ?: '–' }}</td>
                                @if($weighted)
                                    <td class="whitespace-nowrap px-3 py-3 text-right text-sm text-gray-600 tabular-nums">{{ $row->examinees === null ? '–' : number_format($row->examinees) }}</td>
                                @endif
                                @foreach($subjects as $key => $label)
                                    <td class="whitespace-nowrap px-3 py-3 text-right text-sm text-gray-700 tabular-nums">{{ $score($row->{$key}) }}</td>
                                @endforeach
                                <td class="whitespace-nowrap px-3 py-3 text-right text-sm font-semibold text-gray-900 tabular-nums">{{ $score($row->average) }}</td>
                                @if($type->hasQualityLevel())
                                    <td class="whitespace-nowrap px-3 py-3 text-sm">
                                        @if($row->qualityLevel)
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $levelClass($row->qualityLevel) }}">{{ $row->qualityLevel }}</span>
                                        @else
                                            –
                                        @endif
                                    </td>
                                @endif
                                <td class="whitespace-nowrap px-3 py-3 pr-4 text-right text-sm font-medium tabular-nums sm:pr-6 {{ $diff === null ? 'text-gray-400' : ($diff >= 0 ? 'text-green-700' : 'text-red-700') }}">
                                    {{ $diff === null ? '–' : ($diff > 0 ? '▲ +' : ($diff < 0 ? '▼ ' : '')) . number_format($diff, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($rows->hasPages())
                <div class="border-t border-gray-100 px-4 py-3 sm:px-6">
                    {{ $rows->links() }}
                </div>
            @endif
        </div>
    @endif
</div>

@assets
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endassets

@script
<script>
    const seriesColors = { GROUP: '#2a78d6', AREA: '#eb6834', PROVINCE: '#1baf7a', OBEC: '#eda100', NATIONAL: '#e87ba4' };
    const instances = {};
    const grid = { color: 'rgba(0,0,0,0.06)' };

    const draw = (id, config) => {
        if (instances[id]) {
            instances[id].destroy();
            delete instances[id];
        }
        const canvas = document.getElementById(id);
        if (canvas && typeof Chart !== 'undefined') {
            instances[id] = new Chart(canvas, config);
        }
    };

    const render = (charts) => {
        if (typeof Chart === 'undefined') {
            return;
        }
        Chart.defaults.font.family = "'Sarabun', 'Prompt', sans-serif";
        Chart.defaults.color = '#6b7280';

        draw('examSubjectChart', {
            type: 'bar',
            data: {
                labels: charts.subjects.labels,
                datasets: charts.subjects.series.map((series) => ({
                    label: series.label,
                    data: series.data,
                    backgroundColor: seriesColors[series.key],
                    borderColor: '#ffffff',
                    borderWidth: 1,
                    borderRadius: 4,
                    maxBarThickness: 28,
                })),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } } },
                scales: { y: { beginAtZero: true, grid, title: { display: true, text: 'ร้อยละ' } }, x: { grid: { display: false } } },
            },
        });

        draw('examDistrictChart', {
            type: 'bar',
            data: {
                labels: charts.districts.labels,
                datasets: [
                    { type: 'bar', label: 'เฉลี่ยรวม', data: charts.districts.data, backgroundColor: seriesColors.GROUP, borderRadius: 4, maxBarThickness: 44, order: 2 },
                    ...(charts.districts.reference === null ? [] : [{
                        type: 'line',
                        label: 'ระดับเขตพื้นที่',
                        data: charts.districts.labels.map(() => charts.districts.reference),
                        borderColor: seriesColors.AREA,
                        borderWidth: 2,
                        borderDash: [6, 4],
                        pointRadius: 0,
                        order: 1,
                    }]),
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } } },
                scales: { y: { beginAtZero: true, grid, title: { display: true, text: 'ร้อยละ' } }, x: { grid: { display: false } } },
            },
        });

        draw('examQualityChart', {
            type: 'bar',
            data: {
                labels: charts.quality.labels,
                datasets: [{ label: 'จำนวนโรงเรียน', data: charts.quality.data, backgroundColor: seriesColors.GROUP, borderRadius: 4, maxBarThickness: 36 }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, grid, ticks: { precision: 0 }, title: { display: true, text: 'โรงเรียน' } }, y: { grid: { display: false } } },
            },
        });
    };

    $wire.on('exam-charts', ({ charts }) => requestAnimationFrame(() => render(charts)));
    render(@js($this->chartData));
</script>
@endscript
