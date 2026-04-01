@extends('layouts.admin')

@section('title', 'Dashboard Quản trị VPP')

{{-- Header: tiêu đề + filter tháng + user --}}
@section('header-content')
    <div>
        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Dashboard Quản trị VPP</h1>
        <p class="text-xs text-slate-400 font-medium">Tháng {{ $selectedMonth }} • {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    <div class="flex items-center gap-4">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
            <label class="text-xs font-bold text-slate-600">Lọc theo tháng:</label>
            <select name="month" onchange="this.form.submit()"
                class="border-slate-300 rounded-2xl text-sm px-4 py-2 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @for($i = 0; $i < 12; $i++) @php $date = now()->subMonths($i);
                    $monthValue = $date->format('m/Y');
                    @endphp
                    <option value="{{ $monthValue }}" {{ $selectedMonth == $monthValue ? 'selected' : '' }}>
                        Tháng {{ $date->format('m/Y') }}
                    </option>
                    @endfor
            </select>
        </form>
        <div class="bg-slate-100 px-4 py-2 rounded-xl text-sm font-bold text-slate-600 flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">person</span>
            {{ auth()->user()->name }}
        </div>
    </div>
@endsection

{{-- CSS riêng cho dashboard --}}
@push('styles')
<style type="text/tailwindcss">
    .bento-card {
        @apply bg-white rounded-[2rem] border border-slate-100 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.05)] p-6 transition-all duration-300 hover:shadow-[0_20px_50px_-12px_rgba(0,0,0,0.08)];
    }
    .progress-ring {
        transition: stroke-dashoffset 0.35s;
        transform: rotate(-90deg);
        transform-origin: 50% 50%;
    }
    .horizontal-bar {
        @apply h-8 bg-indigo-100 rounded-lg relative overflow-hidden transition-all duration-500;
    }
    .horizontal-bar-fill {
        @apply h-full bg-indigo-600 rounded-lg transition-all duration-700;
    }
</style>
@endpush

@section('content')
    @php
        // Sử dụng selectedMonth từ controller

        // Tính tổng số sản phẩm trong kho
        $totalProducts = \App\Models\Product::where('is_active', true)->count();

        // Tính số khoa đã nhập liệu trong tháng hiện tại
        $totalDepartments = \App\Models\Department::where('is_active', true)->count();
        $departmentsWithOrders = \App\Models\MonthlyOrder::where('month', $selectedMonth)
            ->distinct('department_id')
            ->count('department_id');
        $progressPercentage = $totalDepartments > 0 ? round(($departmentsWithOrders / $totalDepartments) * 100) : 0;

        // Tính tổng số lượng sản phẩm yêu cầu trong tháng
        $monthlyProductCount = \App\Models\MonthlyOrder::where('month', $selectedMonth)->sum('quantity');

        // Lấy top 5 khoa có nhiều sản phẩm yêu cầu nhất (đếm số lượng sản phẩm khác nhau)
        $topDepartments = \App\Models\MonthlyOrder::where('month', $selectedMonth)
            ->select('department_id', \DB::raw('COUNT(DISTINCT product_id) as product_count'))
            ->groupBy('department_id')
            ->orderBy('product_count', 'DESC')
            ->limit(5)
            ->with('department')
            ->get();

        $maxProductCount = $topDepartments->max('product_count') ?? 1;

        // Lấy top sản phẩm được yêu cầu nhiều nhất
        $topProducts = \App\Models\MonthlyOrder::where('month', $selectedMonth)
            ->select('product_id', \DB::raw('SUM(quantity) as total_quantity'), \DB::raw('COUNT(DISTINCT department_id) as department_count'))
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'DESC')
            ->limit(5)
            ->with('product')
            ->get();

        // Lấy hoạt động gần đây
        $recentActivities = \App\Models\MonthlyOrder::with(['department', 'product'])
            ->orderBy('updated_at', 'DESC')
            ->limit(4)
            ->get();
    @endphp

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bento-card flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center">
                    <span class="material-symbols-outlined">database</span>
                </div>
                <span class="text-[10px] font-bold text-emerald-500 bg-emerald-50 px-2 py-1 rounded-full">Hoạt động</span>
            </div>
            <div class="mt-4">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Tổng sản phẩm</p>
                <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($totalProducts) }}</h3>
                <p class="text-[10px] text-slate-400 mt-1">Sản phẩm trong hệ thống</p>
            </div>
        </div>
        <div class="bento-card flex items-center justify-between">
            <div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Tiến độ nhập liệu</p>
                <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ $progressPercentage }}%</h3>
                <p class="text-[10px] text-slate-400 mt-1">{{ $departmentsWithOrders }}/{{ $totalDepartments }} Khoa phòng</p>
            </div>
            <div class="relative w-16 h-16">
                <svg class="w-full h-full" viewBox="0 0 36 36">
                    <circle class="stroke-slate-100" cx="18" cy="18" fill="none" r="16" stroke-width="4"></circle>
                    <circle class="stroke-indigo-600 progress-ring" cx="18" cy="18" fill="none" r="16"
                        stroke-dasharray="100" stroke-dashoffset="{{ 100 - $progressPercentage }}"
                        stroke-linecap="round" stroke-width="4"></circle>
                </svg>
            </div>
        </div>
        <div class="bento-card flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div class="w-12 h-12 bg-mint-50 text-mint-600 rounded-2xl flex items-center justify-center">
                    <span class="material-symbols-outlined">shopping_cart</span>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Yêu cầu tháng này</p>
                <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($monthlyProductCount) }}</h3>
                <p class="text-[10px] text-slate-400 mt-1">Tổng số lượng • {{ $selectedMonth }}</p>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
        <div class="lg:col-span-8 bento-card">
            <div class="flex justify-between items-center mb-10">
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900">Yêu cầu theo Khoa/Phòng</h3>
                    <p class="text-xs text-slate-400 font-medium">Top 5 khoa có nhiều yêu cầu nhất tháng {{ $selectedMonth }}</p>
                </div>
                <a href="{{ route('admin.consolidated') }}"
                    class="text-xs font-bold text-indigo-600 flex items-center gap-1">
                    Xem tất cả <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
            <div class="space-y-6">
                @forelse($topDepartments as $dept)
                @php
                    $percentage = $maxProductCount > 0 ? ($dept->product_count / $maxProductCount) * 100 : 0;
                @endphp
                <div class="grid grid-cols-12 items-center gap-4">
                    <div class="col-span-3 text-right">
                        <p class="text-xs font-bold text-slate-600 truncate">{{ $dept->department->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-span-7">
                        <div class="horizontal-bar">
                            <div class="horizontal-bar-fill" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs font-extrabold text-slate-900">{{ number_format($dept->product_count) }} SP</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-slate-400">
                    <p class="text-sm">Chưa có dữ liệu yêu cầu trong tháng này</p>
                </div>
                @endforelse
            </div>
        </div>
        <div class="lg:col-span-4 bento-card flex flex-col">
            <div class="w-full text-left mb-6">
                <h3 class="text-lg font-extrabold text-slate-900">Sản phẩm được yêu cầu</h3>
                <p class="text-xs text-slate-400 font-medium">Top 5 sản phẩm nhiều nhất tháng {{ $selectedMonth }}</p>
            </div>
            <div class="space-y-3 flex-1">
                @forelse($topProducts as $index => $item)
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50/50 border border-slate-100 hover:bg-slate-50 transition-colors">
                    <div class="flex-shrink-0 w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-sm">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-900 truncate">{{ $item->product->name ?? 'N/A' }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ $item->department_count }} khoa/phòng yêu cầu</p>
                    </div>
                    <div class="flex-shrink-0 text-right">
                        <p class="text-sm font-extrabold text-indigo-600">{{ number_format($item->total_quantity) }}</p>
                        <p class="text-[9px] text-slate-400 uppercase">{{ $item->product->unit ?? '' }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-slate-400">
                    <p class="text-sm">Chưa có dữ liệu sản phẩm</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="bento-card">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600">history</span>
                <h3 class="text-lg font-extrabold text-slate-900">Hoạt động gần đây từ các Khoa</h3>
            </div>
            <div class="flex items-center gap-1.5 px-3 py-1 bg-mint-50 text-mint-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-mint-100">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-mint-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-mint-500"></span>
                </span>
                Cập nhật
            </div>
        </div>
        <div class="flex gap-4 overflow-x-auto pb-4">
            @forelse($recentActivities as $activity)
            <div class="flex-shrink-0 w-80 bg-slate-50/50 p-4 rounded-2xl border border-slate-100 relative group">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded uppercase">
                        {{ Str::limit($activity->department->name ?? 'N/A', 15) }}
                    </span>
                    <span class="text-[10px] font-bold text-slate-400">{{ $activity->updated_at->diffForHumans() }}</span>
                </div>
                <p class="text-xs font-bold text-slate-800 line-clamp-1">{{ $activity->product->name ?? 'N/A' }}
                    ({{ $activity->quantity }} {{ $activity->product->unit ?? '' }})</p>
                <p class="text-[10px] text-slate-500 mt-1">Tháng {{ $activity->month }}</p>
            </div>
            @empty
            <div class="w-full text-center py-8 text-slate-400">
                <p class="text-sm">Chưa có hoạt động gần đây</p>
            </div>
            @endforelse
        </div>
    </div>
@endsection