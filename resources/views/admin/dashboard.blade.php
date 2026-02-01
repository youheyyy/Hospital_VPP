@extends('layouts.admin')

@section('title', 'Admin - Trung Tâm Điều Khiển | Hệ Thống Vật Tư Y Tế')

@section('page-title', 'Trung Tâm Điều Khiển')

@section('content')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
    }
    .dark .glass-card {
        background: rgba(16, 25, 34, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .asymmetric-grid {
        display: grid;
        grid-template-columns: 280px 1fr 320px;
        gap: 1.5rem;
    }
    @media (max-width: 1280px) {
        .asymmetric-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="mb-8">
    <h1 class="text-3xl font-bold tracking-tight">Trung Tâm Điều Khiển</h1>
    <p class="text-[#617589] mt-1">Giám sát tồn kho vật tư y tế và hoạt động các khoa phòng theo thời gian thực.</p>
</div>

<div class="asymmetric-grid">
    <!-- Left Sidebar: Status Cards -->
    <div class="flex flex-col gap-6">
        <div class="glass-card rounded-xl p-6 relative overflow-hidden group hover:translate-y-[-4px] transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-primary/10 text-primary rounded-lg">
                    <span class="material-symbols-outlined">assignment_late</span>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 bg-primary text-white rounded">Khẩn cấp</span>
            </div>
            <p class="text-sm font-medium text-[#617589]">Phiếu chờ duyệt</p>
            <div class="flex items-end gap-2 mt-1">
                <h3 class="text-4xl font-bold">{{ $pendingRequests }}</h3>
                <span class="text-xs font-bold text-green-500 mb-1">+12% ↑</span>
            </div>
            <div class="mt-4 h-12 w-full opacity-50">
                <svg class="w-full h-full text-primary" preserveAspectRatio="none" viewBox="0 0 100 40">
                    <path d="M0,35 Q10,10 20,25 T40,15 T60,30 T80,10 T100,20" fill="none" stroke="currentColor" stroke-width="2" vector-effect="non-scaling-stroke"/>
                </svg>
            </div>
        </div>

        <div class="glass-card rounded-xl p-6 group hover:translate-y-[-4px] transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-purple-500/10 text-purple-500 rounded-lg">
                    <span class="material-symbols-outlined">vitals</span>
                </div>
            </div>
            <p class="text-sm font-medium text-[#617589]">Khoa/Phòng hoạt động</p>
            <div class="flex items-end gap-2 mt-1">
                <h3 class="text-4xl font-bold">{{ $totalDepartments }}</h3>
                <span class="text-xs font-bold text-green-500 mb-1">Hoạt động</span>
            </div>
            <div class="mt-6 flex gap-1 items-center">
                <div class="h-1 flex-1 bg-purple-500 rounded-full"></div>
                <div class="h-1 flex-1 bg-purple-500 rounded-full"></div>
                <div class="h-1 flex-1 bg-purple-500 rounded-full opacity-30"></div>
                <div class="h-1 flex-1 bg-purple-500 rounded-full opacity-30"></div>
                <span class="text-[10px] font-bold text-[#617589] ml-2">Tải cao</span>
            </div>
        </div>

        <div class="glass-card rounded-xl p-6 group hover:translate-y-[-4px] transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-orange-500/10 text-orange-500 rounded-lg">
                    <span class="material-symbols-outlined">local_shipping</span>
                </div>
            </div>
            <p class="text-sm font-medium text-[#617589]">Tổng đơn hàng</p>
            <div class="flex items-end gap-2 mt-1">
                <h3 class="text-4xl font-bold">{{ number_format($totalOrders) }}</h3>
                <span class="text-xs font-bold text-green-500 mb-1">+5% ↑</span>
            </div>
            <p class="text-[10px] text-[#617589] mt-4 flex items-center gap-1">
                <span class="material-symbols-outlined text-xs">schedule</span> Trung bình: 14 phút
            </p>
        </div>
    </div>

    <!-- Center: Main Trends Chart -->
    <div class="glass-card rounded-xl p-8 flex flex-col">
        <div class="flex justify-between items-start mb-8">
            <div>
                <h2 class="text-xl font-bold">Xu hướng yêu cầu theo khoa</h2>
                <p class="text-sm text-[#617589]">Phân tích cho tất cả các khoa hoạt động</p>
            </div>
            <div class="flex gap-2">
                <button class="px-3 py-1.5 text-xs font-semibold bg-[#f0f2f4] dark:bg-white/10 rounded-lg">Tuần</button>
                <button class="px-3 py-1.5 text-xs font-semibold bg-primary text-white rounded-lg">Tháng</button>
            </div>
        </div>

        <div class="flex items-center gap-10 mb-8">
            <div>
                <p class="text-xs text-[#617589] font-medium uppercase tracking-wider">Tổng khối lượng</p>
                <p class="text-3xl font-bold mt-1">{{ number_format($totalOrders) }} <span class="text-sm font-normal text-green-500">+8.4%</span></p>
            </div>
            <div class="h-10 w-[1px] bg-[#f0f2f4] dark:bg-white/10"></div>
            <div>
                <p class="text-xs text-[#617589] font-medium uppercase tracking-wider">Thời gian xử lý TB</p>
                <p class="text-3xl font-bold mt-1">4.2h <span class="text-sm font-normal text-green-500">-12%</span></p>
            </div>
        </div>

        <div class="flex-1 relative min-h-[300px]">
            <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 800 300">
                <defs>
                    <linearGradient id="chartGradient" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#2b8cee" stop-opacity="0.2"/>
                        <stop offset="100%" stop-color="#2b8cee" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <!-- Grid lines -->
                <line class="text-[#f0f2f4] dark:text-white/5" stroke="currentColor" stroke-width="0.5" x1="0" x2="800" y1="0" y2="0"/>
                <line class="text-[#f0f2f4] dark:text-white/5" stroke="currentColor" stroke-width="0.5" x1="0" x2="800" y1="100" y2="100"/>
                <line class="text-[#f0f2f4] dark:text-white/5" stroke="currentColor" stroke-width="0.5" x1="0" x2="800" y1="200" y2="200"/>
                <line class="text-[#f0f2f4] dark:text-white/5" stroke="currentColor" stroke-width="0.5" x1="0" x2="800" y1="300" y2="300"/>
                <!-- Area -->
                <path d="M0,250 C100,230 150,150 200,160 S300,240 400,180 S550,50 650,80 S800,40 800,40 V300 H0 Z" fill="url(#chartGradient)"/>
                <!-- Line -->
                <path d="M0,250 C100,230 150,150 200,160 S300,240 400,180 S550,50 650,80 S800,40 800,40" fill="none" stroke="#2b8cee" stroke-linecap="round" stroke-width="3"/>
                <!-- Custom Tooltip Node -->
                <circle cx="580" cy="62" fill="#2b8cee" r="6" stroke="white" stroke-width="2"/>
            </svg>
            <!-- Floating Tooltip Card -->
            <div class="absolute left-[560px] top-[10px] glass-card p-3 rounded-lg shadow-xl border-primary/20 scale-90">
                <p class="text-[10px] text-[#617589] font-bold">T10 24</p>
                <p class="text-sm font-bold">Khoa Phẫu Thuật</p>
                <p class="text-primary font-bold">242 Yêu cầu</p>
            </div>
        </div>

        <div class="flex justify-between mt-4 px-2">
            <span class="text-xs font-bold text-[#617589]">T2</span>
            <span class="text-xs font-bold text-[#617589]">T3</span>
            <span class="text-xs font-bold text-[#617589]">T4</span>
            <span class="text-xs font-bold text-[#617589]">T5</span>
            <span class="text-xs font-bold text-[#617589]">T6</span>
            <span class="text-xs font-bold text-[#617589]">T7</span>
            <span class="text-xs font-bold text-[#617589]">CN</span>
        </div>
    </div>

    <!-- Right: Top Products Donut Feed -->
    <div class="flex flex-col gap-4">
        <div class="glass-card rounded-xl p-6 flex-1">
            <h2 class="text-lg font-bold mb-6">Được yêu cầu nhiều nhất</h2>
            <div class="space-y-6">
                @php
                    $colors = ['primary', 'purple-500', 'orange-500', 'green-500'];
                @endphp
                @forelse($topProducts as $index => $product)
                    <div class="flex items-center gap-4">
                        <div class="relative size-12 flex-shrink-0">
                            <svg class="size-full" viewBox="0 0 36 36">
                                <path class="text-[#f0f2f4] dark:text-white/5" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3"/>
                                <path class="text-{{ $colors[$index % 4] }}" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-dasharray="{{ $product['percentage'] }}, 100" stroke-linecap="round" stroke-width="3"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center text-[10px] font-bold">{{ $product['percentage'] }}%</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate">{{ $product['name'] }}</p>
                            <p class="text-xs text-[#617589]">{{ $product['department'] }}</p>
                        </div>
                        <span class="material-symbols-outlined text-{{ $colors[$index % 4] }} text-sm">trending_{{ $product['trend'] }}</span>
                    </div>
                @empty
                    <p class="text-sm text-[#617589] text-center py-8">Chưa có dữ liệu</p>
                @endforelse
            </div>

            <div class="mt-8 pt-6 border-t border-[#f0f2f4] dark:border-white/10">
                <button class="w-full py-3 bg-primary/10 text-primary font-bold text-sm rounded-xl hover:bg-primary hover:text-white transition-all">
                    Xem báo cáo tồn kho
                </button>
            </div>
        </div>

        <!-- Micro Performance Card -->
        <div class="bg-primary p-6 rounded-xl text-white shadow-xl shadow-primary/20">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-sm font-bold opacity-80 uppercase tracking-tighter">Hiệu suất hệ thống</h4>
                <span class="material-symbols-outlined text-sm">bolt</span>
            </div>
            <p class="text-2xl font-bold">99.2%</p>
            <p class="text-[10px] opacity-70 mt-1">Thời gian hoạt động trên tất cả các khoa</p>
        </div>
    </div>
</div>

<!-- Bottom Section: Detailed Feed -->
<div class="mt-8">
    <div class="glass-card rounded-xl p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold">Hoạt động gần đây</h3>
            <button class="text-sm text-primary font-semibold">Xem tất cả</button>
        </div>
        <div class="space-y-4">
            @forelse($recentActivities as $activity)
                <div class="flex items-center justify-between py-3 border-b border-[#f0f2f4] dark:border-white/5 last:border-0">
                    <div class="flex items-center gap-4">
                        <div class="size-8 rounded-full bg-{{ $activity['color'] }}-500/20 text-{{ $activity['color'] }}-500 flex items-center justify-center">
                            <span class="material-symbols-outlined text-sm">{{ $activity['icon'] }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold">{{ $activity['title'] }}</p>
                            <p class="text-xs text-[#617589]">{{ $activity['department'] }} • {{ $activity['time'] }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-[#617589]">{{ $activity['reference'] }}</span>
                </div>
            @empty
                <p class="text-sm text-[#617589] text-center py-8">Chưa có hoạt động nào</p>
            @endforelse
        </div>
    </div>
</div>
@endsection