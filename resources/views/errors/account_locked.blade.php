<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản bị khóa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-100 dark:bg-slate-900 h-screen flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 p-8 rounded-xl shadow-2xl max-w-md w-full text-center">
        <div class="mb-6 flex justify-center">
            <div class="p-4 bg-red-100 rounded-full text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-3">Tài khoản đã bị khóa</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-8">
            Tài khoản này đã bị vô hiệu hóa hoặc Khoa/Phòng trực thuộc đã đóng cửa. <br>
            Vui lòng liên hệ Quản trị viên để biết thêm chi tiết.
        </p>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit"
                class="w-full px-6 py-3 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                OK
            </button>
        </form>
    </div>
</body>

</html>