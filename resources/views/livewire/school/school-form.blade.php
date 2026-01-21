<div class="max-w-4xl">
    <form wire:submit="save">
        <div class="space-y-8">
            <!-- Basic Info -->
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-base font-semibold leading-7 text-gray-900 mb-6">ข้อมูลพื้นฐาน</h3>
                
                <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <div class="sm:col-span-2">
                        <label for="code" class="block text-sm font-medium leading-6 text-gray-900">รหัสโรงเรียน <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input wire:model="code" 
                                   type="text" 
                                   id="code"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 @error('code') ring-red-300 @enderror"
                                   placeholder="เช่น 1044500001">
                        </div>
                        @error('code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-4">
                        <label for="name" class="block text-sm font-medium leading-6 text-gray-900">ชื่อโรงเรียน <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input wire:model="name" 
                                   type="text" 
                                   id="name"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 @error('name') ring-red-300 @enderror"
                                   placeholder="ชื่อโรงเรียน">
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-3">
                        <label for="principalName" class="block text-sm font-medium leading-6 text-gray-900">ชื่อผู้อำนวยการ</label>
                        <div class="mt-2">
                            <input wire:model="principalName" 
                                   type="text" 
                                   id="principalName"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="networkGroupId" class="block text-sm font-medium leading-6 text-gray-900">กลุ่มเครือข่าย</label>
                        <div class="mt-2">
                            <select wire:model="networkGroupId" 
                                    id="networkGroupId"
                                    class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                                <option value="">เลือกกลุ่มเครือข่าย</option>
                                @foreach($this->networkGroups as $ng)
                                    <option value="{{ $ng->id }}">{{ $ng->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-base font-semibold leading-7 text-gray-900 mb-6">ที่ตั้ง</h3>
                
                <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <div class="sm:col-span-2">
                        <label for="province" class="block text-sm font-medium leading-6 text-gray-900">จังหวัด</label>
                        <div class="mt-2">
                            <input wire:model="province" 
                                   type="text" 
                                   id="province"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="district" class="block text-sm font-medium leading-6 text-gray-900">อำเภอ <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input wire:model="district" 
                                   type="text" 
                                   id="district"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 @error('district') ring-red-300 @enderror">
                        </div>
                        @error('district')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="subDistrict" class="block text-sm font-medium leading-6 text-gray-900">ตำบล</label>
                        <div class="mt-2">
                            <input wire:model="subDistrict" 
                                   type="text" 
                                   id="subDistrict"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="col-span-full">
                        <label for="address" class="block text-sm font-medium leading-6 text-gray-900">ที่อยู่</label>
                        <div class="mt-2">
                            <textarea wire:model="address" 
                                      id="address"
                                      rows="2"
                                      class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact & Stats -->
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-base font-semibold leading-7 text-gray-900 mb-6">ข้อมูลติดต่อและสถิติ</h3>
                
                <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="phone" class="block text-sm font-medium leading-6 text-gray-900">โทรศัพท์</label>
                        <div class="mt-2">
                            <input wire:model="phone" 
                                   type="text" 
                                   id="phone"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="email" class="block text-sm font-medium leading-6 text-gray-900">อีเมล</label>
                        <div class="mt-2">
                            <input wire:model="email" 
                                   type="email" 
                                   id="email"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 @error('email') ring-red-300 @enderror">
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-3">
                        <label for="studentCount" class="block text-sm font-medium leading-6 text-gray-900">จำนวนนักเรียน</label>
                        <div class="mt-2">
                            <input wire:model="studentCount" 
                                   type="number" 
                                   id="studentCount"
                                   min="0"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="teacherCount" class="block text-sm font-medium leading-6 text-gray-900">จำนวนครู</label>
                        <div class="mt-2">
                            <input wire:model="teacherCount" 
                                   type="number" 
                                   id="teacherCount"
                                   min="0"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-x-4">
                <a href="{{ route('schools.index') }}" 
                   class="rounded-lg px-4 py-2.5 text-sm font-semibold text-gray-900 hover:bg-gray-100 transition-colors">
                    ยกเลิก
                </a>
                <button type="submit" 
                        class="rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600 transition-colors">
                    {{ $editing ? 'บันทึกการแก้ไข' : 'เพิ่มโรงเรียน' }}
                </button>
            </div>
        </div>
    </form>
</div>
