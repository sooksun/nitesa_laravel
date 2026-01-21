<div class="max-w-2xl">
    <div class="space-y-8">
        <!-- Profile Info -->
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
            <h3 class="text-base font-semibold leading-7 text-gray-900 mb-6">ข้อมูลส่วนตัว</h3>
            
            <form wire:submit="updateProfile" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium leading-6 text-gray-900">ชื่อ-นามสกุล</label>
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

                <div class="flex justify-end">
                    <button type="submit" 
                            class="rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 transition-colors">
                        บันทึกข้อมูล
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
            <h3 class="text-base font-semibold leading-7 text-gray-900 mb-6">เปลี่ยนรหัสผ่าน</h3>
            
            <form wire:submit="updatePassword" class="space-y-6">
                <div>
                    <label for="current_password" class="block text-sm font-medium leading-6 text-gray-900">รหัสผ่านปัจจุบัน</label>
                    <div class="mt-2">
                        <input wire:model="current_password" 
                               type="password" 
                               id="current_password"
                               class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 @error('current_password') ring-red-300 @enderror">
                    </div>
                    @error('current_password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new_password" class="block text-sm font-medium leading-6 text-gray-900">รหัสผ่านใหม่</label>
                    <div class="mt-2">
                        <input wire:model="new_password" 
                               type="password" 
                               id="new_password"
                               class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 @error('new_password') ring-red-300 @enderror">
                    </div>
                    @error('new_password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new_password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">ยืนยันรหัสผ่านใหม่</label>
                    <div class="mt-2">
                        <input wire:model="new_password_confirmation" 
                               type="password" 
                               id="new_password_confirmation"
                               class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 transition-colors">
                        เปลี่ยนรหัสผ่าน
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
