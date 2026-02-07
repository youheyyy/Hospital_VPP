<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tổng quan Quản lý Văn phòng phẩm</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#1e40af",
                        secondary: "#3b82f6",
                        "background-light": "#f1f5f9",
                    },
                    fontFamily: {
                        sans: ["Inter", "sans-serif"],
                    },
                },
            },
        };
    </script>
    <style type="text/tailwindcss">
        @layer base {
            body { @apply bg-background-light text-slate-900; font-family: 'Inter', sans-serif; }
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .chart-bar {
            transition: height 0.3s ease;
        }
    </style>
</head>

<body class="bg-slate-50">
    <div class="flex h-screen overflow-hidden">
        <aside class="w-72 flex-shrink-0 bg-white border-r border-slate-200 flex flex-col">
            <div class="p-6">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-tmmc.png') }}" alt="Logo" class="h-12 w-auto">
                    <div class="flex flex-col">
                        <h2 class="text-primary text-xl font-black leading-none tracking-tighter">TÂM TRÍ</h2>
                        <span class="text-[10px] font-bold text-slate-800 uppercase tracking-widest mt-1">CAO
                            LÃNH</span>
                        <span class="text-[9px] font-medium text-slate-500 leading-none mt-0.5">Quản lý VPP</span>
                    </div>
                </div>
            </div>
            <nav class="flex-1 px-6 space-y-2">
                <a class="flex items-center gap-4 px-4 py-3 text-sm font-semibold rounded-xl bg-blue-50 text-primary border border-blue-100"
                    href="{{ route('admin.dashboard') }}">
                    <span class="material-symbols-outlined">dashboard</span>
                    Tổng quan
                </a>
                <a class="flex items-center gap-4 px-4 py-3 text-sm font-medium rounded-xl text-slate-600 hover:bg-slate-50 transition-all"
                    href="{{ route('admin.consolidated') }}">
                    <span class="material-symbols-outlined">summarize</span>
                    Tổng hợp yêu cầu
                </a>
            </nav>
            <div class="p-6 mt-auto">
                <div class="bg-slate-900 rounded-2xl p-5 text-white">
                    <p class="text-xs text-slate-400 font-medium mb-1">Phiên bản Admin</p>
                    <p class="text-sm font-bold">Hệ thống VPP v2.0</p>
                    <div class="mt-4 flex items-center gap-3">
                        <div
                            class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center text-sm font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-xs font-bold leading-none">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-slate-400">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit"
                            class="w-full text-xs text-slate-400 hover:text-white transition-colors text-left">
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        <main class="flex-1 overflow-y-auto">
            <header
                class="bg-white/80 backdrop-blur-md sticky top-0 z-20 border-b border-slate-200 p-6 px-10 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Tổng quan Quản lý Văn phòng phẩm</h1>
                    <p class="text-sm text-slate-500">Chào mừng trở lại, hôm nay là {{ now()->format('d/m/Y') }}</p>
                </div>
            </header>
            <div class="p-10 space-y-8 max-w-7xl mx-auto">
                <section class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div
                        class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-6">
                            <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl">
                                <span class="material-symbols-outlined text-2xl">assignment_late</span>
                            </div>
                            <span
                                class="text-xs font-bold text-green-500 bg-green-50 px-2.5 py-1 rounded-full">+12%</span>
                        </div>
                        <p class="text-slate-500 text-sm font-semibold uppercase tracking-wider">Số lượng yêu cầu</p>
                        <h3 class="text-4xl font-black mt-2 text-slate-800">{{ $totalRequests }}</h3>
                        <p class="text-slate-400 text-xs mt-4 italic">Trong tháng hiện tại</p>
                    </div>
                    <div
                        class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-6">
                            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl">
                                <span class="material-symbols-outlined text-2xl">payments</span>
                            </div>
                            <span class="text-xs font-bold text-red-500 bg-red-50 px-2.5 py-1 rounded-full">+5.4%</span>
                        </div>
                        <p class="text-slate-500 text-sm font-semibold uppercase tracking-wider">Tổng chi phí</p>
                        <h3 class="text-4xl font-black mt-2 text-slate-800">{{ number_format($totalCost) }} <span
                                class="text-lg font-bold">₫</span></h3>
                        <p class="text-slate-400 text-xs mt-4 italic">Dự kiến chi tháng này</p>
                    </div>
                    <div
                        class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-6">
                            <div class="p-3 bg-amber-50 text-amber-600 rounded-2xl">
                                <span class="material-symbols-outlined text-2xl">inventory</span>
                            </div>
                            <span class="text-xs font-bold text-blue-500 bg-blue-50 px-2.5 py-1 rounded-full">Ổn
                                định</span>
                        </div>
                        <p class="text-slate-500 text-sm font-semibold uppercase tracking-wider">Vật phẩm yêu cầu nhất
                        </p>
                        <h3 class="text-2xl font-black mt-2 text-slate-800">{{ $topProductName }}</h3>
                        <p class="text-slate-400 text-xs mt-4 italic">Đã yêu cầu {{ $topProductQuantity }} đơn vị</p>
                    </div>
                </section>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="p-8 flex justify-between items-center border-b border-slate-50">
                        <h3 class="text-lg font-bold text-slate-800">Yêu cầu gần đây</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th
                                        class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        Khoa phòng</th>
                                    <th
                                        class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        Sản phẩm</th>
                                    <th
                                        class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        Tháng</th>
                                    <th
                                        class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">
                                        Số lượng</th>
                                    <th
                                        class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        Ngày tạo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($recentRequests as $request)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-8 py-4">
                                            <div class="text-sm font-medium text-slate-600">{{ $request->department->name }}
                                            </div>
                                        </td>
                                        <td class="px-8 py-4 font-bold text-sm text-slate-700">{{ $request->product->name }}
                                        </td>
                                        <td class="px-8 py-4 text-sm text-slate-500">{{ $request->month }}</td>
                                        <td class="px-8 py-4 text-sm font-bold text-slate-700 text-right">
                                            {{ $request->quantity }} {{ $request->product->unit }}</td>
                                        <td class="px-8 py-4 text-sm text-slate-500">
                                            {{ $request->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-8 py-8 text-center text-slate-400">Chưa có yêu cầu nào
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>

</html>