<div>
    <!-- Header with actions -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('supervisions.index') }}" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ $supervision->school->name }}</h2>
                <p class="text-sm text-gray-500">{{ $supervision->type }} • {{ $supervision->date->format('d/m/Y') }}</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $supervision->status->bgClass() }}">
                {{ $supervision->status->label() }}
            </span>

            <!-- Action Buttons -->
            @if($this->canEdit())
                <a href="{{ route('supervisions.edit', $supervision) }}" 
                   class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    แก้ไข
                </a>
            @endif

            @if($this->canSubmit())
                <button wire:click="confirmSubmit"
                        class="inline-flex items-center rounded-lg bg-yellow-500 px-3 py-2 text-sm font-medium text-white hover:bg-yellow-400 transition-colors">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                    ส่งเพื่ออนุมัติ
                </button>
            @endif

            @if($this->canApprove())
                <button wire:click="confirmReject"
                        class="inline-flex items-center rounded-lg bg-red-100 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-200 transition-colors">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                    </svg>
                    ส่งกลับ
                </button>
                <button wire:click="confirmApprove"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-500 transition-colors">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    อนุมัติ
                </button>
            @endif

            @if($this->canPublish())
                <button wire:click="confirmPublish"
                        class="inline-flex items-center rounded-lg bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-500 transition-colors">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                    </svg>
                    เผยแพร่
                </button>
            @endif
        </div>
    </div>

    <!-- Workflow Status -->
    <div class="mb-8 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">สถานะการดำเนินการ</h3>
        <div class="flex items-center">
            @php
                $steps = [
                    ['key' => 'DRAFT', 'label' => 'ร่าง'],
                    ['key' => 'SUBMITTED', 'label' => 'ส่งแล้ว'],
                    ['key' => 'APPROVED', 'label' => 'อนุมัติแล้ว'],
                    ['key' => 'PUBLISHED', 'label' => 'เผยแพร่แล้ว'],
                ];
                $currentIndex = array_search($supervision->status->value, array_column($steps, 'key'));
                if ($supervision->status->value === 'NEEDS_IMPROVEMENT') {
                    $currentIndex = 0;
                }
            @endphp
            @foreach($steps as $index => $step)
                <div class="flex items-center {{ $index < count($steps) - 1 ? 'flex-1' : '' }}">
                    <div class="relative flex items-center justify-center">
                        @if($index < $currentIndex || ($index === $currentIndex && $supervision->status->value === 'PUBLISHED'))
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-pink-600">
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </span>
                        @elseif($index === $currentIndex)
                            <span class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-pink-600 bg-white">
                                <span class="h-2.5 w-2.5 rounded-full bg-pink-600"></span>
                            </span>
                        @else
                            <span class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-300 bg-white">
                                <span class="h-2.5 w-2.5 rounded-full bg-gray-300"></span>
                            </span>
                        @endif
                    </div>
                    <span class="ml-2 text-sm font-medium {{ $index <= $currentIndex ? 'text-gray-900' : 'text-gray-500' }}">{{ $step['label'] }}</span>
                    @if($index < count($steps) - 1)
                        <div class="flex-1 mx-4 h-0.5 {{ $index < $currentIndex ? 'bg-pink-600' : 'bg-gray-200' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
        @if($supervision->status->value === 'NEEDS_IMPROVEMENT')
            <div class="mt-4 rounded-lg bg-red-50 p-3">
                <p class="text-sm text-red-700">
                    <strong>หมายเหตุ:</strong> การนิเทศนี้ถูกส่งกลับเพื่อปรับปรุง กรุณาแก้ไขและส่งใหม่อีกครั้ง
                </p>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- School Info -->
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">ข้อมูลโรงเรียน</h3>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-gray-500">ชื่อโรงเรียน</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $supervision->school->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">รหัสโรงเรียน</dt>
                        <dd class="text-sm text-gray-900">{{ $supervision->school->code }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">อำเภอ</dt>
                        <dd class="text-sm text-gray-900">{{ $supervision->school->district }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">กลุ่มเครือข่าย</dt>
                        <dd class="text-sm text-gray-900">{{ $supervision->school->networkGroupRelation?->name ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Indicators -->
            @if($supervision->indicators->count() > 0)
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">ตัวชี้วัดการนิเทศ</h3>
                <div class="space-y-3">
                    @foreach($supervision->indicators as $indicator)
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 p-4">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $indicator->name }}</p>
                                @if($indicator->comment)
                                    <p class="text-xs text-gray-500 mt-1">{{ $indicator->comment }}</p>
                                @endif
                            </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium
                                {{ match($indicator->level->value) {
                                    'EXCELLENT' => 'bg-green-100 text-green-700',
                                    'GOOD' => 'bg-blue-100 text-blue-700',
                                    'FAIR' => 'bg-yellow-100 text-yellow-700',
                                    'NEEDS_WORK' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-100 text-gray-700'
                                } }}">
                                {{ $indicator->level->label() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Summary -->
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">สรุปผลการนิเทศ</h3>
                <div class="prose prose-sm max-w-none text-gray-700">
                    {!! nl2br(e($supervision->summary)) !!}
                </div>
            </div>

            <!-- Suggestions -->
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">ข้อเสนอแนะ</h3>
                <div class="prose prose-sm max-w-none text-gray-700">
                    {!! nl2br(e($supervision->suggestions)) !!}
                </div>
            </div>

            <!-- Attachments -->
            @if($supervision->attachments->count() > 0)
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">ไฟล์แนบ</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach($supervision->attachments as $attachment)
                        @php
                            $fileUrl = $attachment->getUrl();
                            $fileExists = $attachment->fileExists();
                        @endphp
                        <div class="relative group">
                            @if($fileExists && $attachment->isImage() && $fileUrl)
                                <img src="{{ $fileUrl }}" 
                                     alt="{{ $attachment->filename }}"
                                     class="w-full h-32 object-cover rounded-lg"
                                     onerror="this.onerror=null; this.src='{{ asset('images/file-not-found.png') }}';">
                            @else
                                <div class="w-full h-32 rounded-lg {{ $fileExists ? 'bg-gray-100' : 'bg-red-50 border-2 border-red-200' }} flex items-center justify-center">
                                    @if(!$fileExists)
                                        <div class="text-center">
                                            <svg class="h-8 w-8 text-red-400 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                            </svg>
                                            <p class="text-xs text-red-600 font-medium">ไฟล์ไม่พบ</p>
                                        </div>
                                    @else
                                        <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                    @endif
                                </div>
                            @endif
                            @if($fileExists && $fileUrl)
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 rounded-lg transition-all flex items-center justify-center">
                                    <a href="{{ $fileUrl }}" 
                                       target="_blank"
                                       class="opacity-0 group-hover:opacity-100 inline-flex items-center rounded-lg bg-white px-3 py-2 text-xs font-medium text-gray-700 shadow-sm transition-opacity">
                                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                        </svg>
                                        ดาวน์โหลด
                                    </a>
                                </div>
                            @endif
                            <div class="mt-2">
                                <p class="text-xs text-gray-500 truncate" title="{{ $attachment->filename }}">
                                    {{ $attachment->filename }}
                                </p>
                                @if($fileExists)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $attachment->fileSizeFormatted }}</p>
                                @else
                                    <p class="text-xs text-red-500 mt-0.5">ไฟล์หายไป</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Meta Info -->
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">ข้อมูลการนิเทศ</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-xs text-gray-500">ประเภท</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $supervision->type }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">วันที่นิเทศ</dt>
                        <dd class="text-sm text-gray-900">{{ $supervision->date->format('d/m/Y') }}</dd>
                    </div>
                    @if($supervision->academicYear)
                    <div>
                        <dt class="text-xs text-gray-500">ปีการศึกษา</dt>
                        <dd class="text-sm text-gray-900">{{ $supervision->academicYear }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-500">ผู้นิเทศ</dt>
                        <dd class="text-sm text-gray-900">{{ $supervision->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">บันทึกเมื่อ</dt>
                        <dd class="text-sm text-gray-900">{{ $supervision->createdAt?->format('d/m/Y H:i') ?? '-' }}</dd>
                    </div>
                    @if($supervision->updatedAt && $supervision->updatedAt != $supervision->createdAt)
                    <div>
                        <dt class="text-xs text-gray-500">แก้ไขล่าสุด</dt>
                        <dd class="text-sm text-gray-900">{{ $supervision->updatedAt->format('d/m/Y H:i') }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Policies -->
            @if($supervision->ministerPolicyRelation || $supervision->obecPolicyRelation || $supervision->areaPolicyRelation)
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">นโยบายที่เกี่ยวข้อง</h3>
                <dl class="space-y-4">
                    @if($supervision->ministerPolicyRelation)
                    <div>
                        <dt class="text-xs text-gray-500">นโยบายกระทรวง</dt>
                        <dd class="text-sm text-gray-900">{{ $supervision->ministerPolicyRelation->title }}</dd>
                    </div>
                    @endif
                    @if($supervision->obecPolicyRelation)
                    <div>
                        <dt class="text-xs text-gray-500">นโยบาย สพฐ.</dt>
                        <dd class="text-sm text-gray-900">{{ $supervision->obecPolicyRelation->title }}</dd>
                    </div>
                    @endif
                    @if($supervision->areaPolicyRelation)
                    <div>
                        <dt class="text-xs text-gray-500">นโยบายเขตพื้นที่</dt>
                        <dd class="text-sm text-gray-900">{{ $supervision->areaPolicyRelation->title }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif

            <!-- Acknowledgement -->
            @if($supervision->acknowledgement)
            <div class="rounded-xl bg-green-50 p-6 shadow-sm ring-1 ring-green-200">
                <h3 class="text-sm font-semibold text-green-900 mb-4">
                    <svg class="inline-block h-5 w-5 mr-1.5 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    โรงเรียนรับทราบแล้ว
                </h3>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-xs text-green-700">รับทราบโดย</dt>
                        <dd class="text-sm font-medium text-green-900">{{ $supervision->acknowledgement->acknowledgedBy }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-green-700">วันที่รับทราบ</dt>
                        <dd class="text-sm text-green-900">{{ $supervision->acknowledgement->acknowledgedAt?->format('d/m/Y H:i') ?? '-' }}</dd>
                    </div>
                    @if($supervision->acknowledgement->comment)
                    <div>
                        <dt class="text-xs text-green-700">ความคิดเห็น</dt>
                        <dd class="text-sm text-green-900">{{ $supervision->acknowledgement->comment }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            @elseif($supervision->status->value === 'PUBLISHED' && auth()->user()->isSchool())
            <div class="rounded-xl bg-yellow-50 p-6 shadow-sm ring-1 ring-yellow-200">
                <h3 class="text-sm font-semibold text-yellow-900 mb-4">
                    <svg class="inline-block h-5 w-5 mr-1.5 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    รอการรับทราบ
                </h3>
                <p class="text-sm text-yellow-800 mb-4">กรุณายืนยันการรับทราบผลการนิเทศ</p>
                <a href="{{ route('supervisions.acknowledge', $supervision) }}" 
                   class="inline-flex w-full justify-center items-center rounded-lg bg-yellow-500 px-3 py-2 text-sm font-medium text-white hover:bg-yellow-400 transition-colors">
                    ยืนยันรับทราบ
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
