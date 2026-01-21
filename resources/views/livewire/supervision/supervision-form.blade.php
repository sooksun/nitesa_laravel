<div class="max-w-5xl">
    <form wire:submit="save">
        <div class="space-y-8">
            <!-- Basic Info -->
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-base font-semibold leading-7 text-gray-900 mb-6">ข้อมูลการนิเทศ</h3>
                
                <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="schoolId" class="block text-sm font-medium leading-6 text-gray-900">โรงเรียน <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <select wire:model="schoolId" 
                                    id="schoolId"
                                    class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 @error('schoolId') ring-red-300 @enderror">
                                <option value="">เลือกโรงเรียน</option>
                                @foreach($this->schools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('schoolId')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-3">
                        <label for="type" class="block text-sm font-medium leading-6 text-gray-900">ประเภทการนิเทศ <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <select wire:model="type" 
                                    id="type"
                                    class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                                @foreach($supervisionTypes as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="date" class="block text-sm font-medium leading-6 text-gray-900">วันที่นิเทศ <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input wire:model="date" 
                                   type="date" 
                                   id="date"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 @error('date') ring-red-300 @enderror">
                        </div>
                        @error('date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="academicYear" class="block text-sm font-medium leading-6 text-gray-900">ปีการศึกษา</label>
                        <div class="mt-2">
                            <input wire:model="academicYear" 
                                   type="text" 
                                   id="academicYear"
                                   placeholder="2567"
                                   class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Policies -->
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-base font-semibold leading-7 text-gray-900 mb-2">นโยบายที่เกี่ยวข้อง</h3>
                <p class="text-sm text-gray-500 mb-6">เลือกนโยบายที่ใช้ในการนิเทศครั้งนี้</p>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div>
                        <label for="ministerPolicyId" class="block text-sm font-medium leading-6 text-gray-900">นโยบายกระทรวง</label>
                        <div class="mt-2">
                            <select wire:model="ministerPolicyId" 
                                    id="ministerPolicyId"
                                    class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                                <option value="">ไม่ระบุ</option>
                                @foreach($this->policies as $policy)
                                    <option value="{{ $policy->id }}">{{ $policy->code }} - {{ \Illuminate\Support\Str::limit($policy->title, 30) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="obecPolicyId" class="block text-sm font-medium leading-6 text-gray-900">นโยบาย สพฐ.</label>
                        <div class="mt-2">
                            <select wire:model="obecPolicyId" 
                                    id="obecPolicyId"
                                    class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                                <option value="">ไม่ระบุ</option>
                                @foreach($this->policies as $policy)
                                    <option value="{{ $policy->id }}">{{ $policy->code }} - {{ \Illuminate\Support\Str::limit($policy->title, 30) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="areaPolicyId" class="block text-sm font-medium leading-6 text-gray-900">นโยบายเขตพื้นที่</label>
                        <div class="mt-2">
                            <select wire:model="areaPolicyId" 
                                    id="areaPolicyId"
                                    class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
                                <option value="">ไม่ระบุ</option>
                                @foreach($this->policies as $policy)
                                    <option value="{{ $policy->id }}">{{ $policy->code }} - {{ \Illuminate\Support\Str::limit($policy->title, 30) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Indicators -->
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-base font-semibold leading-7 text-gray-900">ตัวชี้วัดการนิเทศ</h3>
                        <p class="text-sm text-gray-500">ประเมินระดับตัวชี้วัดแต่ละด้าน</p>
                    </div>
                    <button type="button" 
                            wire:click="addIndicator"
                            class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        เพิ่มตัวชี้วัด
                    </button>
                </div>
                
                <div class="space-y-4">
                    @foreach($indicators as $index => $indicator)
                        <div class="rounded-lg border border-gray-200 p-4" wire:key="indicator-{{ $index }}">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                                <div class="sm:col-span-5">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อตัวชี้วัด</label>
                                    <input wire:model="indicators.{{ $index }}.name" 
                                           type="text"
                                           class="block w-full rounded-lg border-0 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm"
                                           placeholder="ชื่อตัวชี้วัด">
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ระดับ</label>
                                    <select wire:model="indicators.{{ $index }}.level"
                                            class="block w-full rounded-lg border-0 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm">
                                        @foreach($this->indicatorLevels as $level)
                                            <option value="{{ $level->value }}">{{ $level->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">หมายเหตุ</label>
                                    <input wire:model="indicators.{{ $index }}.comment" 
                                           type="text"
                                           class="block w-full rounded-lg border-0 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm"
                                           placeholder="หมายเหตุ">
                                </div>
                                <div class="sm:col-span-1 flex items-end justify-end">
                                    @if(count($indicators) > 1)
                                        <button type="button" 
                                                wire:click="removeIndicator({{ $index }})"
                                                class="p-2 text-gray-400 hover:text-red-600 transition-colors">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Summary & Suggestions -->
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-base font-semibold leading-7 text-gray-900 mb-6">สรุปผลและข้อเสนอแนะ</h3>
                
                <div class="space-y-6">
                    <div>
                        <label for="summary" class="block text-sm font-medium leading-6 text-gray-900">สรุปผลการนิเทศ <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <textarea wire:model="summary" 
                                      id="summary"
                                      rows="4"
                                      class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 @error('summary') ring-red-300 @enderror"
                                      placeholder="สรุปผลการนิเทศติดตาม..."></textarea>
                        </div>
                        @error('summary')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="suggestions" class="block text-sm font-medium leading-6 text-gray-900">ข้อเสนอแนะ <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <textarea wire:model="suggestions" 
                                      id="suggestions"
                                      rows="4"
                                      class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 @error('suggestions') ring-red-300 @enderror"
                                      placeholder="ข้อเสนอแนะสำหรับโรงเรียน..."></textarea>
                        </div>
                        @error('suggestions')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Attachments -->
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-base font-semibold leading-7 text-gray-900 mb-2">ไฟล์แนบ</h3>
                <p class="text-sm text-gray-500 mb-6">แนบไฟล์หลักฐานการนิเทศ (รูปภาพ, PDF, เอกสาร)</p>
                
                <div>
                    <label class="flex justify-center rounded-lg border border-dashed border-gray-300 px-6 py-10 cursor-pointer hover:border-pink-400 transition-colors">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                            </svg>
                            <div class="mt-4 flex text-sm leading-6 text-gray-600">
                                <span class="font-semibold text-pink-600">คลิกเพื่อเลือกไฟล์</span>
                                <span class="pl-1">หรือลากไฟล์มาวาง</span>
                            </div>
                            <p class="text-xs leading-5 text-gray-600">PNG, JPG, PDF สูงสุด 10MB</p>
                        </div>
                        <input wire:model="uploads" type="file" class="sr-only" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                    </label>

                    @if(count($uploads) > 0)
                        <div class="mt-4 space-y-2">
                            @foreach($uploads as $index => $file)
                                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                        <span class="text-sm text-gray-700">{{ $file->getClientOriginalName() }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @error('uploads.*')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <a href="{{ route('supervisions.index') }}" 
                   class="rounded-lg px-4 py-2.5 text-sm font-semibold text-gray-900 hover:bg-gray-100 transition-colors">
                    ยกเลิก
                </a>
                <div class="flex items-center gap-3">
                    <button type="submit" 
                            class="rounded-lg bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-200 transition-colors">
                        บันทึกเป็นร่าง
                    </button>
                    <button type="button"
                            wire:click="saveAndSubmit"
                            class="rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 transition-colors">
                        บันทึกและส่ง
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
