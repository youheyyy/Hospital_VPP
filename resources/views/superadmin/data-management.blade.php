@extends('layouts.superadmin')

@section('title', 'Quản lý dữ liệu')

@section('content')
    <!-- Header -->
    <header class="bg-white border-b px-8 py-4 sticky top-0 z-30">
        <h1 class="text-2xl font-bold text-gray-800">Quản lý dữ liệu</h1>
        <p class="text-sm text-gray-500">Import/Export dữ liệu và Backup database</p>
    </header>

    <!-- Success/Error Messages -->
    <div class="px-8 mt-4">
        @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">error</span>
                {{ session('error') }}
            </div>
        @endif
    </div>

    <!-- Content -->
    <div class="p-8 space-y-8">
        <!-- Import Data Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600">upload_file</span>
                Import dữ liệu từ Excel
            </h2>
            <form action="{{ route('superadmin.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Loại dữ liệu</label>
                        <select name="import_type" required class="w-full border-gray-200 rounded-xl focus:ring-purple-500">
                            <option value="products">Sản phẩm</option>
                            <option value="categories">Danh mục</option>
                            <option value="departments">Khoa/Phòng</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">File Excel</label>
                        <input type="file" name="excel_file" accept=".xlsx,.xls" required
                            class="w-full border-gray-200 rounded-xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 transition-all">
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="submit"
                        class="px-8 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
                        Import ngay
                    </button>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('superadmin.export-template', 'products') }}"
                            class="px-4 py-2.5 border border-gray-200 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-50 flex items-center gap-2 transition-all">
                            <span class="material-symbols-outlined text-sm">download</span> SP
                        </a>
                        <a href="{{ route('superadmin.export-template', 'categories') }}"
                            class="px-4 py-2.5 border border-gray-200 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-50 flex items-center gap-2 transition-all">
                            <span class="material-symbols-outlined text-sm">download</span> Danh mục
                        </a>
                        <a href="{{ route('superadmin.export-template', 'departments') }}"
                            class="px-4 py-2.5 border border-gray-200 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-50 flex items-center gap-2 transition-all">
                            <span class="material-symbols-outlined text-sm">download</span> Khoa
                        </a>
                    </div>
                </div>
                <div
                    class="bg-yellow-50 border border-yellow-100 rounded-xl p-4 text-xs text-yellow-800 flex items-start gap-3">
                    <span class="material-symbols-outlined text-[18px]">warning</span>
                    <div>
                        <strong>Lưu ý quan trọng:</strong> Hệ thống sẽ cập nhật dữ liệu nếu trùng tên. Bạn nên <strong>tạo
                            bản sao lưu (Backup)</strong> trước khi thực hiện để đảm bảo an toàn dữ liệu.
                    </div>
                </div>
            </form>
        </div>

        <!-- Backup Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-green-600">backup</span>
                    Backup Database
                </h2>
                <form action="{{ route('superadmin.backup.create') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="px-6 py-2.5 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-all flex items-center gap-2 shadow-lg shadow-green-100">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        Tạo bản sao lưu mới
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-4 text-xs font-bold text-gray-400 uppercase">Tên file</th>
                            <th class="px-8 py-4 text-xs font-bold text-gray-400 uppercase">Kích thước</th>
                            <th class="px-8 py-4 text-xs font-bold text-gray-400 uppercase">Ngày tạo</th>
                            <th class="px-8 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($backups as $backup)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-8 py-4 text-sm font-bold text-slate-900">{{ $backup['name'] }}</td>
                                <td class="px-8 py-4 text-sm text-gray-500 font-medium">
                                    {{ number_format($backup['size'] / 1024, 2) }} KB</td>
                                <td class="px-8 py-4 text-sm text-gray-500 font-medium">{{ $backup['date'] }}</td>
                                <td class="px-8 py-4 text-right">
                                    <span class="material-symbols-outlined text-gray-300">more_horiz</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-12 text-center text-gray-400 italic">
                                    Chưa có bản sao lưu nào được thực hiện.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection