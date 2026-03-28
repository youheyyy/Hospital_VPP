<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng Nhập - Hệ Thống Quản Lý Văn Phòng Phẩm</title>

    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" />

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    <!-- Tailwind Config -->
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#13c8ec",
                        "background-light": "#f6f8f8",
                        "background-dark": "#101f22",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>

    <!-- Custom Styles -->
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .dark .glass-card {
            background: rgba(16, 31, 34, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>

<body
    class="font-display bg-background-light dark:bg-background-dark min-h-screen flex items-center justify-center overflow-hidden">
    <!-- Full-Screen Background with Gradient and Abstract Illustration -->
    <div
        class="fixed inset-0 z-0 bg-gradient-to-br from-[#e0f7fa] via-[#f8fbfc] to-[#ffffff] dark:from-[#0a1618] dark:to-[#101f22]">
        <!-- Abstract Medical Illustration -->
        <div
            class="absolute top-1/2 left-20 -translate-y-1/2 w-[500px] h-[500px] opacity-20 pointer-events-none hidden lg:block">
            <div class="relative w-full h-full">
                <!-- Stethoscope-like curve -->
                <svg class="w-full h-full text-primary" viewbox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M44.7,-76.4C58.1,-69.2,69.2,-58.1,76.9,-44.9C84.7,-31.7,89.1,-16.4,88.2,-1.5C87.4,13.4,81.3,27.9,72.4,40.1C63.5,52.3,51.8,62.2,38.6,69.5C25.3,76.8,10.6,81.6,-3.6,87.8C-17.8,94.1,-31.4,101.8,-43.3,97.7C-55.3,93.6,-65.7,77.7,-73.4,62.3C-81.1,46.9,-86.2,32,-88.7,16.8C-91.1,1.5,-90.9,-14.1,-84.9,-27.7C-79,-41.2,-67.2,-52.7,-54.1,-60.1C-40.9,-67.5,-26.4,-70.8,-11.4,-74.2C3.6,-77.6,18.5,-81.1,33.3,-80.7C48,-80.2,59.2,-75.7,44.7,-76.4Z"
                        fill="currentColor" transform="translate(100 100)"></path>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[200px] text-primary/40">medical_services</span>
                </div>
            </div>
        </div>
        <!-- Right side accent -->
        <div class="absolute -bottom-20 -right-20 w-[400px] h-[400px] bg-primary/10 rounded-full blur-[100px]"></div>
    </div>

    <!-- Main Content Container -->
    <div class="relative z-10 w-full max-w-[1400px] px-8 lg:px-20 grid lg:grid-cols-2 items-center min-h-screen">
        <!-- Left Column: Branding/Welcome Text (Desktop Only) -->
        <div class="hidden lg:flex flex-col justify-center gap-8 pr-10">
            <div class="flex items-center gap-6">
                <img src="{{ asset('images/logo-tmmc.png') }}" alt="TMMC Healthcare Logo"
                    class="h-32 w-auto object-contain flex-shrink-0" />
                <div>
                    <h1
                        class="text-xl font-extrabold text-[#005ba3] dark:text-blue-400 uppercase leading-snug tracking-wide">
                        BỆNH VIỆN ĐA KHOA <br /> TÂM TRÍ CAO LÃNH
                    </h1>
                    <p class="text-base font-bold text-gray-500 dark:text-gray-400 mt-1">TMMC HEALTHCARE</p>
                </div>
            </div>
            <div class="space-y-4">
                <h2 class="text-5xl font-bold text-[#0d191b] dark:text-white leading-tight">
                    Hệ Thống Quản Lý <br />
                    <span class="text-primary">Văn Phòng Phẩm</span>
                </h2>
                <p class="text-lg text-[#4c8d9a] dark:text-[#a1cbd3] max-w-md">
                    Giải pháp quản lý vật tư y tế và văn phòng phẩm chuyên nghiệp, hiện đại cho đội ngũ y bác sĩ.
                </p>
            </div>
        </div>

        <!-- Right Column: Login Card -->
        <div class="flex justify-center lg:justify-end items-center">
            <div
                class="layout-content-container flex flex-col w-full max-w-[480px] glass-card rounded-xl p-8 shadow-2xl">
                <!-- Mobile Header -->
                <div class="flex items-center gap-4 mb-8 lg:hidden">
                    <img src="{{ asset('images/logo-tmmc.png') }}" alt="TMMC Healthcare Logo"
                        class="h-20 w-auto object-contain flex-shrink-0" />
                    <div class="text-left">
                        <h1 class="text-base font-extrabold text-[#005ba3] dark:text-blue-400 uppercase leading-tight">
                            BỆNH VIỆN ĐA KHOA <br /> TÂM TRÍ CAO LÃNH</h1>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 mt-1">TMMC HEALTHCARE</p>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-2xl font-bold text-[#0d191b] dark:text-white mb-2">Đăng Nhập</h3>
                    <p class="text-[#4c8d9a] dark:text-[#a1cbd3] text-sm leading-normal">
                        Vui lòng nhập thông tin tài khoản được cấp để truy cập hệ thống.
                    </p>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <ul class="text-sm text-red-600 dark:text-red-400 space-y-1">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if (session('status'))
                <div
                    class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <p class="text-sm text-green-600 dark:text-green-400">{{ session('status') }}</p>
                </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login.post') }}" class="flex flex-col gap-5">
                    @csrf

                    <!-- TextField: Email -->
                    <div class="flex flex-col gap-2">
                        <label class="text-[#0d191b] dark:text-white text-sm font-semibold" for="email">Email</label>
                        <div class="flex items-stretch rounded-lg shadow-sm">
                            <input id="email" name="email"
                                class="form-input flex-1 h-14 border border-[#cfe3e7] dark:border-white/10 bg-white/50 dark:bg-black/20 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary rounded-l-lg p-4 text-[#0d191b] dark:text-white placeholder:text-[#4c8d9a]/50 text-base"
                                placeholder="Nhập email của bạn" type="email" value="{{ old('email') }}" required
                                autofocus />
                            <div
                                class="flex items-center px-4 bg-white/50 dark:bg-black/20 border border-l-0 border-[#cfe3e7] dark:border-white/10 rounded-r-lg text-[#4c8d9a]">
                                <span class="material-symbols-outlined">email</span>
                            </div>
                        </div>
                    </div>

                    <!-- TextField: Password -->
                    <div class="flex flex-col gap-2">
                        <div class="flex justify-between items-center">
                            <label class="text-[#0d191b] dark:text-white text-sm font-semibold" for="password">Mật
                                khẩu</label>
                            <a class="text-primary text-xs font-medium hover:underline" href="#">Quên mật khẩu?</a>
                        </div>
                        <div class="flex items-stretch rounded-lg shadow-sm">
                            <input id="password" name="password"
                                class="form-input flex-1 h-14 border border-[#cfe3e7] dark:border-white/10 bg-white/50 dark:bg-black/20 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary rounded-l-lg p-4 text-[#0d191b] dark:text-white placeholder:text-[#4c8d9a]/50 text-base"
                                placeholder="Nhập mật khẩu của bạn" type="password" required />
                            <div
                                class="flex items-center px-4 bg-white/50 dark:bg-black/20 border border-l-0 border-[#cfe3e7] dark:border-white/10 rounded-r-lg text-[#4c8d9a]">
                                <span class="material-symbols-outlined">lock</span>
                            </div>
                        </div>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="flex items-center gap-2 py-1">
                        <input class="w-4 h-4 rounded border-[#cfe3e7] text-primary focus:ring-primary" id="remember"
                            name="remember" type="checkbox" />
                        <label class="text-sm text-[#4c8d9a] dark:text-[#a1cbd3] cursor-pointer" for="remember">
                            Ghi nhớ đăng nhập
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button
                            class="w-full flex items-center justify-center rounded-lg h-14 bg-primary text-[#0d191b] text-base font-bold shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all duration-200"
                            type="submit">
                            <span>ĐĂNG NHẬP</span>
                            <span class="material-symbols-outlined ml-2 text-xl">login</span>
                        </button>
                    </div>
                </form>

                <!-- Footer info in card -->
                <!-- <div class="mt-10 pt-6 border-t border-[#cfe3e7]/50 dark:border-white/5 flex flex-col gap-4">
                    <div class="flex justify-between items-center text-xs text-[#4c8d9a] dark:text-[#a1cbd3]">
                        <span>Liên hệ hỗ trợ: <b>1900 1234</b></span>
                        <span>Phiên bản 3.0.1</span>
                    </div>
                </div> -->
            </div>
        </div>
    </div>

    <!-- Page Footer -->
    <div class="fixed bottom-6 w-full text-center z-10 pointer-events-none">
        <p class="text-[#4c8d9a] dark:text-white/40 text-xs font-medium uppercase tracking-widest">
            © 2024 IT Department - General Hospital Management
        </p>
    </div>
</body>

</html>