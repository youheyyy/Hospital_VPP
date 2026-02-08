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
        <!-- Import Data Section (COMMENTED OUT AS REQUESTED) -->
        {{--
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
        </div>
        <!-- Advanced Import Warning -->
        <div class="mt-6 bg-blue-50 border border-blue-100 rounded-xl p-4 text-xs text-blue-800 flex items-start gap-3">
            <span class="material-symbols-outlined text-[18px]">info</span>
            <div>
                <strong>Import Nâng Gao (File Tổng Hợp):</strong>
                <ul class="list-disc list-inside mt-1 space-y-1">
                    <li>Sheet <strong>"TỔNG HỢP"</strong>: Dùng để cập nhật Danh mục & Sản phẩm.</li>
                    <li>Các sheet còn lại (tên Khoa): Dùng để cập nhật <strong>Số lượng yêu cầu</strong> của từng khoa.</li>
                    <li>Hệ thống sẽ <strong>XÓA</strong> dữ liệu cũ của tháng được chọn để đảm bảo tính chính xác theo file
                        Excel.</li>
                </ul>
            </div>
        </div>
        </form>
        --}}

        <!-- Use a separate form for Advanced Import? Or combine? 
                                                 Let's create a separate distinct section for clarity as requested.
                                            -->
    </div>

    <!-- Grid Layout for Import & Restore -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Advanced Import Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-600">folder_zip</span>
                Import File Tổng Hợp
            </h2>
            <div class="alert alert-info mb-6 text-xs">
                <strong>Lưu ý:</strong><br>
                - Hệ thống tự động bỏ qua các cột "Sheet", "Column".<br>
                - Tự động cộng dồn số lượng nếu trùng sản phẩm và khoa.<br>
            </div>
            <form action="{{ route('superadmin.import.advanced') }}" method="POST" enctype="multipart/form-data"
                id="importAdvancedForm" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Chọn Tháng/Năm</label>
                    <input type="month" name="month" required value="{{ date('Y-m') }}"
                        class="w-full border-gray-200 rounded-xl focus:ring-purple-500 font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">File Excel Tổng Hợp</label>
                    <input type="file" name="excel_file" accept=".xlsx,.xls" required
                        class="w-full border-gray-200 rounded-xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 transition-all">
                </div>
                <button type="submit"
                    class="w-full px-6 py-2.5 bg-purple-600 text-white font-bold rounded-xl hover:bg-purple-700 transition-all shadow-lg shadow-purple-100 flex justify-center items-center gap-2"
                    onclick="return confirm('Hệ thống sẽ cập nhật dữ liệu cho tháng đã chọn dựa trên file Excel.\n\nDữ liệu cũ của tháng này sẽ bị thay thế để đảm bảo chính xác.\n\nBạn có chắc chắn muốn tiếp tục?');">
                    <span class="material-symbols-outlined text-[20px]">cloud_upload</span>
                    Tiến hành Import
                </button>
            </form>
        </div>

        <!-- Restore Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 h-full">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-yellow-600">publish</span>
                    Khôi phục (Restore)
                </h2>
                <form action="{{ route('superadmin.backup.create') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="px-3 py-1.5 bg-green-100 text-green-700 hover:bg-green-200 rounded-lg text-xs font-bold transition-all flex items-center gap-1 shadow-sm"
                        title="Tạo bản sao lưu dữ liệu ngay lập tức">
                        <span class="material-symbols-outlined text-[16px]">add_circle</span>
                        Tạo Backup
                    </button>
                </form>
            </div>
            <form action="{{ route('superadmin.backup.upload') }}" method="POST" enctype="multipart/form-data"
                class="space-y-6"
                onsubmit="return confirm('CẢNH BÁO: Hành động này sẽ XÓA TOÀN BỘ dữ liệu hiện tại và thay thế bằng dữ liệu trong file upload.\n\nBạn có chắc chắn muốn tiếp tục không?');">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">File Backup (.sql)</label>
                    <input type="file" name="backup_file" accept=".sql" required
                        class="w-full border-gray-200 rounded-xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100 transition-all">
                </div>
                <button type="submit"
                    class="w-full px-6 py-2.5 bg-yellow-600 text-white font-bold rounded-xl hover:bg-yellow-700 transition-all shadow-lg shadow-yellow-100 flex justify-center items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">history</span>
                    Upload & Khôi phục ngay
                </button>
                <div class="mt-4 p-4 bg-yellow-50 rounded-xl text-xs text-yellow-800 border border-yellow-100">
                    <strong>Lưu ý:</strong> Dùng để khôi phục dữ liệu từ máy tính (ví dụ: file backup tháng trước).
                </div>
            </form>

            <hr class="my-6 border-gray-100">

            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-green-600 text-[18px]">dns</span>
                Danh sách Backup có sẵn trên Server
            </h3>

            <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                @forelse($backups as $backup)
                    {{-- Hide V2 Backups as requested --}}
                    @if(str_contains($backup['name'], '_v2.sql')) @continue @endif

                    <div
                        class="p-3 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-white hover:shadow-sm transition-all flex justify-between items-center group">
                        <div class="flex items-center gap-3">
                            <span
                                class="material-symbols-outlined {{ str_contains($backup['name'], '_auto_') ? 'text-green-500' : 'text-gray-400' }} text-[20px]">
                                {{ str_contains($backup['name'], '_auto_') ? 'check_circle' : 'description' }}
                            </span>
                            <div>
                                <p class="text-xs font-bold text-slate-700 truncate max-w-[200px]"
                                    title="{{ $backup['name'] }}">
                                    {{ $backup['name'] }}
                                </p>
                                <p class="text-[10px] text-gray-400">{{ $backup['date'] }} •
                                    {{ number_format($backup['size'] / 1024, 1) }} KB
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 opacity-60 group-hover:opacity-100 transition-opacity">
                            {{-- Restore Button --}}
                            <form action="{{ route('superadmin.backup.restore', $backup['name']) }}" method="POST"
                                onsubmit="return confirm('CẢNH BÁO: Khôi phục từ file này sẽ XÓA dữ liệu hiện tại.\n\nTiếp tục?');">
                                @csrf
                                <button type="submit"
                                    class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors"
                                    title="Khôi phục ngay">
                                    <span class="material-symbols-outlined text-[18px]">history</span>
                                </button>
                            </form>

                            <a href="{{ route('superadmin.backup.download', $backup['name']) }}"
                                class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Tải về">
                                <span class="material-symbols-outlined text-[18px]">download</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-xs text-gray-400 italic py-4">Chưa có file backup nào.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Auto Backup Config -->
    <!-- Auto Backup Config (HIDDEN AS REQUESTED) -->
    {{--
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
            <span class="material-symbols-outlined text-purple-600">timer</span>
            Cấu hình Tự động Backup
        </h2>
        <form action="{{ route('superadmin.backup.config') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Phút</label>
                    <input type="number" name="interval_minutes" min="0" value="{{ $backupConfig['minutes'] ?? 0 }}"
                        class="w-full border-gray-200 rounded-xl focus:ring-purple-500 text-center font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Giây</label>
                    <input type="number" name="interval_seconds" min="0" max="59"
                        value="{{ $backupConfig['seconds'] ?? 0 }}"
                        class="w-full border-gray-200 rounded-xl focus:ring-purple-500 text-center font-bold">
                </div>
            </div>
            <button type="submit"
                class="w-full px-8 py-2.5 bg-purple-600 text-white font-bold rounded-xl hover:bg-purple-700 transition-all shadow-lg shadow-purple-100 flex justify-center items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">save</span>
                Lưu cấu hình
            </button>
            <p class="text-xs text-gray-500 italic mt-2">
                * Hệ thống sẽ tự động tạo bản backup mới nếu có thay đổi và quá thời gian quy định.
                (Để 0 để tắt tính năng này).
            </p>
        </form>
    </div>
    --}}
    </div>
    {{-- Backup Table Removed from here to move inside Restore Card --}}
    </div>
@endsection