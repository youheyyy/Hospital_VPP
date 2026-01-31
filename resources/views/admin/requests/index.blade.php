@extends('layouts.admin')

@section('title', 'Danh sách Yêu cầu Mua sắm (PR)')

@section('page-title', 'Danh sách Yêu cầu')

@section('content')
    <div
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm mb-6 -mx-8 -mt-8">
        <div
            class="px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-4">
                <div class="bg-blue-600 p-2.5 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-white text-2xl">description</span>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-slate-800 dark:text-white">Danh sách Phiếu Yêu cầu
                    </h1>
                    <p class="text-xs font-medium text-slate-500 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">apartment</span>
                        Yêu cầu từ các Khoa/Phòng
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                    <input type="text" placeholder="Tìm kiếm phiếu..."
                        class="pl-10 pr-4 py-2 text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-800 dark:border-slate-700 dark:text-white w-64">
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                        <th
                            class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center w-16">
                            STT</th>
                        <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest">Mã Phiếu</th>
                        <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest">Khoa / Phòng
                        </th>
                        <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest">Người yêu cầu
                        </th>
                        <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center">
                            Ngày tạo</th>
                        <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-right">
                            Tổng giá trị (dự kiến)</th>
                        <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center">
                            Trạng thái</th>
                        <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center">
                            Tác vụ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($requests as $index => $req)
                        @php
                            $total = $req->items->sum(function ($item) {
                                return $item->quantity_requested * ($item->product->unit_price ?? 0);
                            });
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-4 text-sm text-center text-slate-400 font-medium">
                                {{ $requests->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ $req->request_code }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-700 dark:text-slate-200">
                                    {{ $req->department->department_name ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-600 dark:text-slate-300">
                                    {{ $req->requester->full_name ?? $req->username ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="text-xs font-bold text-slate-500">
                                    {{ \Carbon\Carbon::parse($req->created_at)->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-sm font-black font-mono text-slate-800 dark:text-white">
                                    {{ number_format($total, 0, ',', '.') }} VNĐ
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($req->status == 'APPROVED')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Đã duyệt
                                    </span>
                                @elseif($req->status == 'SUBMITTED')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Chờ duyệt
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        {{ $req->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <!-- Currently no detailed view for single PR, maybe open modal or link to something. 
                                                              For now just a placeholder button or link to approve logic if pending. -->
                                @if($req->status == 'SUBMITTED')
                                    <a href="{{ route('admin.approve_summary_votes', ['dept_id' => $req->department_id]) }}"
                                        class="p-1.5 bg-yellow-50 text-yellow-600 rounded-lg hover:bg-yellow-100 transition-colors"
                                        title="Xem chi tiết">
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </a>
                                @else
                                    <button class="text-slate-400 cursor-not-allowed p-2" disabled>
                                        <span class="material-symbols-outlined text-[20px]">lock</span>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">inbox</span>
                                    <p>Không có yêu cầu nào.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
@endsection