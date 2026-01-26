@extends('layouts.department')

@section('title', 'Tạo Phiếu Yêu Cầu Văn Phòng Phẩm')

@section('styles')
<style type="text/tailwindcss">
    .form-input-large {
        @apply text-lg py-3 px-4 border-2 border-[#ccd1d9] focus:border-primary focus:ring-0 rounded-lg w-full bg-white dark:bg-[#1a2131] transition-all;
    }
    .label-text {
        @apply text-base font-bold text-[#172b4d] dark:text-gray-200 mb-2 block;
    }
    .btn-large {
        @apply text-lg font-bold py-4 px-8 rounded-xl transition-all flex items-center justify-center gap-3 active:scale-[0.98];
    }
</style>
@endsection

@section('content')
<div class="max-w-6xl mx-auto w-full">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 mb-6 text-sm text-gray-500 font-medium">
        <a class="hover:text-primary" href="{{ route('department.dashboard') }}">Trang chủ</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <a class="hover:text-primary" href="#">Quản lý vật tư</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-primary font-bold">Tạo phiếu yêu cầu mới</span>
    </div>

    <!-- Page Header -->
    <div class="mb-10">
        <h1 class="text-4xl font-black text-[#091e42] dark:text-white tracking-tight mb-2">Tạo Phiếu Yêu Cầu</h1>
        <p class="text-lg text-gray-500 dark:text-gray-400">Vui lòng điền thông tin chi tiết để yêu cầu văn phòng phẩm cho khoa/phòng.</p>
    </div>

    <div class="space-y-8">
        <!-- Main Form Card -->
        <div class="bg-white dark:bg-[#1a2131] rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-none border-2 border-[#cfd7e7] dark:border-[#2a3447] overflow-hidden">
            
            <!-- General Information Section -->
            <div class="p-8">
                <div class="flex items-center gap-3 mb-8 pb-4 border-b-2 border-gray-100 dark:border-gray-800">
                    <span class="material-symbols-outlined text-primary text-3xl">info</span>
                    <h3 class="text-xl font-black uppercase tracking-wide">Thông tin chung</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <label class="label-text">Mã Phiếu</label>
                        <input class="form-input-large bg-gray-100 dark:bg-gray-800 cursor-not-allowed font-mono text-gray-500" readonly type="text" value="PYC-20231027-001" />
                    </div>
                    <div>
                        <label class="label-text">Ngày Yêu Cầu</label>
                        <input class="form-input-large" type="date" value="{{ date('Y-m-d') }}" />
                    </div>
                    <div>
                        <label class="label-text">Khoa / Phòng</label>
                        <select class="form-input-large">
                            <option>Khoa Cấp Cứu</option>
                            <option>Khoa Chẩn Đoán Hình Ảnh</option>
                            <option>Khoa Nhi</option>
                            <option>Phòng Hành Chính Tổng Hợp</option>
                            <option>Phòng Khám Ngoại Trú</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Product List Section -->
            <div class="p-8 bg-gray-50/80 dark:bg-[#161c2b]">
                <div class="flex items-center justify-between mb-8 pb-4 border-b-2 border-gray-200 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary text-3xl">list_alt</span>
                        <h3 class="text-xl font-black uppercase tracking-wide">Danh sách vật tư yêu cầu</h3>
                    </div>
                    <span class="bg-primary/10 text-primary px-4 py-1.5 rounded-full text-sm font-bold">Đã chọn 3 loại</span>
                </div>

                <!-- Search and Add Section -->
                <div class="flex flex-col md:flex-row gap-6 mb-8 items-end">
                    <div class="flex-1 w-full">
                        <label class="label-text">Tìm kiếm sản phẩm (Tên hoặc Mã SKU)</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-2xl">search</span>
                            <input class="form-input-large pl-14 text-xl" placeholder="Nhập tên sản phẩm để tìm..." type="text" />
                        </div>
                    </div>
                    <button class="btn-large bg-primary text-white hover:bg-primary-dark shadow-lg shadow-primary/20 w-full md:w-auto min-w-[200px]">
                        <span class="material-symbols-outlined">add_circle</span>
                        Thêm dòng
                    </button>
                </div>

                <!-- Products Table -->
                <div class="bg-white dark:bg-background-dark border-2 border-[#cfd7e7] dark:border-[#2a3447] rounded-xl overflow-hidden shadow-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#f8fafc] dark:bg-gray-800 text-[#475569] dark:text-gray-300">
                                <th class="px-6 py-5 font-bold text-base border-b-2 border-[#cfd7e7] dark:border-[#2a3447]">Tên sản phẩm / Thông tin</th>
                                <th class="px-6 py-5 font-bold text-base border-b-2 border-[#cfd7e7] dark:border-[#2a3447] text-center w-40">Tồn kho</th>
                                <th class="px-6 py-5 font-bold text-base border-b-2 border-[#cfd7e7] dark:border-[#2a3447] w-48">Số lượng</th>
                                <th class="px-6 py-5 font-bold text-base border-b-2 border-[#cfd7e7] dark:border-[#2a3447] text-center w-24">Xóa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr class="hover:bg-blue-50/30 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-6 py-6">
                                    <div class="font-bold text-lg text-primary">Giấy in A4 (Định lượng 80gsm)</div>
                                    <div class="text-sm font-medium text-gray-500 mt-1 uppercase tracking-tight">SKU: PAP-A4-80</div>
                                </td>
                                <td class="px-6 py-6 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-bold">45 Ram</span>
                                </td>
                                <td class="px-6 py-6">
                                    <input class="form-input-large text-center font-bold" type="number" value="10" />
                                </td>
                                <td class="px-6 py-6 text-center">
                                    <button class="text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 p-3 rounded-full transition-all">
                                        <span class="material-symbols-outlined text-2xl">delete_forever</span>
                                    </button>
                                </td>
                            </tr>
                            <tr class="hover:bg-blue-50/30 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-6 py-6">
                                    <div class="font-bold text-lg text-primary">Bút bi xanh (Hộp 12 cây)</div>
                                    <div class="text-sm font-medium text-gray-500 mt-1 uppercase tracking-tight">SKU: PEN-BLU-12</div>
                                </td>
                                <td class="px-6 py-6 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-bold">12 Hộp</span>
                                </td>
                                <td class="px-6 py-6">
                                    <input class="form-input-large text-center font-bold" type="number" value="2" />
                                </td>
                                <td class="px-6 py-6 text-center">
                                    <button class="text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 p-3 rounded-full transition-all">
                                        <span class="material-symbols-outlined text-2xl">delete_forever</span>
                                    </button>
                                </td>
                            </tr>
                            <tr class="hover:bg-blue-50/30 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-6 py-6">
                                    <div class="font-bold text-lg text-primary">File còng nhẫn A4</div>
                                    <div class="text-sm font-medium text-gray-500 mt-1 uppercase tracking-tight">SKU: FIL-LEV-A4</div>
                                </td>
                                <td class="px-6 py-6 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-bold">120 Chiếc</span>
                                </td>
                                <td class="px-6 py-6">
                                    <input class="form-input-large text-center font-bold" type="number" value="5" />
                                </td>
                                <td class="px-6 py-6 text-center">
                                    <button class="text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 p-3 rounded-full transition-all">
                                        <span class="material-symbols-outlined text-2xl">delete_forever</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reason and Actions Section -->
            <div class="p-8">
                <div class="mb-10">
                    <label class="label-text">Lý do yêu cầu</label>
                    <textarea class="form-input-large min-h-[120px]" placeholder="Nhập lý do cần cấp vật tư (VD: Cấp định kỳ tháng 10 cho Khoa Cấp Cứu)..." rows="4"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col md:flex-row justify-between items-center pt-8 border-t-2 border-gray-100 dark:border-gray-800 gap-6">
                    <button class="text-gray-500 dark:text-gray-400 font-bold hover:text-red-600 transition-colors flex items-center gap-2 text-lg">
                        <span class="material-symbols-outlined">delete_sweep</span>
                        Hủy phiếu
                    </button>
                    <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                        <button class="btn-large border-2 border-primary text-primary bg-white dark:bg-transparent hover:bg-primary/5 min-w-[180px]">
                            <span class="material-symbols-outlined">save</span>
                            Lưu nháp
                        </button>
                        <button class="btn-large bg-primary text-white hover:bg-primary-dark shadow-xl shadow-primary/30 min-w-[240px]">
                            <span class="material-symbols-outlined">send</span>
                            Gửi yêu cầu
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Important Notice -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-primary p-6 rounded-r-xl flex items-start gap-4">
            <span class="material-symbols-outlined text-primary text-3xl">info</span>
            <div>
                <h4 class="font-bold text-primary mb-1 text-lg">Lưu ý quan trọng</h4>
                <p class="text-blue-800 dark:text-blue-300 leading-relaxed">
                    Yêu cầu sau khi gửi sẽ được chuyển đến <strong>Phòng Quản trị vật tư</strong> để phê duyệt. Bạn sẽ nhận được thông báo qua hệ thống khi trạng thái phiếu thay đổi. Vui lòng kiểm tra kỹ số lượng trước khi nhấn "Gửi yêu cầu".
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Add your JavaScript here if needed
    console.log('Request page loaded');
</script>
@endsection
