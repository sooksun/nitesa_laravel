<div class="max-w-2xl">
    <form wire:submit="save">
        <div class="space-y-8">
            <!-- Basic Info -->
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-base font-semibold leading-7 text-gray-900 mb-6">ข้อมูลผู้ใช้</h3>
                
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium leading-6 text-gray-900">ชื่อ-นามสกุล <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input wire:model="name" 
                                   type="text" 
                                   id="name"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 @error('name') ring-red-300 @enderror">
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium leading-6 text-gray-900">อีเมล <span class="text-red-500">*</span></label>
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

                    <div>
                        <label for="role" class="block text-sm font-medium leading-6 text-gray-900">บทบาท <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <select wire:model.live="role" 
                                    id="role"
                                    class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                                @foreach($this->roles as $r)
                                    <option value="{{ $r->value }}">{{ $r->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if($role === 'SUPERVISOR')
                    <div>
                        <label class="block text-sm font-medium leading-6 text-gray-900 mb-2">โรงเรียนที่รับผิดชอบ</label>
                        <div class="max-h-64 overflow-y-auto rounded-lg border border-gray-300 p-3 space-y-2">
                            @foreach($this->schools as $school)
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded-lg">
                                    <input wire:model="assignedSchools" 
                                           type="checkbox" 
                                           value="{{ $school->id }}"
                                           class="h-4 w-4 rounded border-gray-300 text-pink-600 focus:ring-pink-600">
                                    <span class="text-sm text-gray-700">{{ $school->name }}</span>
                                    <span class="text-xs text-gray-400">({{ $school->code }})</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1 text-xs text-gray-500">เลือกโรงเรียนที่ศึกษานิเทศก์จะรับผิดชอบดูแล</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Password -->
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-base font-semibold leading-7 text-gray-900 mb-6">
                    {{ $editing ? 'เปลี่ยนรหัสผ่าน' : 'ตั้งรหัสผ่าน' }}
                </h3>
                @if($editing)
                    <p class="text-sm text-gray-500 mb-6">เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน</p>
                @endif
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="password" class="block text-sm font-medium leading-6 text-gray-900">
                            รหัสผ่าน @if(!$editing)<span class="text-red-500">*</span>@endif
                        </label>
                        <div class="mt-2">
                            <input wire:model="password" 
                                   type="password" 
                                   id="password"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 @error('password') ring-red-300 @enderror"
                                   placeholder="••••••••">
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">
                            ยืนยันรหัสผ่าน @if(!$editing)<span class="text-red-500">*</span>@endif
                        </label>
                        <div class="mt-2">
                            <input wire:model="password_confirmation" 
                                   type="password" 
                                   id="password_confirmation"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6"
                                   placeholder="••••••••">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-x-4">
                <a href="{{ route('users.index') }}" 
                   class="rounded-lg px-4 py-2.5 text-sm font-semibold text-gray-900 hover:bg-gray-100 transition-colors">
                    ยกเลิก
                </a>
                <button type="submit" 
                        class="rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600 transition-colors">
                    {{ $editing ? 'บันทึกการแก้ไข' : 'เพิ่มผู้ใช้' }}
                </button>
            </div>
        </div>
    </form>
</div>
