<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Không có quyền truy cập</title>
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 flex items-center justify-center h-screen overflow-hidden">
    <div class="text-center max-w-lg px-6">
        <div class="mb-8 relative">
            <div class="absolute inset-0 bg-red-100 rounded-full w-32 h-32 mx-auto blur-xl opacity-50"></div>
            <svg class="w-32 h-32 mx-auto text-red-500 relative z-10" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h1 class="text-6xl font-black text-slate-900 mb-2">403</h1>
        <h2 class="text-2xl font-bold text-slate-800 mb-4">Truy cập bị từ chối</h2>
        <p class="text-slate-500 mb-8 leading-relaxed">Xin lỗi, bạn không có quyền truy cập vào trang này. Vui lòng kiểm
            tra lại tài khoản hoặc liên hệ quản trị viên.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ url('/') }}"
                class="px-8 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl transition-all shadow-lg shadow-slate-900/20">
                Về trang chủ
            </a>
            <button onclick="history.back()"
                class="px-8 py-3 bg-white hover:bg-slate-50 text-slate-700 font-bold border border-slate-200 rounded-xl transition-all">
                Quay lại
            </button>
        </div>
    </div>
</body>

</html>