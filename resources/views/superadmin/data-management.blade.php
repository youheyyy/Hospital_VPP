@extends('layouts.superadmin')

@section('title', 'Quản lý dữ liệu')

@section('content')
    <!-- Header -->
    <header class="bg-white border-b px-8 py-4 sticky top-0 z-30">
        <h1 class="text-2xl font-bold text-gray-800">Quản lý dữ liệu</h1>
        <p class="text-sm text-gray-500">Import/Export dữ liệu và Backup database</p>
    </header>

    <!-- Success/Error Messages -->
    <div class="px-8 mt-2">
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
    <div class="px-8 pt-2 pb-8 space-y-4">
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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Advanced Import Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 h-full flex flex-col">
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
                id="importAdvancedForm" class="space-y-6 flex-grow flex flex-col">
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
                <div class="mt-auto">
                    <button type="submit"
                        class="w-full px-6 py-2.5 bg-purple-600 text-white font-bold rounded-xl hover:bg-purple-700 transition-all shadow-lg shadow-purple-100 flex justify-center items-center gap-2"
                        onclick="return confirm('Hệ thống sẽ cập nhật dữ liệu cho tháng đã chọn dựa trên file Excel.\n\nDữ liệu cũ của tháng này sẽ bị thay thế để đảm bảo chính xác.\n\nBạn có chắc chắn muốn tiếp tục?');">
                        <span class="material-symbols-outlined text-[20px]">cloud_upload</span>
                        Tiến hành Import
                    </button>
                </div>
            </form>
        </div>

        <!-- Upload & Restore Section (Horizontal with Import) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 h-full flex flex-col">
            <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-yellow-600">publish</span>
                Upload & Khôi phục
            </h2>
            <div class="alert alert-warning mb-6 text-xs text-yellow-800 bg-yellow-50 border-yellow-100">
                <strong>Lưu ý:</strong> Dùng để khôi phục dữ liệu từ máy tính (ví dụ: file backup tháng trước).
            </div>
            <form action="{{ route('superadmin.backup.upload') }}" method="POST" enctype="multipart/form-data"
                class="space-y-6 flex-grow flex flex-col"
                onsubmit="return confirm('CẢNH BÁO: Hành động này sẽ XÓA TOÀN BỘ dữ liệu hiện tại và thay thế bằng dữ liệu trong file upload.\n\nBạn có chắc chắn muốn tiếp tục không?');">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">File Backup (.sql)</label>
                    <input type="file" name="backup_file" accept=".sql" required
                        class="w-full border-gray-200 rounded-xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100 transition-all">
                </div>
                <!-- Spacer to match the "Month" input height in the other card roughly, or just rely on mt-auto -->
                <div class="hidden md:block h-[74px]"></div>

                <div class="mt-auto">
                    <button type="submit"
                        class="w-full px-6 py-2.5 bg-yellow-600 text-white font-bold rounded-xl hover:bg-yellow-700 transition-all shadow-lg shadow-yellow-100 flex justify-center items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">history</span>
                        Upload & Khôi phục ngay
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- Backup Database Section (Full Width - Image 1 Style) -->
    <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-green-600">cloud_upload</span>
                Backup Database
            </h2>
            <form action="{{ route('superadmin.backup.create') }}" method="POST">
                @csrf
                <button type="submit"
                    class="px-4 py-2 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-all shadow-lg shadow-green-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    Tạo bản sao lưu mới
                </button>
            </form>
        </div>

        <!-- Toggle Upload Form -->


        <!-- Backup List -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-xs font-bold text-gray-400 uppercase border-b border-gray-100">
                        <th class="py-3 pl-2">Tên file</th>
                        <th class="py-3">Kích thước</th>
                        <th class="py-3">Ngày tạo</th>
                        <th class="py-3 text-right pr-2">Hành động</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-50">
                    @php $latestAutoShown = false; @endphp
                    @forelse($backups as $backup)
                        {{-- Hide V2 Backups --}}
                        @if(str_contains($backup['name'], '_v2.sql')) @continue @endif

                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="py-3 pl-2">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="material-symbols-outlined text-blue-500 bg-blue-50 p-1.5 rounded-lg text-[20px]">
                                        {{ str_contains($backup['name'], '_auto_') ? 'schedule' : 'description' }}
                                    </span>
                                    <div>
                                        <span class="font-bold text-slate-700 block">{{ $backup['name'] }}</span>
                                        @if(str_contains($backup['name'], '_auto_'))
                                            @if(!$latestAutoShown)
                                                @php $latestAutoShown = true; @endphp
                                                <span
                                                    class="text-[10px] items-center gap-1 inline-flex bg-green-50 text-green-700 px-1.5 py-0.5 rounded border border-green-100 font-bold mt-1">
                                                    Bản chính (Mới nhất)
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 text-gray-500">
                                {{ number_format($backup['size'] / 1024, 2) }} KB
                            </td>
                            <td class="py-3 text-gray-500">
                                {{ $backup['date'] }}
                            </td>
                            <td class="py-3 text-right pr-2">
                                <div
                                    class="flex justify-end items-center gap-2 opacity-100 group-hover:opacity-100 transition-opacity">
                                    {{-- Restore --}}
                                    <form action="{{ route('superadmin.backup.restore', $backup['name']) }}" method="POST"
                                        onsubmit="return confirm('CẢNH BÁO: Khôi phục từ file này sẽ XÓA dữ liệu hiện tại.\n\nTiếp tục?');">
                                        @csrf
                                        <button type="submit"
                                            class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors"
                                            title="Khôi phục">
                                            <span class="material-symbols-outlined text-[20px]">history</span>
                                        </button>
                                    </form>
                                    {{-- Download --}}
                                    <a href="{{ route('superadmin.backup.download', $backup['name']) }}"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                        title="Tải về">
                                        <span class="material-symbols-outlined text-[20px]">download</span>
                                    </a>
                                    {{-- Delete --}}
                                    <form action="{{ route('superadmin.backup.delete', $backup['name']) }}" method="POST"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa file backup này không?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Xóa">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-gray-400 italic">
                                Chưa có file backup nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
@endsection