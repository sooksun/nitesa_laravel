<div class="max-w-3xl">
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
        <h3 class="text-base font-semibold leading-7 text-gray-900 mb-2">นำเข้าข้อมูลจาก Excel</h3>
        <p class="text-sm text-gray-500 mb-6">อัปโหลดไฟล์ Excel เพื่อนำเข้าข้อมูลเข้าสู่ระบบ</p>

        <form wire:submit="import">
            <div class="space-y-6">
                <div>
                    <label for="importType" class="block text-sm font-medium leading-6 text-gray-900">ประเภทข้อมูล</label>
                    <div class="mt-2">
                        <select wire:model="importType" 
                                id="importType"
                                class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                            <option value="schools">โรงเรียน</option>
                            <option value="policies">นโยบาย</option>
                            <option value="network_groups">กลุ่มเครือข่าย</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium leading-6 text-gray-900 mb-2">ไฟล์ Excel</label>
                    <div class="flex justify-center rounded-lg border border-dashed border-gray-300 px-6 py-10 cursor-pointer hover:border-pink-400 transition-colors"
                         onclick="document.getElementById('file').click()">
                        <div class="text-center">
                            @if($file)
                                <svg class="mx-auto h-12 w-12 text-pink-500" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-700">{{ $file->getClientOriginalName() }}</p>
                            @else
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <div class="mt-4 flex text-sm leading-6 text-gray-600">
                                    <span class="font-semibold text-pink-600">คลิกเพื่อเลือกไฟล์</span>
                                    <span class="pl-1">หรือลากไฟล์มาวาง</span>
                                </div>
                                <p class="text-xs leading-5 text-gray-600">XLSX, XLS, CSV สูงสุด 10MB</p>
                            @endif
                        </div>
                        <input wire:model="file" type="file" id="file" class="sr-only" accept=".xlsx,.xls,.csv">
                    </div>
                    @error('file')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Template Downloads -->
                <div class="rounded-lg bg-gray-50 p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">ดาวน์โหลดแม่แบบ</h4>
                    <div class="flex flex-wrap gap-2">
                        <a href="#" class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            <svg class="h-4 w-4 mr-1.5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            แม่แบบโรงเรียน
                        </a>
                        <a href="#" class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            <svg class="h-4 w-4 mr-1.5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            แม่แบบนโยบาย
                        </a>
                        <a href="#" class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            <svg class="h-4 w-4 mr-1.5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            แม่แบบกลุ่มเครือข่าย
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            class="w-full rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600 disabled:opacity-50 transition-colors">
                        <span wire:loading.remove>นำเข้าข้อมูล</span>
                        <span wire:loading>กำลังนำเข้า...</span>
                    </button>
                </div>
            </div>
        </form>

        <!-- Results -->
        @if(count($results) > 0)
            <div class="mt-6 rounded-lg bg-green-50 p-4">
                <h4 class="text-sm font-medium text-green-900 mb-2">ผลการนำเข้า</h4>
                <dl class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <dt class="text-green-700">ทั้งหมด</dt>
                        <dd class="text-lg font-semibold text-green-900">{{ $results['total'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-green-700">สำเร็จ</dt>
                        <dd class="text-lg font-semibold text-green-900">{{ $results['imported'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-green-700">ล้มเหลว</dt>
                        <dd class="text-lg font-semibold text-green-900">{{ $results['failed'] }}</dd>
                    </div>
                </dl>
            </div>
        @endif

        <!-- Errors -->
        @if(count($importErrors) > 0)
            <div class="mt-6 rounded-lg bg-red-50 p-4">
                <h4 class="text-sm font-medium text-red-900 mb-2">ข้อผิดพลาด</h4>
                <ul class="list-disc list-inside text-sm text-red-700 space-y-1 max-h-40 overflow-y-auto">
                    @foreach($importErrors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
