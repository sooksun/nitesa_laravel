<div class="max-w-2xl">
    <form wire:submit="save">
        <div class="space-y-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-base font-semibold leading-7 text-gray-900 mb-6">ตั้งค่าทั่วไป</h3>
                
                <div class="space-y-6">
                    <div>
                        <label for="site_name" class="block text-sm font-medium leading-6 text-gray-900">ชื่อระบบ</label>
                        <div class="mt-2">
                            <input wire:model="site_name" 
                                   type="text" 
                                   id="site_name"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div>
                        <label for="area_office_name" class="block text-sm font-medium leading-6 text-gray-900">ชื่อสำนักงานเขตพื้นที่</label>
                        <div class="mt-2">
                            <input wire:model="area_office_name" 
                                   type="text" 
                                   id="area_office_name"
                                   placeholder="สำนักงานเขตพื้นที่การศึกษาประถมศึกษา..."
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div>
                        <label for="current_academic_year" class="block text-sm font-medium leading-6 text-gray-900">ปีการศึกษาปัจจุบัน</label>
                        <div class="mt-2">
                            <input wire:model="current_academic_year" 
                                   type="text" 
                                   id="current_academic_year"
                                   placeholder="2567"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <input wire:model="allow_registration" 
                               type="checkbox" 
                               id="allow_registration"
                               class="h-4 w-4 rounded border-gray-300 text-pink-600 focus:ring-pink-600">
                        <label for="allow_registration" class="text-sm font-medium text-gray-900">เปิดให้ลงทะเบียนเอง</label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" 
                        class="rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 transition-colors">
                    บันทึกการตั้งค่า
                </button>
            </div>
        </div>
    </form>
</div>
