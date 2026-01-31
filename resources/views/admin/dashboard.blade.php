@extends('layouts.admin')

@section('title', 'Admin - Tổng Quan | Hệ Thống Vật Tư Y Tế')

@section('page-title', 'Bảng Điều Khiển')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-1">Chào buổi sáng, Admin</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Hệ thống đang hoạt động ổn định. Bạn có <span
                class="text-primary font-semibold">12 thông báo</span> cần xử lý trong hôm nay.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div
            class="bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between group cursor-pointer">
            <div>
                <p class="text-[13px] font-semibold text-slate-500 dark:text-slate-400 mb-2">Phiếu chờ duyệt</p>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-slate-900 dark:text-white">{{ $pendingRequests }}</span>
                    <span
                        class="text-xs font-medium text-amber-500 bg-amber-50 dark:bg-amber-900/20 px-2 py-1 rounded-md">Cần
                        xử lý</span>
                </div>
            </div>
            <div
                class="bg-primary/10 p-4 rounded-xl text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300">
                <span class="material-symbols-outlined text-3xl">pending_actions</span>
            </div>
        </div>
        <div
            class="bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between group cursor-pointer">
            <div>
                <p class="text-[13px] font-semibold text-slate-500 dark:text-slate-400 mb-2">Tổng sản phẩm trong
                    kho</p>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($totalOrders) }}</span>
                    <span class="text-xs font-medium text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-1 rounded-md">Toàn hệ thống</span>
                </div>
            </div>
            <div
                class="bg-emerald-50 dark:bg-emerald-900/10 p-4 rounded-xl text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-300">
                <span class="material-symbols-outlined text-3xl">inventory</span>
            </div>
        </div>
        <div
            class="bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between group cursor-pointer">
            <div>
                <p class="text-[13px] font-semibold text-slate-500 dark:text-slate-400 mb-2">Khoa/Phòng hoạt
                    động</p>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-slate-900 dark:text-white">{{ $totalDepartments }}</span>
                    <span class="text-xs font-medium text-blue-600 bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded-md">Đơn vị</span>
                </div>
            </div>
            <div
                class="bg-blue-50 dark:bg-blue-900/10 p-4 rounded-xl text-blue-600 group-hover:bg-blue-500 group-hover:text-white transition-all duration-300">
                <span class="material-symbols-outlined text-3xl">local_hospital</span>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div
            class="lg:col-span-4 bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Biểu đồ yêu cầu hàng tháng</h3>
                    <p class="text-xs text-slate-500 mt-1">Dữ liệu thống kê năm 2024 (Đơn vị: Phiếu)</p>
                </div>
                <div class="flex p-1 bg-slate-100 dark:bg-slate-700 rounded-md">
                    <button class="px-3 py-1 text-xs font-semibold text-slate-600 dark:text-slate-300">Tháng</button>
                    <button
                        class="px-3 py-1 text-xs font-semibold bg-white dark:bg-slate-600 shadow-sm rounded text-primary">Năm</button>
                </div>
            </div>
            <div
                class="h-64 flex items-end justify-between gap-6 px-4 border-b border-slate-100 dark:border-slate-700 pb-2">
                <div class="flex-1 flex flex-col items-center gap-3">
                    <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-sm chart-bar" style="height: 40%;"></div>
                    <span class="text-[11px] font-medium text-slate-400 uppercase">T4</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-3">
                    <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-sm chart-bar" style="height: 60%;"></div>
                    <span class="text-[11px] font-medium text-slate-400 uppercase">T5</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-3">
                    <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-sm chart-bar" style="height: 50%;"></div>
                    <span class="text-[11px] font-medium text-slate-400 uppercase">T6</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-3">
                    <div class="w-full bg-primary/40 hover:bg-primary/60 rounded-t-sm chart-bar border-x-2 border-t-2 border-primary/20"
                        style="height: 80%;"></div>
                    <span class="text-[11px] font-bold text-primary uppercase">T7</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-3">
                    <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-sm chart-bar" style="height: 70%;"></div>
                    <span class="text-[11px] font-medium text-slate-400 uppercase">T8</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-3">
                    <div class="w-full bg-primary/60 rounded-t-sm chart-bar" style="height: 95%;"></div>
                    <span class="text-[11px] font-medium text-slate-400 uppercase">T9</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-3">
                    <div class="w-full bg-primary/20 rounded-t-sm chart-bar" style="height: 55%;"></div>
                    <span class="text-[11px] font-medium text-slate-400 uppercase">T10</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-3">
                    <div class="w-full bg-primary/20 rounded-t-sm chart-bar" style="height: 45%;"></div>
                    <span class="text-[11px] font-medium text-slate-400 uppercase">T11</span>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-10 border-slate-200 dark:border-slate-800">

    <div class="mb-8">
        <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Chi tiết báo cáo thống kê
        </h3>
        <p class="text-xs text-slate-500 font-medium mt-1">Dữ liệu chi phí và phân bổ vật tư y tế</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div
            class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col gap-4">
            <h3 class="font-semibold text-slate-800 dark:text-slate-100">Bộ lọc thời gian</h3>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tháng</label>
                    <select
                        class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold">
                        @for($m = 1; $m <= 12; $m++)
                            <option {{ date('m') == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Năm</label>
                    <select
                        class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold">
                        <option selected>2024</option>
                        <option>2025</option>
                    </select>
                </div>
            </div>
            <button
                class="w-full bg-primary text-white py-2 rounded-lg text-xs font-bold hover:bg-primary/90 transition-colors">
                CẬP NHẬT BÁO CÁO
            </button>
        </div>

        <div class="bg-primary p-6 rounded-xl shadow-lg flex flex-col justify-between text-white relative overflow-hidden">
            <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-white/10 text-9xl">payments</span>
            <div>
                <p class="text-white/80 text-[10px] font-bold uppercase tracking-widest">Tổng chi phí tháng</p>
                <h2 class="text-2xl font-black mt-1">1.245.000 VNĐ</h2>
            </div>
            <div class="mt-4 flex items-center gap-1 text-[10px] font-bold bg-white/20 w-fit px-2 py-1 rounded">
                <span class="material-symbols-outlined text-xs">trending_up</span>
                <span>+4.2% so với tháng trước</span>
            </div>
        </div>

        <div
            class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
            <div>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">Phiếu đã duyệt</p>
                <h2 class="text-2xl font-black text-slate-900 dark:text-white mt-1">42</h2>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <div class="flex -space-x-2">
                    <div
                        class="w-7 h-7 rounded-full border-2 border-white dark:border-slate-900 bg-blue-100 flex items-center justify-center text-[9px] font-black text-blue-600">
                        BS</div>
                    <div
                        class="w-7 h-7 rounded-full border-2 border-white dark:border-slate-900 bg-green-100 flex items-center justify-center text-[9px] font-black text-green-600">
                        YT</div>
                    <div
                        class="w-7 h-7 rounded-full border-2 border-white dark:border-slate-900 bg-amber-100 flex items-center justify-center text-[9px] font-black text-amber-600">
                        KT</div>
                </div>
                <span class="text-[9px] font-bold text-slate-400">12 khoa/phòng</span>
            </div>
        </div>

        <div
            class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
            <div>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">Hạng mục chi nhiều nhất</p>
                <h2 class="text-lg font-black text-slate-900 dark:text-white mt-1">Văn phòng phẩm</h2>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full mt-4 overflow-hidden">
                <div class="bg-primary h-full w-[75%] rounded-full"></div>
            </div>
            <span class="text-[9px] font-bold text-slate-400 mt-2">Chiếm 75% ngân sách</span>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <div
            class="xl:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <h3
                    class="font-black text-slate-900 dark:text-white uppercase tracking-tight text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">bar_chart</span>
                    Chi phí theo bộ phận (Khoa)
                </h3>
            </div>
            <div class="flex items-end justify-between h-48 gap-4 pt-4">
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-lg transition-all h-[40%]"
                        title="Cấp cứu: 450.000đ"></div>
                    <span class="text-[9px] font-bold text-slate-400 mt-4 rotate-[-45deg]">Cấp cứu</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-lg transition-all h-[65%]"
                        title="Phòng mổ: 780.000đ"></div>
                    <span class="text-[9px] font-bold text-slate-400 mt-4 rotate-[-45deg]">P. Mổ</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    <div class="w-full bg-primary rounded-t-lg transition-all h-[95%]" title="Khoa Dược: 1.120.000đ"></div>
                    <span class="text-[9px] font-black text-primary mt-4 rotate-[-45deg]">K. Dược</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-lg transition-all h-[30%]"
                        title="Khám bệnh: 310.000đ"></div>
                    <span class="text-[9px] font-bold text-slate-400 mt-4 rotate-[-45deg]">P. Khám</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-lg transition-all h-[50%]"
                        title="Nội soi: 520.000đ"></div>
                    <span class="text-[9px] font-bold text-slate-400 mt-4 rotate-[-45deg]">Nội soi</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-lg transition-all h-[45%]"
                        title="Xét nghiệm: 490.000đ"></div>
                    <span class="text-[9px] font-bold text-slate-400 mt-4 rotate-[-45deg]">X. Nghiệm</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-lg transition-all h-[20%]"
                        title="Hành chính: 150.000đ"></div>
                    <span class="text-[9px] font-bold text-slate-400 mt-4 rotate-[-45deg]">H. Chính</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <h3
                class="font-black text-slate-900 dark:text-white uppercase tracking-tight text-sm mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-500">warning</span>
                Cảnh báo tồn kho
            </h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.5)]"></div>
                        <span class="text-xs font-bold text-slate-700">Bìa sơ mi lá lỗ</span>
                    </div>
                    <span class="text-[10px] font-black text-red-500 bg-red-50 px-2 py-0.5 rounded">3 XẤP</span>
                </div>
                <div class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.5)]"></div>
                        <span class="text-xs font-bold text-slate-700">Pin AA tốt</span>
                    </div>
                    <span class="text-[10px] font-black text-amber-500 bg-amber-50 px-2 py-0.5 rounded">12 CẶP</span>
                </div>
                <div class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.5)]"></div>
                        <span class="text-xs font-bold text-slate-700">Giấy A4 80gsm</span>
                    </div>
                    <span class="text-[10px] font-black text-red-500 bg-red-50 px-2 py-0.5 rounded">5 GRAM</span>
                </div>
                <button
                    class="w-full mt-2 py-2 border-2 border-dashed border-slate-100 text-slate-400 text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-slate-50 hover:border-primary/20 hover:text-primary transition-all">
                    TÔI MUỐN XEM TẤT CẢ
                </button>
            </div>
        </div>
    </div>

    <div
        class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="font-black text-slate-900 dark:text-white uppercase tracking-tight text-sm">Chi tiết sử dụng hàng
                    hóa</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Khoa Cấp Cứu • Tháng
                    {{ date('m/Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <button
                    class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-lg text-xs font-bold hover:bg-emerald-100 transition-all">
                    <span class="material-symbols-outlined !text-sm">download</span>
                    XUẤT EXCEL
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50">
                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.12em]">
                        <th class="px-6 py-4">Stt</th>
                        <th class="px-6 py-4">Tên hàng hóa</th>
                        <th class="px-6 py-4 text-center">ĐVT</th>
                        <th class="px-6 py-4 text-center">Số Lượng</th>
                        <th class="px-6 py-4 text-right">Đơn giá</th>
                        <th class="px-6 py-4 text-right">Thành Tiền</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr class="bg-blue-50/30">
                        <td class="px-6 py-2 text-[10px] font-black text-primary uppercase tracking-widest" colspan="6">VĂN
                            PHÒNG PHẨM - NHÀ SÁCH THANH VÂN</td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3 text-xs font-bold text-slate-400">01</td>
                        <td class="px-6 py-3">
                            <div class="text-xs font-bold text-slate-800">Bìa sơ mi lá lỗ cung tròn</div>
                            <div class="text-[9px] text-slate-400 font-mono">VPP-0012</div>
                        </td>
                        <td class="px-6 py-3 text-center text-[10px] font-bold text-slate-500">xấp</td>
                        <td class="px-6 py-3 text-center text-xs font-black text-red-600">1</td>
                        <td class="px-6 py-3 text-right text-xs font-bold text-slate-600">25.000 VNĐ</td>
                        <td class="px-6 py-3 text-right text-xs font-black text-slate-900">25.000 VNĐ</td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3 text-xs font-bold text-slate-400">17</td>
                        <td class="px-6 py-3">
                            <div class="text-xs font-bold text-slate-800">Kim bấm No.10</div>
                            <div class="text-[9px] text-slate-400 font-mono">VPP-0045</div>
                        </td>
                        <td class="px-6 py-3 text-center text-[10px] font-bold text-slate-500">hộp</td>
                        <td class="px-6 py-3 text-center text-xs font-black text-red-600">2</td>
                        <td class="px-6 py-3 text-right text-xs font-bold text-slate-600">2.800 VNĐ</td>
                        <td class="px-6 py-3 text-right text-xs font-black text-slate-900">5.600 VNĐ</td>
                    </tr>
                    <tr class="bg-primary/5 font-bold">
                        <td class="px-6 py-4 text-right text-[10px] text-slate-500 uppercase font-black" colspan="5">Tổng
                            cộng bộ phận:</td>
                        <td class="px-6 py-4 text-right text-sm text-primary font-black">194,000 VNĐ</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
        <div
            class="md:col-span-3 bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="p-2 bg-slate-100 dark:bg-slate-700 rounded-full">
                    <span class="material-symbols-outlined text-slate-500">info</span>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300">Cần hỗ trợ về hệ thống? Liên hệ phòng
                    CNTT: <span class="font-bold">Ext 112</span></p>
            </div>
            <button
                class="px-4 py-1.5 text-xs font-bold border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50">Hướng
                dẫn sử dụng</button>
        </div>
    </div>
@endsection