<div>
    <!-- Header with Stats -->
    <div class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">กิจกรรมทั้งหมด</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($activities->total()) }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">วันนี้</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format(\Spatie\Activitylog\Models\Activity::whereDate('created_at', today())->count()) }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">ผู้ใช้งานที่มีกิจกรรม</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format(\Spatie\Activitylog\Models\Activity::distinct('causer_id')->count('causer_id')) }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-pink-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">ประเภทกิจกรรม</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $events->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">ตัวกรอง</h3>
            @if($search || $eventFilter || $subjectTypeFilter || $causerFilter || $dateFrom || $dateTo)
                <button wire:click="clearFilters" 
                        class="text-sm text-pink-600 hover:text-pink-500 font-medium">
                    ล้างตัวกรองทั้งหมด
                </button>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ค้นหา</label>
                <input type="text" 
                       wire:model.live.debounce.300ms="search"
                       placeholder="ค้นหาคำอธิบายหรือเหตุการณ์..."
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500">
            </div>

            <!-- Event Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">เหตุการณ์</label>
                <select wire:model.live="eventFilter"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">ทั้งหมด</option>
                    @foreach($events as $event)
                        <option value="{{ $event }}">{{ $event }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Subject Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ประเภทข้อมูล</label>
                <select wire:model.live="subjectTypeFilter"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">ทั้งหมด</option>
                    @foreach($subjectTypes as $type)
                        <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Causer Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ผู้กระทำ</label>
                <select wire:model.live="causerFilter"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">ทั้งหมด</option>
                    @foreach($causers as $causer)
                        <option value="{{ $causer->id }}">{{ $causer->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ตั้งแต่วันที่</label>
                <input type="date" 
                       wire:model.live="dateFrom"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ถึงวันที่</label>
                <input type="date" 
                       wire:model.live="dateTo"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500">
            </div>
        </div>
    </div>

    <!-- Activity Log Table -->
    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">
                            เวลา
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">
                            ผู้กระทำ
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">
                            เหตุการณ์
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">
                            คำอธิบาย
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">
                            ประเภทข้อมูล
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-900 uppercase tracking-wider">
                            รายละเอียด
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($activities as $activity)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <!-- Time -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $activity->created_at->format('d/m/Y') }}</span>
                                    <span class="text-xs text-gray-500">{{ $activity->created_at->format('H:i:s') }}</span>
                                    <span class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</span>
                                </div>
                            </td>

                            <!-- Causer -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($activity->causer)
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-pink-100 flex items-center justify-center">
                                            <span class="text-pink-600 font-medium text-xs">
                                                {{ strtoupper(substr($activity->causer->name, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">{{ $activity->causer->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $activity->causer->role->label() }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">ระบบ</span>
                                @endif
                            </td>

                            <!-- Event -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium
                                    {{ match($activity->event) {
                                        'created' => 'bg-green-100 text-green-700',
                                        'updated' => 'bg-blue-100 text-blue-700',
                                        'deleted' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700'
                                    } }}">
                                    {{ $activity->event }}
                                </span>
                            </td>

                            <!-- Description -->
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ $activity->description }}</p>
                            </td>

                            <!-- Subject Type -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $activity->subject_type ? class_basename($activity->subject_type) : '-' }}
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                <button 
                                    onclick="Swal.fire({
                                        title: 'รายละเอียดกิจกรรม',
                                        html: '<div class=\'text-left\'><pre class=\'text-xs bg-gray-50 p-4 rounded overflow-auto max-h-96\'>{{ json_encode([
                                            'ID' => $activity->id,
                                            'Event' => $activity->event,
                                            'Description' => $activity->description,
                                            'Subject Type' => $activity->subject_type,
                                            'Subject ID' => $activity->subject_id,
                                            'Causer' => $activity->causer?->name,
                                            'Properties' => $activity->properties->toArray(),
                                            'Created At' => $activity->created_at->toDateTimeString(),
                                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></div>',
                                        width: '800px',
                                        confirmButtonText: 'ปิด',
                                        confirmButtonColor: '#ec4899'
                                    })"
                                    class="text-pink-600 hover:text-pink-900">
                                    <svg class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">ไม่พบบันทึกกิจกรรม</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($activities->hasPages())
            <div class="border-t border-gray-200 bg-white px-4 py-3">
                {{ $activities->links() }}
            </div>
        @endif
    </div>

    <!-- Per Page Selector -->
    <div class="mt-4 flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <label class="text-sm text-gray-700">แสดง</label>
            <select wire:model.live="perPage" 
                    class="rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <label class="text-sm text-gray-700">รายการต่อหน้า</label>
        </div>

        <div class="text-sm text-gray-700">
            แสดง {{ $activities->firstItem() ?? 0 }} ถึง {{ $activities->lastItem() ?? 0 }} จากทั้งหมด {{ number_format($activities->total()) }} รายการ
        </div>
    </div>
</div>
