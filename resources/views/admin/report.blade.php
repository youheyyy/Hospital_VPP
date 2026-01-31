@extends('layouts.admin')

@section('title', 'Báo Cáo Thống Kê | Hệ Thống Vật Tư Y Tế')

@section('page-title', 'Báo Cáo Thống Kê')

@push('styles')
    <style>
        .chart-bar {
            transition: height 1s ease-in-out;
        }
    </style>
@endpush

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <p class="text-slate-500 dark:text-slate-400">Quản lý chi phí văn phòng phẩm và vật tư y tế</p>
        </div>
        <div class="flex items-center gap-3">
            <button
                class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
                <span class="material-icons text-sm">print</span>
                <span>In báo cáo</span>
            </button>
            <button
                class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm transition-all">
                <span class="material-icons text-sm">download</span>
                <span>Xuất Excel</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
        <div
            class="lg:col-span-1 bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col gap-4">
            <h3 class="font-semibold text-slate-800 dark:text-slate-100">Bộ lọc thời gian</h3>
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Chọn Tháng</label>
                <select
                    class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-primary focus:border-primary">
                    <option>Tháng 1</option>
                    <option>Tháng 2</option>
                    <option>Tháng 3</option>
                    <option>Tháng 4</option>
                    <option>Tháng 5</option>
                    <option selected>Tháng 6</option>
                    <option>Tháng 7</option>
                    <option>Tháng 8</option>
                    <option>Tháng 9</option>
                    <option>Tháng 10</option>
                    <option>Tháng 11</option>
                    <option>Tháng 12</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Chọn Năm</label>
                <select
                    class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-primary focus:border-primary">
                    <option>2022</option>
                    <option>2023</option>
                    <option selected>2024</option>
                    <option>2025</option>
                </select>
            </div>
            <button
                class="mt-2 w-full bg-primary text-white py-2 rounded-lg font-medium hover:bg-primary/90 transition-colors">
                Áp dụng lọc
            </button>
        </div>

        <div
            class="lg:col-span-1 bg-primary p-6 rounded-xl shadow-lg flex flex-col justify-between text-white relative overflow-hidden">
            <span class="material-icons absolute -right-4 -bottom-4 text-white/10 text-9xl">payments</span>
            <div>
                <p class="text-white/80 text-sm font-medium">Tổng chi phí tháng 06/2024</p>
                <h2 class="text-3xl font-bold mt-1">1.245.000 VNĐ</h2>
            </div>
            <div class="mt-4 flex items-center gap-1 text-sm bg-white/20 w-fit px-2 py-1 rounded">
                <span class="material-icons text-sm">trending_up</span>
                <span>+4.2% so với tháng trước</span>
            </div>
        </div>

        <div
            class="lg:col-span-1 bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
            <div>
                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Phiếu đã duyệt</p>
                <h2 class="text-3xl font-bold text-slate-900 dark:text-white mt-1">42</h2>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <div class="flex -space-x-2">
                    <div
                        class="w-8 h-8 rounded-full border-2 border-white dark:border-slate-900 bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-600">
                        BS</div>
                    <div
                        class="w-8 h-8 rounded-full border-2 border-white dark:border-slate-900 bg-green-100 flex items-center justify-center text-xs font-bold text-green-600">
                        YT</div>
                    <div
                        class="w-8 h-8 rounded-full border-2 border-white dark:border-slate-900 bg-amber-100 flex items-center justify-center text-xs font-bold text-amber-600">
                        KT</div>
                </div>
                <span class="text-xs text-slate-500 dark:text-slate-400">Phân bổ theo 12 khoa</span>
            </div>
        </div>

        <div
            class="lg:col-span-1 bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
            <div>
                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Hạng mục chi nhiều nhất</p>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Văn phòng phẩm</h2>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full mt-4 overflow-hidden">
                <div class="bg-primary h-full w-[75%] rounded-full"></div>
            </div>
            <span class="text-xs text-slate-500 dark:text-slate-400 mt-2">75% tổng chi phí trong tháng</span>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <div
            class="xl:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-icons text-primary">bar_chart</span>
                    Chi phí theo bộ phận (Khoa)
                </h3>
                <button class="text-xs text-primary font-semibold hover:underline">Xem chi tiết</button>
            </div>
            <div class="flex items-end justify-between h-48 gap-2 pt-4">
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-lg transition-all h-[40%]"
                        title="Cấp cứu: 450.000đ"></div>
                    <span class="text-[10px] font-medium text-slate-500 uppercase rotate-[-45deg] mt-4">Cấp cứu</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-lg transition-all h-[65%]"
                        title="Phòng mổ: 780.000đ"></div>
                    <span class="text-[10px] font-medium text-slate-500 uppercase rotate-[-45deg] mt-4">P. Mổ</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    <div class="w-full bg-primary rounded-t-lg transition-all h-[95%]" title="Khoa Dược: 1.120.000đ"></div>
                    <span class="text-[10px] font-bold text-primary uppercase rotate-[-45deg] mt-4">K. Dược</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-lg transition-all h-[30%]"
                        title="Khám bệnh: 310.000đ"></div>
                    <span class="text-[10px] font-medium text-slate-500 uppercase rotate-[-45deg] mt-4">P. Khám</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-lg transition-all h-[50%]"
                        title="Nội soi: 520.000đ"></div>
                    <span class="text-[10px] font-medium text-slate-500 uppercase rotate-[-45deg] mt-4">Nội soi</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-lg transition-all h-[45%]"
                        title="Xét nghiệm: 490.000đ"></div>
                    <span class="text-[10px] font-medium text-slate-500 uppercase rotate-[-45deg] mt-4">X. Nghiệm</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-lg transition-all h-[20%]"
                        title="Hành chính: 150.000đ"></div>
                    <span class="text-[10px] font-medium text-slate-500 uppercase rotate-[-45deg] mt-4">H. Chính</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-6 flex items-center gap-2">
                <span class="material-icons text-amber-500">warning</span>
                Vật tư sắp hết
            </h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                        <span class="text-sm font-medium">Bìa sơ mi lá lỗ</span>
                    </div>
                    <span class="text-xs font-bold text-red-500">Chỉ còn 3 xấp</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                        <span class="text-sm font-medium">Pin AA tốt</span>
                    </div>
                    <span class="text-xs font-bold text-amber-500">Còn 12 cặp</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                        <span class="text-sm font-medium">Giấy A4 80gsm</span>
                    </div>
                    <span class="text-xs font-bold text-red-500">Chỉ còn 5 gram</span>
                </div>
                <button
                    class="w-full mt-2 py-2 border border-dashed border-slate-300 dark:border-slate-700 text-slate-500 text-sm rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                    Xem tất cả cảnh báo
                </button>
            </div>
        </div>
    </div>

    <div
        class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div
            class="p-6 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider text-sm">Chi tiết sử dụng:
                    KHOA CẤP CỨU</h3>
                <span
                    class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-[10px] font-bold rounded">THÁNG
                    06/2024</span>
            </div>
            <div class="relative">
                <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                <input
                    class="pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-primary focus:border-primary text-sm w-full md:w-64"
                    placeholder="Tìm kiếm hàng hóa..." type="text" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Stt</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Tên hàng</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            ĐVT</th>
                        <th
                            class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">
                            Số Lượng</th>
                        <th
                            class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">
                            Đơn giá</th>
                        <th
                            class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">
                            Thành Tiền</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    <tr class="bg-slate-100 dark:bg-slate-800/80">
                        <td class="px-6 py-2 text-xs font-bold text-primary" colspan="6">VĂN PHÒNG PHẨM - NHÀ SÁCH THANH VÂN
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-3 text-sm font-medium text-slate-500">01</td>
                        <td class="px-6 py-3 text-sm font-medium text-slate-900 dark:text-white">Bìa sơ mi lá lỗ cung tròn
                        </td>
                        <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400">xấp</td>
                        <td class="px-6 py-3 text-sm font-bold text-red-600 dark:text-red-400 text-center">1</td>
                        <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400 text-right">25.000 VNĐ</td>
                        <td class="px-6 py-3 text-sm font-bold text-slate-900 dark:text-white text-right">25.000 VNĐ</td>
                    </tr>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-3 text-sm font-medium text-slate-500">17</td>
                        <td class="px-6 py-3 text-sm font-medium text-slate-900 dark:text-white">Kim bấm No.10</td>
                        <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400">hộp</td>
                        <td class="px-6 py-3 text-sm font-bold text-red-600 dark:text-red-400 text-center">2</td>
                        <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400 text-right">2.800 VNĐ</td>
                        <td class="px-6 py-3 text-sm font-bold text-slate-900 dark:text-white text-right">5.600 VNĐ</td>
                    </tr>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-3 text-sm font-medium text-slate-500">39</td>
                        <td class="px-6 py-3 text-sm font-medium text-slate-900 dark:text-white">Hồ dán Queen</td>
                        <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400">chai</td>
                        <td class="px-6 py-3 text-sm font-bold text-red-600 dark:text-red-400 text-center">30</td>
                        <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400 text-right">4.200 VNĐ</td>
                        <td class="px-6 py-3 text-sm font-bold text-slate-900 dark:text-white text-right">126.000 VNĐ</td>
                    </tr>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-3 text-sm font-medium text-slate-500">63</td>
                        <td class="px-6 py-3 text-sm font-medium text-slate-900 dark:text-white">Pin AA tốt</td>
                        <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400">cặp</td>
                        <td class="px-6 py-3 text-sm font-bold text-red-600 dark:text-red-400 text-center">3</td>
                        <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400 text-right">4.800 VNĐ</td>
                        <td class="px-6 py-3 text-sm font-bold text-slate-900 dark:text-white text-right">14.400 VNĐ</td>
                    </tr>
                    <tr class="bg-slate-100 dark:bg-slate-800/80">
                        <td class="px-6 py-2 text-xs font-bold text-primary" colspan="6">VẬT TƯ TIÊU HAO - NHÀ SÁCH QUỐC NAM
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-3 text-sm font-medium text-slate-500">171</td>
                        <td class="px-6 py-3 text-sm font-medium text-slate-900 dark:text-white">Ly nhựa trung 380ml</td>
                        <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400">cây</td>
                        <td class="px-6 py-3 text-sm font-bold text-red-600 dark:text-red-400 text-center">1</td>
                        <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400 text-right">13.000 VNĐ</td>
                        <td class="px-6 py-3 text-sm font-bold text-slate-900 dark:text-white text-right">13.000 VNĐ</td>
                    </tr>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-3 text-sm font-medium text-slate-500">172</td>
                        <td class="px-6 py-3 text-sm font-medium text-slate-900 dark:text-white">Ly rau câu</td>
                        <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400">cây</td>
                        <td class="px-6 py-3 text-sm font-bold text-red-600 dark:text-red-400 text-center">1</td>
                        <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400 text-right">10.000 VNĐ</td>
                        <td class="px-6 py-3 text-sm font-bold text-slate-900 dark:text-white text-right">10.000 VNĐ</td>
                    </tr>
                    <tr class="bg-primary/5 dark:bg-primary/10 font-bold">
                        <td class="px-6 py-4 text-right text-sm text-slate-900 dark:text-white uppercase" colspan="5">Tổng
                            cộng phiếu này:</td>
                        <td class="px-6 py-4 text-right text-lg text-primary">194,000 VNĐ</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <span class="text-xs text-slate-500 dark:text-slate-400">Hiển thị 1 - 6 trên tổng số 24 mặt hàng</span>
            <div class="flex gap-2">
                <button
                    class="w-8 h-8 flex items-center justify-center rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50">
                    <span class="material-icons text-sm">chevron_left</span>
                </button>
                <button
                    class="w-8 h-8 flex items-center justify-center rounded border border-primary bg-primary text-white">1</button>
                <button
                    class="w-8 h-8 flex items-center justify-center rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50">2</button>
                <button
                    class="w-8 h-8 flex items-center justify-center rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50">3</button>
                <button
                    class="w-8 h-8 flex items-center justify-center rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50">
                    <span class="material-icons text-sm">chevron_right</span>
                </button>
            </div>
        </div>
    </div>
@endsection