<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Không tìm thấy trang</title>
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
            <div class="absolute inset-0 bg-blue-100 rounded-full w-32 h-32 mx-auto blur-xl opacity-50"></div>
            <svg class="w-32 h-32 mx-auto text-blue-600 relative z-10" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="text-6xl font-black text-slate-900 mb-2">404</h1>
        <h2 class="text-2xl font-bold text-slate-800 mb-4">Không tìm thấy trang</h2>
        <p class="text-slate-500 mb-8 leading-relaxed">Trang bạn đang tìm kiếm có thể đã bị xóa, đổi tên hoặc tạm thời
            không khả dụng.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ url('/') }}"
                class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-blue-600/20">
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