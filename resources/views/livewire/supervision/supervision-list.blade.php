<div>
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">รายการการนิเทศ</h2>
            <p class="mt-1 text-sm text-gray-500">บันทึกการนิเทศติดตามโรงเรียน</p>
        </div>
        @if(auth()->user()->isSupervisor() || auth()->user()->isAdmin())
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('supervisions.create') }}" 
               class="inline-flex items-center rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 transition-colors">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                บันทึกการนิเทศ
            </a>
        </div>
        @endif
    </div>

    <!-- Filters -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-5">
        <div class="sm:col-span-2">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" 
                       type="search" 
                       placeholder="ค้นหาชื่อโรงเรียน..."
                       class="block w-full rounded-lg border-0 py-2.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
            </div>
        </div>
        <div>
            <select wire:model.live="status" 
                    class="block w-full rounded-lg border-0 py-2.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                <option value="">ทุกสถานะ</option>
                @foreach($this->statuses as $s)
                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select wire:model.live="schoolId" 
                    class="block w-full rounded-lg border-0 py-2.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                <option value="">ทุกโรงเรียน</option>
                @foreach($this->schools as $school)
                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select wire:model.live="academicYear" 
                    class="block w-full rounded-lg border-0 py-2.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                <option value="">ทุกปีการศึกษา</option>
                @foreach($this->academicYears as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Cards View -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($supervisions as $supervision)
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $supervision->school->name }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ $supervision->type }}</p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $supervision->status->bgClass() }}">
                        {{ $supervision->status->label() }}
                    </span>
                </div>

                <div class="mt-4 space-y-2">
                    <div class="flex items-center text-xs text-gray-500">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        {{ $supervision->date->format('d/m/Y') }}
                        @if($supervision->academicYear)
                            <span class="mx-1">•</span>
                            ปีการศึกษา {{ $supervision->academicYear }}
                        @endif
                    </div>
                    <div class="flex items-center text-xs text-gray-500">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        {{ $supervision->user->name }}
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between pt-4 border-t border-gray-100">
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        @if($supervision->indicators->count() > 0)
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $supervision->indicators->count() }} ตัวชี้วัด
                            </span>
                        @endif
                        @if($supervision->attachments->count() > 0)
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                </svg>
                                {{ $supervision->attachments->count() }}
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('supervisions.show', $supervision) }}" 
                           class="text-pink-600 hover:text-pink-500 font-medium text-sm">
                            ดูรายละเอียด
                        </a>
                        @if((auth()->user()->isAdmin() || $supervision->userId === auth()->id()) && $supervision->status->value === 'DRAFT')
                            <button wire:click="confirmDelete('{{ $supervision->id }}')" 
                                    class="text-gray-400 hover:text-red-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="rounded-xl bg-white px-6 py-12 text-center shadow-sm ring-1 ring-gray-900/5">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">ไม่พบการนิเทศ</h3>
                    <p class="mt-1 text-sm text-gray-500">ไม่มีข้อมูลการนิเทศที่ตรงกับเงื่อนไขการค้นหา</p>
                    @if(auth()->user()->isSupervisor() || auth()->user()->isAdmin())
                    <div class="mt-6">
                        <a href="{{ route('supervisions.create') }}" 
                           class="inline-flex items-center rounded-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-pink-500">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            บันทึกการนิเทศ
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $supervisions->links() }}
    </div>
</div>
