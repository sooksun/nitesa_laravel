@props(['placeholder' => 'วว/ดด/ปปปป (พ.ศ.)'])

<div x-data="thaiDatePicker"
     x-modelable="value"
     {{ $attributes->whereStartsWith('wire:model') }}
     @keydown.escape="close()"
     @click.outside="close()"
     class="relative">
    <input type="text"
           inputmode="numeric"
           autocomplete="off"
           x-model="text"
           @change="commitText()"
           @keydown.enter.prevent="commitText()"
           placeholder="{{ $placeholder }}"
           {{ $attributes->whereDoesntStartWith('wire:model') }}>

    <button type="button"
            @click="toggle()"
            aria-label="เปิดปฏิทิน"
            class="absolute inset-y-0 right-0 flex items-center px-2.5 text-gray-400 hover:text-pink-600">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
        </svg>
    </button>

    <div x-show="open"
         x-cloak
         x-transition.opacity
         wire:ignore
         class="absolute left-0 z-50 mt-1 w-72 rounded-xl bg-white p-3 shadow-lg ring-1 ring-gray-900/10">
        <div class="mb-2 flex items-center gap-1">
            <button type="button" @click="shiftMonth(-1)" aria-label="เดือนก่อนหน้า"
                    class="rounded-lg p-1.5 text-gray-500 hover:bg-pink-50 hover:text-pink-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </button>
            <select x-model.number="viewMonth" aria-label="เดือน"
                    class="min-w-0 flex-1 rounded-lg border-gray-300 py-1 text-sm focus:border-pink-500 focus:ring-pink-500">
                <template x-for="(name, index) in months" :key="index">
                    <option :value="index" x-text="name" :selected="index === viewMonth"></option>
                </template>
            </select>
            <select x-model.number="viewYear" aria-label="ปี พ.ศ."
                    class="w-24 rounded-lg border-gray-300 py-1 text-sm focus:border-pink-500 focus:ring-pink-500">
                <template x-for="year in years" :key="year.value">
                    <option :value="year.value" x-text="year.label" :selected="year.value === viewYear"></option>
                </template>
            </select>
            <button type="button" @click="shiftMonth(1)" aria-label="เดือนถัดไป"
                    class="rounded-lg p-1.5 text-gray-500 hover:bg-pink-50 hover:text-pink-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-7 text-center text-xs font-medium text-gray-500">
            <template x-for="weekday in weekdays" :key="weekday">
                <span class="py-1" x-text="weekday"></span>
            </template>
        </div>

        <div class="grid grid-cols-7 gap-y-0.5 text-center text-sm">
            <template x-for="cell in cells" :key="cell.key">
                <div>
                    <template x-if="cell.day">
                        <button type="button"
                                @click="select(cell.iso)"
                                x-text="cell.day"
                                :class="cell.isSelected
                                    ? 'bg-pink-600 font-semibold text-white'
                                    : (cell.isToday ? 'font-semibold text-pink-600 ring-1 ring-inset ring-pink-300 hover:bg-pink-50' : 'text-gray-700 hover:bg-pink-50')"
                                class="mx-auto flex h-8 w-8 items-center justify-center rounded-full"></button>
                    </template>
                </div>
            </template>
        </div>

        <div class="mt-2 flex items-center justify-between border-t border-gray-100 pt-2 text-sm">
            <button type="button" @click="clear()" class="font-medium text-gray-500 hover:text-gray-700">ล้าง</button>
            <button type="button" @click="selectToday()" class="font-medium text-pink-600 hover:text-pink-500">วันนี้</button>
        </div>
    </div>
</div>
