<x-guest-layout>
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-fuchsia-600 mb-2">ระบบนิเทศการศึกษา</h1>
        <p class="text-gray-500">เข้าสู่ระบบด้วยอีเมลและรหัสผ่าน</p>
    </div>

    @if(session('error'))
        <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200">
            <p class="text-sm text-red-600">{{ session('error') }}</p>
        </div>
    @endif

    <form class="space-y-5" action="{{ route('login') }}" method="POST">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">อีเมล</label>
            <input id="email" 
                   name="email" 
                   type="email" 
                   autocomplete="email" 
                   required 
                   value="{{ old('email') }}"
                   class="block w-full rounded-xl border-2 border-gray-200 py-3 px-4 text-gray-900 placeholder:text-gray-400 focus:border-fuchsia-500 focus:ring-fuchsia-500 transition-colors @error('email') border-red-300 @enderror"
                   placeholder="your@email.com">
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">รหัสผ่าน</label>
            <input id="password" 
                   name="password" 
                   type="password" 
                   autocomplete="current-password" 
                   required
                   class="block w-full rounded-xl border-2 border-gray-200 py-3 px-4 text-gray-900 placeholder:text-gray-400 focus:border-fuchsia-500 focus:ring-fuchsia-500 transition-colors"
                   placeholder="••••••••">
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember" 
                       name="remember" 
                       type="checkbox"
                       class="h-4 w-4 rounded border-gray-300 text-fuchsia-600 focus:ring-fuchsia-500">
                <label for="remember" class="ml-2 block text-sm text-gray-600">จดจำฉัน</label>
            </div>
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-gradient-to-r from-fuchsia-500 to-pink-500 px-4 py-3.5 text-sm font-semibold text-white shadow-lg hover:from-fuchsia-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-fuchsia-500 focus:ring-offset-2 transition-all">
            เข้าสู่ระบบ
        </button>
    </form>

</x-guest-layout>
