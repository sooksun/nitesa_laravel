@props(['options' => [], 'placeholder' => 'ไม่ระบุ', 'id' => null, 'searchThreshold' => 8])

<div x-data="multiSelect(@js($options))"
     x-modelable="value"
     {{ $attributes->whereStartsWith('wire:model') }}
     @keydown.escape="close()"
     @click.outside="close()"
     class="relative">
    <div wire:ignore>
        <button type="button"
                @if($id) id="{{ $id }}" @endif
                @click="toggleOpen()"
                aria-haspopup="listbox"
                :aria-expanded="open.toString()"
                class="flex w-full items-center justify-between gap-2 rounded-lg border-0 bg-white py-2.5 pl-3 pr-2 text-left shadow-sm ring-1 ring-inset ring-gray-300 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6">
            <span x-text="selected.length ? summary : @js($placeholder)"
                  :class="selected.length ? 'text-gray-900' : 'text-gray-500'"
                  class="truncate">{{ $placeholder }}</span>
            <svg class="h-5 w-5 shrink-0 text-gray-400 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </button>

        <div x-show="open"
             x-cloak
             x-transition.opacity
             class="absolute left-0 z-40 mt-1 w-full min-w-[16rem] rounded-xl bg-white shadow-lg ring-1 ring-gray-900/10">
            @if(count($options) > $searchThreshold)
                <div class="border-b border-gray-100 p-2">
                    <input type="text"
                           x-ref="search"
                           x-model="search"
                           @keydown.enter.prevent
                           placeholder="ค้นหา"
                           aria-label="ค้นหา"
                           class="block w-full rounded-lg border-0 py-1.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600">
                </div>
            @endif

            <ul role="listbox" aria-multiselectable="true" class="max-h-64 overflow-y-auto py-1">
                <template x-for="option in filtered" :key="option.id">
                    <li role="option" :aria-selected="isSelected(option.id).toString()">
                        <label class="flex cursor-pointer items-start gap-2.5 px-3 py-2 text-sm hover:bg-pink-50"
                               :class="isSelected(option.id) && 'bg-pink-50/60'">
                            <input type="checkbox"
                                   :checked="isSelected(option.id)"
                                   @change="toggle(option.id)"
                                   class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-pink-600 focus:ring-pink-600">
                            <span class="min-w-0 text-gray-900">
                                <span x-show="option.code" x-text="option.code" class="font-medium text-gray-500"></span>
                                <span x-text="option.label"></span>
                            </span>
                        </label>
                    </li>
                </template>
                <li x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-500">ไม่พบรายการ</li>
            </ul>

            <div class="flex items-center justify-between border-t border-gray-100 px-3 py-2 text-sm">
                <button type="button" @click="clear()" class="font-medium text-gray-500 hover:text-gray-700">ล้างทั้งหมด</button>
                <button type="button" @click="close()" class="font-medium text-pink-600 hover:text-pink-500">เสร็จ</button>
            </div>
        </div>

        <ul x-show="selected.length" x-cloak class="mt-2 space-y-1.5">
            <template x-for="option in selected" :key="option.id">
                <li class="flex items-start gap-2 rounded-lg bg-pink-50 py-1.5 pl-2.5 pr-1.5 text-sm text-pink-900 ring-1 ring-inset ring-pink-200">
                    <span class="min-w-0 flex-1">
                        <span x-show="option.code" x-text="option.code" class="font-medium"></span>
                        <span x-text="option.label"></span>
                    </span>
                    <button type="button"
                            @click="remove(option.id)"
                            :aria-label="`นำ ${option.code ?? option.label} ออก`"
                            class="shrink-0 rounded p-0.5 text-pink-500 hover:bg-pink-100 hover:text-pink-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </li>
            </template>
        </ul>
    </div>
</div>
