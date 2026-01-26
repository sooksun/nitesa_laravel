<x-guest-layout>
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-fuchsia-600 mb-2">ระบบนิเทศการศึกษา</h1>
        <p class="text-gray-500">เข้าสู่ระบบด้วยอีเมลและรหัสผ่าน หรือ Google</p>
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

    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-200"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="bg-white px-4 text-gray-400">หรือ</span>
        </div>
    </div>

    <a href="{{ route('auth.google') }}" 
       class="w-full flex items-center justify-center gap-3 rounded-xl border-2 border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-colors shadow-sm">
        <svg class="h-5 w-5" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        เข้าสู่ระบบด้วย Google
    </a>
</x-guest-layout>
