<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $department->name }} - Lịch sử yêu cầu</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
        .excel-table { border-collapse: collapse; width: 100%; }
        .excel-table th, .excel-table td { border: 1px solid #d1d5db; padding: 8px 12px; }
        .excel-table th { background: #f3f4f6; font-weight: 600; text-align: center; }
        .category-header { background: #3b82f6 !important; color: white; font-weight: bold; text-align: left; }
        .total-row { background: #fef3c7; font-weight: bold; }
    </style>
</head>

<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
            <div class="p-6 border-b">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-600 p-2 rounded-lg text-white">
                        <span class="material-symbols-outlined">local_hospital</span>
                    </div>
                    <h2 class="font-bold text-lg">VPP Hospital</h2>
                </div>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('department.index') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <span class="material-symbols-outlined">assignment</span>
                    <span>Yêu cầu VPP</span>
                </a>
                <a href="{{ route('department.history') }}" class="flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-lg">
                    <span class="material-symbols-outlined">history</span>
                    <span>Lịch sử yêu cầu</span>
                </a>
            </nav>
            <div class="p-4 border-t">
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr($department->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate">{{ $department->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->name }}</p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full text-xs text-gray-500 hover:text-blue-600 text-left px-2 py-1">
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white border-b px-8 py-4 flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Lịch sử yêu cầu</h1>
                    <p class="text-sm text-gray-500">{{ $department->name }} - Tháng {{ $selectedMonth }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <form method="GET" action="{{ route('department.history') }}">
                        <select name="month" onchange="this.form.submit()" class="border-gray-300 rounded-lg text-sm px-4 py-2">
                            @for($i = 0; $i < 12; $i++)
                                @php
                                    $date = now()->subMonths($i);
                                    $monthValue = $date->format('m/Y');
                                @endphp
                                <option value="{{ $monthValue }}" {{ $selectedMonth == $monthValue ? 'selected' : '' }}>
                                    Tháng {{ $date->format('m/Y') }}
                                </option>
                            @endfor
                        </select>
                    </form>
                </div>
            </header>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-8">
                @if($orders->isEmpty())
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                        <span class="material-symbols-outlined text-6xl text-gray-300">inbox</span>
                        <p class="mt-4 text-gray-500">Chưa có yêu cầu nào trong tháng này</p>
                        <a href="{{ route('department.index') }}" class="mt-4 inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Tạo yêu cầu mới
                        </a>
                    </div>
                @else
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <table class="excel-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">STT</th>
                                    <th>Tên hàng</th>
                                    <th style="width: 100px;">ĐVT</th>
                                    <th style="width: 120px;">Số lượng</th>
                                    <th style="width: 130px;">Đơn giá</th>
                                    <th style="width: 150px;">Thành tiền</th>
                                    <th style="width: 150px;">Ngày tạo</th>
                                    <th style="width: 150px;">Ngày cập nhật</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $stt = 0; @endphp
                                @foreach($orders as $categoryName => $categoryOrders)
                                    <!-- Category Header -->
                                    <tr class="category-header">
                                        <td colspan="8">{{ strtoupper($categoryName) }}</td>
                                    </tr>

                                    <!-- Products -->
                                    @foreach($categoryOrders as $order)
                                        @php $stt++; @endphp
                                        <tr>
                                            <td class="text-center text-sm text-gray-600">{{ $stt }}</td>
                                            <td class="text-sm font-medium">{{ $order->product->name }}</td>
                                            <td class="text-center text-sm">{{ $order->product->unit }}</td>
                                            <td class="text-right text-sm">{{ number_format($order->quantity, 0, ',', '.') }}</td>
                                            <td class="text-right text-sm">{{ number_format($order->product->price, 0, ',', '.') }}</td>
                                            <td class="text-right text-sm font-semibold">{{ number_format($order->quantity * $order->product->price, 0, ',', '.') }}</td>
                                            <td class="text-center text-sm text-gray-600">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="text-center text-sm text-gray-600">{{ $order->updated_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach

                                <!-- Total Row -->
                                <tr class="total-row">
                                    <td colspan="5" class="text-right font-bold">TỔNG CỘNG:</td>
                                    <td class="text-right font-bold">{{ number_format($totalAmount, 0, ',', '.') }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end gap-4">
                        <a href="{{ route('department.index') }}" class="px-6 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50">
                            <span class="material-symbols-outlined text-sm inline-block align-middle">edit</span>
                            Chỉnh sửa yêu cầu
                        </a>
                        <button onclick="window.print()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <span class="material-symbols-outlined text-sm inline-block align-middle">print</span>
                            In yêu cầu
                        </button>
                    </div>
                @endif
            </div>
        </main>
    </div>
</body>

</html>
