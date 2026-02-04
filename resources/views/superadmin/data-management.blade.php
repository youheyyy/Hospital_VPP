<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quản lý dữ liệu - SuperAdmin</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
            <div class="p-6 border-b">
                <div class="flex items-center gap-3">
                    <div class="bg-purple-600 p-2 rounded-lg text-white">
                        <span class="material-symbols-outlined">admin_panel_settings</span>
                    </div>
                    <h2 class="font-bold text-lg">SuperAdmin</h2>
                </div>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('superadmin.users') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <span class="material-symbols-outlined">group</span>
                    <span>Quản lý người dùng</span>
                </a>
                <a href="{{ route('superadmin.data-management') }}" class="flex items-center gap-3 px-4 py-3 bg-purple-600 text-white rounded-lg">
                    <span class="material-symbols-outlined">database</span>
                    <span>Quản lý dữ liệu</span>
                </a>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>
            </nav>
            <div class="p-4 border-t">
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full text-xs text-gray-500 hover:text-purple-600 text-left px-2 py-1">
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <!-- Header -->
            <header class="bg-white border-b px-8 py-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Quản lý dữ liệu</h1>
                    <p class="text-sm text-gray-500">Import/Export dữ liệu và Backup database</p>
                </div>
            </header>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mx-8 mt-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mx-8 mt-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Content -->
            <div class="p-8 space-y-6">
                <!-- Import Data Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">upload_file</span>
                        Import dữ liệu từ Excel
                    </h2>
                    <form action="{{ route('superadmin.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Loại dữ liệu</label>
                                <select name="import_type" required class="w-full border-gray-300 rounded-lg">
                                    <option value="products">Sản phẩm</option>
                                    <option value="categories">Danh mục</option>
                                    <option value="departments">Khoa/Phòng</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">File Excel</label>
                                <input type="file" name="excel_file" accept=".xlsx,.xls" required class="w-full border-gray-300 rounded-lg">
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Import
                            </button>
                            <a href="{{ route('superadmin.export-template', 'products') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Tải template Sản phẩm
                            </a>
                            <a href="{{ route('superadmin.export-template', 'categories') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Tải template Danh mục
                            </a>
                            <a href="{{ route('superadmin.export-template', 'departments') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Tải template Khoa/Phòng
                            </a>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
                            <strong>⚠️ Lưu ý:</strong> Import sẽ cập nhật dữ liệu hiện có nếu trùng tên. Nên tạo backup trước khi import!
                        </div>
                    </form>
                </div>

                <!-- Backup Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold flex items-center gap-2">
                            <span class="material-symbols-outlined text-green-600">backup</span>
                            Backup Database
                        </h2>
                        <form action="{{ route('superadmin.backup.create') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">add</span>
                                Tạo Backup mới
                            </button>
                        </form>
                    </div>

                    <!-- Backups List -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tên file</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Kích thước</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Ngày tạo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($backups as $backup)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-medium">{{ $backup['name'] }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ number_format($backup['size'] / 1024, 2) }} KB</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $backup['date'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                            Chưa có backup nào
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="font-bold text-blue-900 mb-3">📖 Hướng dẫn sử dụng</h3>
                    <div class="space-y-2 text-sm text-blue-800">
                        <p><strong>Import dữ liệu:</strong></p>
                        <ol class="list-decimal list-inside ml-4 space-y-1">
                            <li>Tải template Excel tương ứng với loại dữ liệu cần import</li>
                            <li>Điền dữ liệu vào file Excel theo đúng cột</li>
                            <li>Chọn loại dữ liệu và upload file</li>
                            <li>Hệ thống sẽ tự động cập nhật hoặc tạo mới dữ liệu</li>
                        </ol>
                        <p class="mt-4"><strong>Backup:</strong></p>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li>Click "Tạo Backup mới" để tạo bản sao lưu database</li>
                            <li>File backup sẽ được lưu trong thư mục storage/app/backups</li>
                            <li>Nên tạo backup trước khi import dữ liệu lớn</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
