<div class="max-w-2xl mx-auto">
    <!-- Summary -->
    <div class="mb-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
        <h3 class="text-base font-semibold text-gray-900 mb-4">สรุปการนิเทศ</h3>
        <dl class="space-y-3">
            <div>
                <dt class="text-xs text-gray-500">โรงเรียน</dt>
                <dd class="text-sm font-medium text-gray-900">{{ $supervision->school->name }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">ประเภท</dt>
                <dd class="text-sm text-gray-900">{{ $supervision->type }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">วันที่นิเทศ</dt>
                <dd class="text-sm text-gray-900">{{ $supervision->date->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">ผู้นิเทศ</dt>
                <dd class="text-sm text-gray-900">{{ $supervision->user->name }}</dd>
            </div>
        </dl>
    </div>

    <!-- Acknowledge Form -->
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
        <h3 class="text-base font-semibold text-gray-900 mb-6">ยืนยันการรับทราบ</h3>
        
        <form wire:submit="submit" class="space-y-6">
            <div>
                <label for="acknowledged_by" class="block text-sm font-medium leading-6 text-gray-900">
                    ชื่อผู้รับทราบ <span class="text-red-500">*</span>
                </label>
                <div class="mt-2">
                    <input wire:model="acknowledged_by" 
                           type="text" 
                           id="acknowledged_by"
                           placeholder="ชื่อ-นามสกุล ตำแหน่ง"
                           class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 @error('acknowledged_by') ring-red-300 @enderror">
                </div>
                @error('acknowledged_by')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="comment" class="block text-sm font-medium leading-6 text-gray-900">ความคิดเห็น</label>
                <div class="mt-2">
                    <textarea wire:model="comment" 
                              id="comment"
                              rows="3"
                              placeholder="ความคิดเห็นเพิ่มเติม (ถ้ามี)"
                              class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4">
                <a href="{{ route('supervisions.show', $supervision) }}" 
                   class="text-sm font-medium text-gray-600 hover:text-gray-500">
                    ← ย้อนกลับ
                </a>
                <button type="submit" 
                        class="rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 transition-colors">
                    ยืนยันรับทราบ
                </button>
            </div>
        </form>
    </div>
</div>
