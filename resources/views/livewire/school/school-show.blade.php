<div>
    <!-- Header -->
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('schools.index') }}" class="text-gray-400 hover:text-gray-500">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900">{{ $school->name }}</h2>
            <p class="text-sm text-gray-500">{{ $school->code }} • {{ $school->district }}</p>
        </div>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('schools.edit', $school) }}" 
               class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                </svg>
                แก้ไข
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">ข้อมูลพื้นฐาน</h3>
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs text-gray-500">ชื่อโรงเรียน</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $school->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">รหัสโรงเรียน</dt>
                        <dd class="text-sm text-gray-900">{{ $school->code }}</dd>
                    </div>
                    @if($school->principalName)
                    <div>
                        <dt class="text-xs text-gray-500">ผู้อำนวยการ</dt>
                        <dd class="text-sm text-gray-900">{{ $school->principalName }}</dd>
                    </div>
                    @endif
                    @if($school->networkGroupRelation)
                    <div>
                        <dt class="text-xs text-gray-500">กลุ่มเครือข่าย</dt>
                        <dd class="text-sm text-gray-900">{{ $school->networkGroupRelation->name }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Location -->
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">ที่ตั้ง</h3>
                <dl class="grid grid-cols-2 gap-4">
                    @if($school->province)
                    <div>
                        <dt class="text-xs text-gray-500">จังหวัด</dt>
                        <dd class="text-sm text-gray-900">{{ $school->province }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-500">อำเภอ</dt>
                        <dd class="text-sm text-gray-900">{{ $school->district }}</dd>
                    </div>
                    @if($school->subDistrict)
                    <div>
                        <dt class="text-xs text-gray-500">ตำบล</dt>
                        <dd class="text-sm text-gray-900">{{ $school->subDistrict }}</dd>
                    </div>
                    @endif
                    @if($school->address)
                    <div class="col-span-2">
                        <dt class="text-xs text-gray-500">ที่อยู่</dt>
                        <dd class="text-sm text-gray-900">{{ $school->address }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Recent Supervisions -->
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">การนิเทศล่าสุด</h3>
                        <span class="text-xs text-gray-500">{{ $school->supervisions->count() }} รายการ</span>
                    </div>
                </div>
                <ul role="list" class="divide-y divide-gray-100">
                    @forelse($school->supervisions as $supervision)
                        <li class="px-6 py-4">
                            <div class="flex items-center justify-between gap-x-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $supervision->type }}</p>
                                    <div class="mt-1 flex items-center gap-x-2 text-xs text-gray-500">
                                        <span>{{ $supervision->user->name }}</span>
                                        <span>•</span>
                                        <span>{{ $supervision->date->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-x-3">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $supervision->status->bgClass() }}">
                                        {{ $supervision->status->label() }}
                                    </span>
                                    <a href="{{ route('supervisions.show', $supervision) }}" class="text-pink-600 hover:text-pink-500">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-12 text-center">
                            <p class="text-sm text-gray-500">ยังไม่มีการนิเทศ</p>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Contact -->
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">ข้อมูลติดต่อ</h3>
                <dl class="space-y-3">
                    @if($school->phone)
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                        </svg>
                        <span class="text-sm text-gray-900">{{ $school->phone }}</span>
                    </div>
                    @endif
                    @if($school->email)
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        <span class="text-sm text-gray-900">{{ $school->email }}</span>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Statistics -->
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">สถิติ</h3>
                <dl class="space-y-3">
                    @if($school->studentCount)
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-500">จำนวนนักเรียน</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ number_format($school->studentCount) }}</dd>
                    </div>
                    @endif
                    @if($school->teacherCount)
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-500">จำนวนครู</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ number_format($school->teacherCount) }}</dd>
                    </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-500">การนิเทศทั้งหมด</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $school->supervisions->count() }} ครั้ง</dd>
                    </div>
                </dl>
            </div>

            <!-- Supervisors -->
            @if($school->supervisors->count() > 0)
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">ศึกษานิเทศก์ที่รับผิดชอบ</h3>
                <ul class="space-y-3">
                    @foreach($school->supervisors as $supervisor)
                        <li class="flex items-center gap-3">
                            @if($supervisor->image)
                                <img class="h-8 w-8 rounded-full" src="{{ $supervisor->image }}" alt="">
                            @else
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-pink-600">
                                    <span class="text-xs font-medium text-white">{{ substr($supervisor->name, 0, 1) }}</span>
                                </span>
                            @endif
                            <span class="text-sm text-gray-900">{{ $supervisor->name }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
</div>
