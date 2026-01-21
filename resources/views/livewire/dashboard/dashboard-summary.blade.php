<div>
    <!-- Header with Year Filter -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">แดชบอร์ดผู้ดูแลระบบ</h1>
            <p class="text-gray-500 mt-1">ภาพรวมระบบนิเทศการศึกษาทั้งเขต</p>
        </div>
        <div>
            <select wire:model.live="academicYear" 
                    class="rounded-xl border-2 border-pink-200 py-2.5 px-4 text-gray-700 font-medium focus:border-pink-500 focus:ring-pink-500 bg-white shadow-sm">
                @foreach($academicYears as $year)
                    <option value="{{ $year }}">ปีการศึกษา {{ $year }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Schools Card -->
        <div class="stat-card rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">โรงเรียนทั้งหมด</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($this->stats['totalSchools']) }}</p>
                    <p class="text-xs text-gray-400 mt-1">โรงเรียนในเขตพื้นที่</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-green-100">
                    <svg class="h-7 w-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Supervisions Card -->
        <div class="stat-card rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">การนิเทศทั้งหมด</p>
                    <p class="text-3xl font-bold text-orange-500 mt-2">{{ number_format($this->stats['totalSupervisions']) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $this->stats['approvedCount'] }} รายการที่อนุมัติแล้ว</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-100">
                    <svg class="h-7 w-7 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Users Card -->
        <div class="stat-card rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">ผู้ใช้งาน</p>
                    <p class="text-3xl font-bold text-fuchsia-600 mt-2">{{ number_format($this->stats['userCount']) }}</p>
                    <p class="text-xs text-gray-400 mt-1">ผู้ใช้งานในระบบ</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-fuchsia-100">
                    <svg class="h-7 w-7 text-fuchsia-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Approval Rate Card -->
        <div class="stat-card rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">อัตราการอนุมัติ</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $this->stats['approvalRate'] }}%</p>
                    <p class="text-xs text-gray-400 mt-1">การนิเทศที่อนุมัติแล้ว</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100">
                    <svg class="h-7 w-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1: Radar + Donut -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Indicator Radar Chart -->
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 mb-1">การกระจายตัวชี้วัด</h3>
            <p class="text-sm text-gray-500 mb-4">แสดงผลตัวชี้วัดแต่ละประเภทในรูปแบบ Spider Chart</p>
            <div class="chart-container">
                <canvas id="radarChart"></canvas>
            </div>
            <div class="flex flex-wrap gap-3 mt-4 justify-center text-xs">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-500"></span> ดี</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-green-500"></span> ดีเยี่ยม</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-500"></span> ต้องพัฒนา</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-yellow-500"></span> พอใช้</span>
            </div>
        </div>

        <!-- Indicator Donut Chart -->
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 mb-1">สัดส่วนตัวชี้วัด</h3>
            <p class="text-sm text-gray-500 mb-4">แสดงสัดส่วนตัวชี้วัดทั้งหมด</p>
            <div class="chart-container">
                <canvas id="indicatorDonutChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 2: Status Pie + Policy Pie + Year Line -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Status Pie Chart -->
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 mb-1">สถานะการนิเทศ</h3>
            <p class="text-sm text-gray-500 mb-4">แสดงสัดส่วนของสถานะต่างๆ</p>
            <div class="chart-container">
                <canvas id="statusPieChart"></canvas>
            </div>
        </div>

        <!-- Policy Usage Pie Chart -->
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 mb-1">การใช้นโยบาย</h3>
            <p class="text-sm text-gray-500 mb-4">แสดงสัดส่วนการใช้นโยบาย</p>
            <div class="chart-container">
                <canvas id="policyPieChart"></canvas>
            </div>
        </div>

        <!-- Yearly Trend Line Chart -->
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 mb-1">แนวโน้มตามปีการศึกษา</h3>
            <p class="text-sm text-gray-500 mb-4">แสดงแนวโน้มการนิเทศ</p>
            <div class="chart-container">
                <canvas id="yearlyTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 3: Network Group + District + Supervisor Performance -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Network Group Bar Chart -->
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 mb-1">การนิเทศแยกตามกลุ่มเครือข่าย</h3>
            <p class="text-sm text-gray-500 mb-4">เปรียบเทียบการนิเทศระหว่างกลุ่มเครือข่าย</p>
            <div class="chart-container">
                <canvas id="networkGroupChart"></canvas>
            </div>
        </div>

        <!-- District Bar Chart -->
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 mb-1">การนิเทศแยกตามอำเภอ</h3>
            <p class="text-sm text-gray-500 mb-4">เปรียบเทียบการนิเทศระหว่างอำเภอ</p>
            <div class="chart-container">
                <canvas id="districtChart"></canvas>
            </div>
        </div>

        <!-- Supervisor Performance Bar Chart -->
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 mb-1">ประสิทธิภาพผู้นิเทศ</h3>
            <p class="text-sm text-gray-500 mb-4">เปรียบเทียบประสิทธิภาพผู้นิเทศ</p>
            <div class="chart-container">
                <canvas id="supervisorChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Supervisions -->
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-900 mb-1">การนิเทศล่าสุด</h3>
        <p class="text-sm text-gray-500 mb-4">รายการการนิเทศที่เพิ่งสร้างล่าสุด</p>
        <div class="divide-y divide-gray-100">
            @forelse($this->recentSupervisions as $supervision)
                <div class="py-4 flex items-center justify-between hover:bg-gray-50 -mx-2 px-2 rounded-lg transition-colors">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $supervision->school?->name ?? 'ไม่ระบุโรงเรียน' }}</p>
                        <p class="text-sm text-gray-500">{{ $supervision->type ?? '-' }} • {{ $supervision->user?->name ?? 'ไม่ระบุ' }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $supervision->createdAt?->format('d/m/Y H:i') ?? '-' }}</p>
                    </div>
                    @php
                        $statusValue = is_object($supervision->status) ? $supervision->status->value : $supervision->status;
                        $statusConfig = [
                            'DRAFT' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => 'ร่าง'],
                            'SUBMITTED' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'ส่งแล้ว'],
                            'APPROVED' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'อนุมัติแล้ว'],
                            'PUBLISHED' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'เผยแพร่แล้ว'],
                            'NEEDS_IMPROVEMENT' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'ต้องปรับปรุง'],
                        ];
                        $config = $statusConfig[$statusValue] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => $statusValue];
                    @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $config['bg'] }} {{ $config['text'] }}">
                        {{ $config['label'] }}
                    </span>
                </div>
            @empty
                <div class="py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                    <p class="mt-4 text-gray-500">ยังไม่มีข้อมูลการนิเทศ</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js default config
    Chart.defaults.font.family = "'Sarabun', 'Prompt', sans-serif";
    Chart.defaults.color = '#6b7280';
    
    // Status Pie Chart
    const statusData = @json($this->statusChartData);
    new Chart(document.getElementById('statusPieChart'), {
        type: 'pie',
        data: {
            labels: statusData.labels,
            datasets: [{
                data: statusData.data,
                backgroundColor: statusData.colors,
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { 
                        font: { size: 11 },
                        padding: 15,
                        usePointStyle: true,
                    }
                }
            }
        }
    });

    // Policy Pie Chart
    const policyData = @json($this->policyUsage);
    new Chart(document.getElementById('policyPieChart'), {
        type: 'pie',
        data: {
            labels: policyData.labels,
            datasets: [{
                data: policyData.data,
                backgroundColor: policyData.colors,
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { 
                        font: { size: 11 },
                        padding: 15,
                        usePointStyle: true,
                    }
                }
            }
        }
    });

    // Indicator Donut Chart
    const indicatorDonutData = @json($this->indicatorDonut);
    new Chart(document.getElementById('indicatorDonutChart'), {
        type: 'doughnut',
        data: {
            labels: indicatorDonutData.labels,
            datasets: [{
                data: indicatorDonutData.data,
                backgroundColor: indicatorDonutData.colors,
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'right',
                    labels: { 
                        font: { size: 12 },
                        padding: 15,
                        usePointStyle: true,
                    }
                }
            }
        }
    });

    // Radar Chart
    const radarData = @json($this->indicatorRadar);
    new Chart(document.getElementById('radarChart'), {
        type: 'radar',
        data: {
            labels: radarData.labels,
            datasets: [
                {
                    label: 'ดี',
                    data: radarData.good,
                    backgroundColor: 'rgba(59, 130, 246, 0.15)',
                    borderColor: '#3b82f6',
                    borderWidth: 2,
                    pointBackgroundColor: '#3b82f6',
                },
                {
                    label: 'ดีเยี่ยม',
                    data: radarData.excellent,
                    backgroundColor: 'rgba(34, 197, 94, 0.15)',
                    borderColor: '#22c55e',
                    borderWidth: 2,
                    pointBackgroundColor: '#22c55e',
                },
                {
                    label: 'ต้องพัฒนา',
                    data: radarData.needsWork,
                    backgroundColor: 'rgba(239, 68, 68, 0.15)',
                    borderColor: '#ef4444',
                    borderWidth: 2,
                    pointBackgroundColor: '#ef4444',
                },
                {
                    label: 'พอใช้',
                    data: radarData.fair,
                    backgroundColor: 'rgba(234, 179, 8, 0.15)',
                    borderColor: '#eab308',
                    borderWidth: 2,
                    pointBackgroundColor: '#eab308',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                r: {
                    ticks: { font: { size: 9 } },
                    pointLabels: { font: { size: 9 } },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                }
            }
        }
    });

    // Yearly Trend Line Chart
    const yearlyData = @json($this->yearlyTrend);
    new Chart(document.getElementById('yearlyTrendChart'), {
        type: 'line',
        data: {
            labels: yearlyData.labels,
            datasets: [{
                label: 'จำนวนการนิเทศ',
                data: yearlyData.data,
                borderColor: '#ec4899',
                backgroundColor: 'rgba(236, 72, 153, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 5,
                pointBackgroundColor: '#ec4899',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { 
                        font: { size: 11 },
                        usePointStyle: true,
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Network Group Bar Chart
    const networkData = @json($this->networkGroupChart);
    new Chart(document.getElementById('networkGroupChart'), {
        type: 'bar',
        data: {
            labels: networkData.labels,
            datasets: [{
                label: 'จำนวนการนิเทศ',
                data: networkData.data,
                backgroundColor: '#d946ef',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                y: {
                    grid: { display: false }
                }
            }
        }
    });

    // District Bar Chart
    const districtData = @json($this->districtChart);
    new Chart(document.getElementById('districtChart'), {
        type: 'bar',
        data: {
            labels: districtData.labels,
            datasets: [{
                label: 'จำนวนการนิเทศ',
                data: districtData.data,
                backgroundColor: '#3b82f6',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Supervisor Performance Bar Chart
    const supervisorData = @json($this->supervisorPerformance);
    new Chart(document.getElementById('supervisorChart'), {
        type: 'bar',
        data: {
            labels: supervisorData.labels,
            datasets: [
                {
                    label: 'จำนวนทั้งหมด',
                    data: supervisorData.total,
                    backgroundColor: '#3b82f6',
                    borderRadius: 4,
                },
                {
                    label: 'อนุมัติแล้ว',
                    data: supervisorData.approved,
                    backgroundColor: '#22c55e',
                    borderRadius: 4,
                },
                {
                    label: 'อัตราการอนุมัติ (%)',
                    data: supervisorData.rate,
                    backgroundColor: '#d946ef',
                    borderRadius: 4,
                    yAxisID: 'percentage',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { 
                        font: { size: 10 },
                        padding: 10,
                        usePointStyle: true,
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                percentage: {
                    position: 'right',
                    beginAtZero: true,
                    max: 100,
                    grid: { drawOnChartArea: false }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
@endpush
