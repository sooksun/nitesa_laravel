<div class="max-w-2xl">
    <form wire:submit="save">
        <div class="space-y-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-base font-semibold leading-7 text-gray-900 mb-6">ข้อมูลนโยบาย</h3>
                
                <div class="space-y-6">
                    <div>
                        <label for="type" class="block text-sm font-medium leading-6 text-gray-900">ประเภทนโยบาย <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <select wire:model="type" 
                                    id="type"
                                    class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 @error('type') ring-red-300 @enderror">
                                <option value="">เลือกประเภท</option>
                                @foreach($this->policyTypes as $t)
                                    <option value="{{ $t->value }}">{{ $t->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="code" class="block text-sm font-medium leading-6 text-gray-900">รหัสนโยบาย <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input wire:model="code" 
                                   type="text" 
                                   id="code"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 @error('code') ring-red-300 @enderror"
                                   placeholder="เช่น POL001">
                        </div>
                        @error('code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-medium leading-6 text-gray-900">ชื่อนโยบาย <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input wire:model="title" 
                                   type="text" 
                                   id="title"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 @error('title') ring-red-300 @enderror"
                                   placeholder="ชื่อนโยบาย">
                        </div>
                        @error('title')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium leading-6 text-gray-900">รายละเอียด</label>
                        <div class="mt-2">
                            <textarea wire:model="description" 
                                      id="description"
                                      rows="4"
                                      class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6"
                                      placeholder="รายละเอียดเพิ่มเติม..."></textarea>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
<input wire:model="isActive" 
                               type="checkbox"
                               id="isActive"
                               class="h-4 w-4 rounded border-gray-300 text-pink-600 focus:ring-pink-600">
                        <label for="isActive" class="text-sm font-medium text-gray-900">เปิดใช้งาน</label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-x-4">
                <a href="{{ route('policies.index') }}" 
                   class="rounded-lg px-4 py-2.5 text-sm font-semibold text-gray-900 hover:bg-gray-100 transition-colors">
                    ยกเลิก
                </a>
                <button type="submit" 
                        class="rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 transition-colors">
                    {{ $editing ? 'บันทึกการแก้ไข' : 'เพิ่มนโยบาย' }}
                </button>
            </div>
        </div>
    </form>
</div>
