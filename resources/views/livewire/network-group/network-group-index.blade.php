<div>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">กลุ่มเครือข่าย</h2>
            <p class="mt-1 text-sm text-gray-500">จัดการกลุ่มเครือข่ายสถานศึกษา</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" wire:click="create"
                    class="inline-flex items-center rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 transition-colors">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                เพิ่มกลุ่มเครือข่าย
            </button>
        </div>
    </div>

    @if($showForm)
        <form wire:submit="save" class="mb-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
            <h3 class="text-base font-semibold text-gray-900 mb-4">{{ $editingId ? 'แก้ไขกลุ่มเครือข่าย' : 'เพิ่มกลุ่มเครือข่าย' }}</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="ng-code" class="block text-sm font-medium text-gray-900">รหัส <span class="text-red-500">*</span></label>
                    <input wire:model="code" id="ng-code" type="text" maxlength="50"
                           class="mt-1 block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm">
                    @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="ng-name" class="block text-sm font-medium text-gray-900">ชื่อกลุ่มเครือข่าย <span class="text-red-500">*</span></label>
                    <input wire:model="name" id="ng-name" type="text" maxlength="255"
                           class="mt-1 block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-3">
                    <label for="ng-description" class="block text-sm font-medium text-gray-900">รายละเอียด</label>
                    <textarea wire:model="description" id="ng-description" rows="2" maxlength="1000"
                              class="mt-1 block w-full rounded-lg border-0 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm"></textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-3">
                <button type="button" wire:click="cancel" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">ยกเลิก</button>
                <button type="submit" class="rounded-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white hover:bg-pink-500">บันทึก</button>
            </div>
        </form>
    @endif

    <div class="mb-6">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="ค้นหารหัสหรือชื่อกลุ่มเครือข่าย..."
               class="block w-full sm:max-w-sm rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm">
    </div>

    <div class="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">รหัส</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">ชื่อกลุ่มเครือข่าย</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">รายละเอียด</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">จำนวนโรงเรียน</th>
                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">จัดการ</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($groups as $group)
                    <tr wire:key="ng-{{ $group->id }}">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $group->code }}</td>
                        <td class="px-3 py-4 text-sm text-gray-900">{{ $group->name }}</td>
                        <td class="px-3 py-4 text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($group->description, 80) ?: '-' }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $group->schools_count }}</td>
                        <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <div class="flex items-center justify-end gap-3">
                                <button type="button" wire:click="edit('{{ $group->id }}')" class="text-gray-500 hover:text-blue-600">แก้ไข</button>
                                <button type="button" wire:click="confirmDelete('{{ $group->id }}')" class="text-gray-500 hover:text-red-600">ลบ</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-sm text-gray-500">ไม่พบกลุ่มเครือข่าย</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $groups->links() }}
    </div>
</div>
