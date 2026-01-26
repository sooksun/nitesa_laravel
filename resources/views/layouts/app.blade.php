<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'NITESA' }} - ระบบนิเทศ ติดตาม และประเมินผลการศึกษา</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sarabun:400,500,600,700|prompt:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <style>
        body {
            font-family: 'Sarabun', 'Prompt', sans-serif;
        }
        
        /* Top Header with Pink Wave Background */
        .top-header {
            background: linear-gradient(135deg, #fff 0%, #fce7f3 30%, #fbcfe8 60%, #f9a8d4 100%);
            position: relative;
            overflow: hidden;
            height: 102px;
        }
        
        .top-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 60%;
            height: 100%;
            background: url('{{ asset('Purple-Blue-Neon-Far-Future-Utopia-Desktop-Wallpaper.jpg') }}') right center;
            background-size: cover;
            opacity: 0.8;
            mask-image: linear-gradient(to left, black 50%, transparent 100%);
            -webkit-mask-image: linear-gradient(to left, black 50%, transparent 100%);
        }
        
        /* Black Mourning Ribbon */
        .black-ribbon {
            position: absolute;
            top: 0;
            left: 0;
            width: 84px;
            height: 84px;
            z-index: 100;
        }
        
        /* Navigation Button Styles */
        .nav-btn {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .nav-btn-active {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        
        .nav-btn-inactive {
            color: #374151;
            background: transparent;
        }
        
        .nav-btn-inactive:hover {
            background: rgba(255, 255, 255, 0.8);
            color: #db2777;
        }
        
        /* Sidebar Styles */
        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .sidebar-item-active {
            background: linear-gradient(135deg, #ec4899, #db2777);
            color: white;
            box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);
        }
        
        .sidebar-item-inactive {
            color: #4b5563;
        }
        
        .sidebar-item-inactive:hover {
            background: #fdf2f8;
            color: #db2777;
        }
        
        /* Chart container */
        .chart-container {
            position: relative;
            height: 280px;
        }
        
        /* Card hover effects */
        .stat-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        /* Sidebar positioning - ต้องอยู่ใต้ top header */
        .desktop-sidebar {
            top: 102px !important;
            height: calc(100vh - 72px) !important;
        }
        
        /* Override h-14 to be larger for logo */
        .h-14 {
            height: calc(var(--spacing) * 20) !important;
        }
    </style>
</head>
<body class="h-full antialiased bg-gray-50" x-data="{ sidebarOpen: false }">
    <div class="min-h-full">
        <!-- Top Header Banner - ขยาย 20% -->
        <header class="top-header sticky top-0 z-50 shadow-md">
            <!-- Black Ribbon - ขยาย 20% -->
            <img src="{{ asset('black-ribbon.png') }}" alt="Mourning Ribbon" class="black-ribbon">
            
            <div class="relative z-10 flex items-center justify-between px-4 py-2 lg:px-8">
                <!-- Left: Empty space for layout balance -->
                <div class="flex items-center gap-4 ml-16">
                </div>
                
                
                <!-- Right: Motto + Logout -->
                <div class="flex items-center gap-4">
                    <img src="{{ asset('motto.png') }}" alt="Motto" class="h-10 lg:h-12 w-auto hidden lg:block drop-shadow-sm">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                class="flex items-center gap-2 text-sm text-gray-600 hover:text-pink-600 transition-colors bg-white/50 backdrop-blur-sm px-3 py-2 rounded-lg">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0110.5 3h6a2.25 2.25 0 012.25 2.25v13.5A2.25 2.25 0 0116.5 21h-6a2.25 2.25 0 01-2.25-2.25V15m-3 0l-3-3m0 0l3-3m-3 3H15" />
                            </svg>
                            <span class="hidden sm:inline">ออกจากระบบ</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Mobile Menu Button -->
        <div class="lg:hidden fixed bottom-4 left-4 z-50">
            <button type="button" 
                    @click="sidebarOpen = true"
                    class="flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-r from-pink-500 to-fuchsia-500 text-white shadow-lg hover:shadow-xl transition-shadow">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
        </div>

        <!-- Sidebar for mobile -->
        <div x-show="sidebarOpen" 
             class="relative z-50 lg:hidden" 
             x-cloak
             role="dialog" 
             aria-modal="true">
            <div x-show="sidebarOpen"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"
                 @click="sidebarOpen = false"></div>

            <div class="fixed inset-0 flex">
                <div x-show="sidebarOpen"
                     x-transition:enter="transition ease-in-out duration-300 transform"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in-out duration-300 transform"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     class="relative mr-16 flex w-full max-w-xs flex-1">
                    <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                        <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                            <span class="sr-only">Close sidebar</span>
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    @include('layouts.partials.sidebar')
                </div>
            </div>
        </div>

        <!-- Static sidebar for desktop -->
        <div class="desktop-sidebar hidden lg:fixed lg:inset-y-0 lg:z-40 lg:flex lg:w-56 lg:flex-col">
            @include('layouts.partials.sidebar')
        </div>

        <!-- Main content -->
        <div class="lg:pl-56">
            <!-- Page content -->
            <main class="min-h-screen py-6">
                <div class="px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                    {{-- Flash messages are now handled by SweetAlert2 in the footer script --}}
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <!-- Cookie Consent -->
    @include('components.cookie-consent')

    @livewireScripts
    
    <!-- SweetAlert2 for Flash Messages -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: @json(session('success')),
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#10b981',
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif
            
            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: @json(session('error')),
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#ef4444'
                });
            @endif
            
            @if(session('warning'))
                Swal.fire({
                    icon: 'warning',
                    title: 'คำเตือน!',
                    text: @json(session('warning')),
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#f59e0b'
                });
            @endif
            
            @if(session('info'))
                Swal.fire({
                    icon: 'info',
                    title: 'แจ้งเตือน',
                    text: @json(session('info')),
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#3b82f6'
                });
            @endif
        });
    </script>
    
    @stack('scripts')
</body>
</html>
