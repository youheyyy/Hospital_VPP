@extends('layouts.admin')

@section('title', 'Quản lý Sản phẩm VPP')
@section('page-title', 'Quản lý Sản phẩm Văn phòng phẩm')

@section('content')
<div class="space-y-8 animate-in fade-in duration-500">
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <!-- Left: Stats Cards -->
        <div class="flex flex-col md:flex-row items-center gap-4 flex-1">
            <div
                class="bg-white px-6 py-4 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all flex items-center gap-4 group min-w-[240px]">
                <div
                    class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform shadow-sm shadow-indigo-100">
                    <span class="material-symbols-outlined text-[24px]">inventory_2</span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Tổng sản phẩm</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ number_format($totalProducts) }}
                        </h3>
                        <span
                            class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-lg border border-emerald-100">+12%</span>
                    </div>
                </div>
            </div>

            <div
                class="bg-white px-6 py-4 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all flex items-center gap-4 group min-w-[240px]">
                <div
                    class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center text-violet-600 group-hover:scale-110 transition-transform shadow-sm shadow-violet-100">
                    <span class="material-symbols-outlined text-[24px]">hub</span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Nhà cung cấp</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ number_format($totalSuppliers)
                            }}</h3>
                        <span
                            class="text-[9px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded-lg border border-indigo-100">Hoạt
                            động</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Buttons -->
        <div class="flex items-center gap-3">
            <button onclick="openModal('addCategoryModal')"
                class="flex items-center gap-2 px-5 py-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-50 transition-all shadow-sm hover:border-primary/30">
                <span class="material-symbols-outlined text-[18px]">add_business</span>
                Thêm nhà cung cấp
            </button>
            <button onclick="openModal('addProductModal')"
                class="flex items-center gap-2 px-6 py-3 rounded-2xl bg-primary text-white font-black text-xs hover:bg-primary/90 transition-all shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-95 leading-none">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                Thêm mới vật tư
            </button>
        </div>
    </div>

    <!-- Product List Grouped by Category (Supplier) -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 z-10">
                    <tr
                        class="bg-white/80 backdrop-blur-md text-slate-500 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200 shadow-sm">
                        <th class="px-4 py-5 w-12 text-center border-r border-slate-100">STT</th>
                        <th class="px-4 py-5 border-r border-slate-100 min-w-[320px] relative">
                            <div class="flex items-center gap-3">
                                <span class="whitespace-nowrap">Tên hàng</span>
                                <div class="relative flex-1 group/search">
                                    <input type="text" id="headerSearch" placeholder="Tìm kiếm & lọc..."
                                        onkeyup="filterProducts()" onclick="toggleFilter(event)"
                                        class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 bg-white shadow-sm focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-[11px] font-bold uppercase tracking-normal placeholder:capitalize placeholder:font-normal outline-none">
                                    <span
                                        class="material-symbols-outlined absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 text-sm group-focus-within/search:text-primary transition-colors">search</span>
                                </div>
                            </div>

                            <!-- Filter Dropdown -->
                            <div id="filterDropdown"
                                style="display: none; position: absolute; top: 100%; left: 1rem; z-index: 1000; background: white; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1); width: calc(100% - 2rem); min-width: 320px; max-height: 400px; overflow: hidden; margin-top: 8px;">
                                <div style="max-height: 320px; overflow-y: auto; padding: 12px;"
                                    class="custom-scrollbar">
                                    <!-- Select All -->
                                    <label
                                        class="flex items-center px-3 py-2.5 hover:bg-slate-50 cursor-pointer rounded-xl transition-colors group">
                                        <input type="checkbox" id="selectAllFilter" checked
                                            onchange="toggleAllFilters(this)"
                                            class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary/20">
                                        <span
                                            class="ml-3 text-sm font-bold text-slate-700 group-hover:text-primary transition-colors">(Tất
                                            cả)</span>
                                    </label>

                                    <div class="my-2 border-t border-slate-100"></div>

                                    <!-- Product List -->
                                    <div id="filterProductList" class="space-y-0.5">
                                        @foreach($categories as $category)
                                        @foreach($category->products as $product)
                                        <label
                                            class="filter-option flex items-center px-3 py-2 hover:bg-slate-50 cursor-pointer rounded-xl transition-colors group"
                                            data-product-name="{{ strtolower($product->name) }}">
                                            <input type="checkbox" checked
                                                class="product-filter-checkbox w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary/20"
                                                data-product-id="{{ $product->id }}" onchange="applyFilter()">
                                            <span
                                                class="ml-3 text-sm text-slate-600 group-hover:text-slate-900 transition-colors">{{
                                                $product->name }}</span>
                                        </label>
                                        @endforeach
                                        @endforeach
                                    </div>
                                </div>
                                <div class="px-4 py-3 bg-slate-50/50 border-t border-slate-100 flex justify-end">
                                    <button type="button" onclick="closeFilter()"
                                        class="px-4 py-1.5 text-xs font-bold text-slate-500 hover:text-primary hover:bg-white border border-transparent hover:border-slate-200 rounded-lg transition-all">Đóng</button>
                                </div>
                            </div>
                        </th>
                        <th class="px-4 py-5 w-24 text-center border-r border-slate-100">ĐVT</th>
                        <th class="px-4 py-5 w-28 text-center border-r border-slate-100 font-bold text-[10px] text-emerald-600">Biểu mẫu</th>
                        <th class="px-4 py-5 w-24 text-center border-r border-slate-100 font-bold text-[10px] text-blue-600">Khổ giấy</th>
                        <th class="px-4 py-5 w-56 border-r border-slate-100">Nhà cung cấp</th>
                        <th class="px-4 py-5 w-36 text-right border-r border-slate-100">Đơn giá (VND)</th>
                        <th class="px-4 py-5 w-32 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($categories as $category)
                    <!-- Category Header -->
                    <tr class="bg-slate-50/20 border-t-4 border-white border-b border-slate-200 category-row"
                        data-category-id="{{ $category->id }}">
                        <td colspan="8" class="px-6 py-6 font-bold">
                            <div class="flex items-center justify-between group/header"
                                x-data="{ isEditing: false, categoryName: @js($category->name) }">
                                <div class="flex items-center gap-4 flex-1">
                                    <div
                                        class="w-10 h-10 rounded-2xl bg-white shadow-sm border border-slate-100 flex items-center justify-center text-indigo-600">
                                        <span class="material-symbols-outlined text-[20px]">business</span>
                                    </div>
                                    <div class="flex-1">
                                        <span x-show="!isEditing" @dblclick="isEditing = true"
                                            class="text-[14px] font-black text-slate-900 uppercase tracking-wider cursor-pointer hover:text-indigo-600 transition-colors truncate max-w-xl block">
                                            {{ $category->name }}
                                        </span>
                                        <input x-show="isEditing" x-model="categoryName" @click.away="isEditing = false"
                                            @keydown.enter="updateCategoryName('{{ $category->id }}', categoryName); isEditing = false"
                                            @keydown.escape="isEditing = false"
                                            class="bg-white border-2 border-indigo-400 rounded-xl px-4 py-1.5 text-sm font-bold text-slate-900 w-full max-w-md focus:ring-4 focus:ring-indigo-50 outline-none"
                                            x-init="$watch('isEditing', value => value && $nextTick(() => $el.focus()))">
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="isEditing = !isEditing"
                                        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white shadow-sm border border-slate-200 text-slate-600 hover:text-indigo-600 hover:border-indigo-300 transition-all text-xs font-bold">
                                        <span class="material-symbols-outlined text-sm font-bold"
                                            x-text="isEditing ? 'close' : 'edit'"></span>
                                        <span x-text="isEditing ? 'Hủy' : 'Sửa tên'"></span>
                                    </button>
                                    <button onclick="deleteCategory({{ $category->id }}, @js($category->name))"
                                        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white shadow-sm border border-slate-200 text-rose-500 hover:bg-rose-50 hover:border-rose-200 transition-all text-xs font-bold">
                                        <span class="material-symbols-outlined text-sm font-bold">delete</span>
                                        <span>Xóa Nhà Cung Cấp</span>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>

                    @forelse ($category->products as $index => $product)
                    <tr class="hover:bg-slate-50/50 transition-colors group border-b border-slate-200 last:border-0 product-row"
                        data-category-id="{{ $category->id }}" data-product-id="{{ $product->id }}">
                        <td class="px-4 py-4 border-r border-slate-100 text-center">
                            <span class="text-[11px] font-bold text-slate-400">{{ str_pad($index + 1, 2, '0',
                                STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-4 py-4 border-r border-slate-100">
                            <input type="text" value="{{ $product->name }}"
                                onblur="updateProductName({{ $product->id }}, this.value)"
                                class="w-full bg-transparent border-none focus:ring-0 text-sm font-bold text-slate-700 hover:bg-slate-100 rounded-lg px-2 py-1 transition-all outline-none"
                                title="{{ $product->name }}">
                        </td>
                        <td class="px-4 py-4 border-r border-slate-100 text-center">
                            <input type="text" value="{{ $product->unit }}"
                                onblur="updateProductUnit({{ $product->id }}, this.value)"
                                class="w-16 mx-auto bg-transparent border-none focus:ring-0 text-[10px] font-extrabold text-slate-500 bg-slate-50/50 px-2 py-1 rounded-lg uppercase tracking-wider text-center hover:bg-slate-100 transition-all outline-none">
                        </td>
                        <td class="px-4 py-4 border-r border-slate-100 text-center">
                            <label class="relative inline-flex items-center cursor-pointer group">
                                <input type="checkbox" class="sr-only peer" {{ $product->is_form ? 'checked' : '' }}
                                    onchange="updateProductIsForm({{ $product->id }}, this.checked)">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </td>
                        <td class="px-4 py-4 border-r border-slate-100 text-center" x-data="{ isForm: {{ $product->is_form ? 'true' : 'false' }} }" @update-is-form.window="if($event.detail.id == {{ $product->id }}) isForm = $event.detail.value">
                            <select x-show="isForm" onchange="updateProductPaperSize({{ $product->id }}, this.value)"
                                class="bg-blue-50/50 border-blue-100 rounded-lg text-[10px] font-bold text-blue-700 px-2 py-1 focus:ring-2 focus:ring-blue-100 outline-none border">
                                <option value="" {{ is_null($product->paper_size) ? 'selected' : '' }}>-</option>
                                <option value="A3" {{ $product->paper_size == 'A3' ? 'selected' : '' }}>A3</option>
                                <option value="A4" {{ $product->paper_size == 'A4' ? 'selected' : '' }}>A4</option>
                                <option value="A5" {{ $product->paper_size == 'A5' ? 'selected' : '' }}>A5</option>
                            </select>
                            <span x-show="!isForm" class="text-slate-300 text-[10px]">-</span>
                        </td>
                        <td class="px-4 py-4 text-sm border-r border-slate-100">
                            <select onchange="updateProductCategory({{ $product->id }}, this.value)"
                                class="bg-indigo-50/30 border-indigo-100/50 rounded-xl text-[11px] font-black text-indigo-700 px-3 py-1.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-300 cursor-pointer transition-all max-w-[190px] truncate outline-none border shadow-sm">
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $cat->id == $category->id ? 'selected' : '' }}>{{
                                    $cat->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-4 py-4 text-right border-r border-slate-100">
                            <div class="flex items-center justify-end gap-2">
                                <input type="text" value="{{ number_format($product->price) }}"
                                    onfocus="this.value = '{{ $product->price }}'"
                                    onblur="updateProductPrice({{ $product->id }}, this)"
                                    class="w-28 text-right bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 px-3 py-1.5 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-400 transition-all shadow-sm">
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="deleteProduct({{ $product->id }}, {{ json_encode($product->name) }})"
                                    class="px-3 py-1.5 rounded-lg text-rose-500 hover:bg-rose-50 border border-slate-100 hover:border-rose-200 transition-all text-xs font-bold flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">delete</span>
                                    Xóa
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400 italic text-sm">Chưa có sản phẩm nào
                            cho nhà cung cấp này.</td>
                    </tr>
                    @endforelse
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Thêm Nhà Cung Cấp -->
<div id="addCategoryModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm"
            onclick="closeModal('addCategoryModal')"></div>
        <div
            class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-slate-900 rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('admin.category.store') }}" method="POST">
                @csrf
                <div class="px-6 py-6">
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">add_business</span>
                        Thêm Nhà cung cấp mới
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5">Tên nhà
                                cung cấp</label>
                            <input type="text" name="name" required placeholder="Nhập tên nhà cung cấp..."
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 flex justify-end gap-3">
                    <button type="button" onclick="closeModal('addCategoryModal')"
                        class="px-5 py-2 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">Hủy</button>
                    <button type="submit"
                        class="px-5 py-2 rounded-xl text-sm font-bold bg-primary text-white hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">Lưu
                        lại</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Thêm Sản Phẩm -->
<div id="addProductModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm"
            onclick="closeModal('addProductModal')"></div>
        <div
            class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-slate-900 rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('admin.products.store') }}" method="POST">
                @csrf
                <div class="px-6 py-6">
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">add_circle</span>
                        Thêm mới vật tư
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5">Tên hàng
                                hóa</label>
                            <input type="text" name="name" required placeholder="Ví dụ: Giấy in A4 Double A..."
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5">Đơn vị
                                    tính</label>
                                <input type="text" name="unit" required placeholder="Ram, Cây, Cái..."
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5">Đơn giá
                                    (VNĐ)</label>
                                <input type="number" name="price" required placeholder="0"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5">Nhà cung
                                cấp</label>
                            <select name="category_id" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 flex justify-end gap-3">
                    <button type="button" onclick="closeModal('addProductModal')"
                        class="px-5 py-2 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">Hủy</button>
                    <button type="submit"
                        class="px-5 py-2 rounded-xl text-sm font-bold bg-primary text-white hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">Thêm
                        sản phẩm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Xác Nhận Xóa -->
<div id="deleteConfirmModal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm"
            onclick="closeModal('deleteConfirmModal')"></div>
        <div
            class="inline-block overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-900 rounded-2xl shadow-2xl sm:max-w-md w-full relative z-10">
            <div class="px-6 py-8 text-center">
                <div class="w-20 h-20 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="material-symbols-outlined text-rose-500 text-[40px]">delete_forever</span>
                </div>
                <h3 id="deleteModalTitle" class="text-xl font-black text-slate-800 dark:text-white mb-2">Xác nhận xóa?
                </h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm mb-8 leading-relaxed">
                    Bạn có chắc chắn muốn xóa <span id="deleteItemType">sản phẩm</span> <br>
                    <span id="deleteProductName" class="font-bold text-slate-800 dark:text-slate-200"></span>? <br>
                    Hành động này không thể hoàn tác.
                </p>
                <div class="flex flex-col gap-3">
                    <button id="btnConfirmDelete"
                        class="w-full py-3 rounded-xl bg-rose-500 text-white font-bold text-sm hover:bg-rose-600 transition-all shadow-lg shadow-rose-200">
                        Xác nhận xóa vĩnh viễn
                    </button>
                    <button onclick="closeModal('deleteConfirmModal')"
                        class="w-full py-3 rounded-xl bg-slate-100 text-slate-600 font-bold text-sm hover:bg-slate-200 transition-all">
                        Hủy bỏ
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function updateProductIsForm(id, isChecked) {
        fetch('{{ route("admin.products.update-is-form") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ product_id: id, is_form: isChecked })
        }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Dispatch event to update paper size visibility in the same row
                    window.dispatchEvent(new CustomEvent('update-is-form', { detail: { id: id, value: isChecked } }));
                }
            });
    }

    function updateProductPaperSize(id, size) {
        fetch('{{ route("admin.products.update-paper-size") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ product_id: id, paper_size: size })
        }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Success feedback
                }
            });
    }

    function updateCategoryName(id, newName) {
        if (newName && newName.trim() !== '') {
            fetch('{{ route("admin.category.update-name") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    category_id: id,
                    name: newName
                })
            }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
        }
    }

    function updateProductPrice(id, input) {
        const price = input.value.replace(/,/g, '');
        if (isNaN(price)) {
            alert('Vui lòng nhập số hợp lệ');
            return;
        }

        fetch('{{ route("admin.products.update-price") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                product_id: id,
                price: price
            })
        }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.value = new Intl.NumberFormat().format(price);
                }
            });
    }

    function updateProductName(id, newName) {
        if (!newName || newName.trim() === '') return;
        fetch('{{ route("admin.products.update-name") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ product_id: id, name: newName })
        }).then(response => response.json())
            .then(data => {
                if (data.success) { }
            });
    }

    function updateProductUnit(id, newUnit) {
        if (!newUnit || newUnit.trim() === '') return;
        fetch('{{ route("admin.products.update-unit") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ product_id: id, unit: newUnit })
        }).then(response => response.json())
            .then(data => {
                if (data.success) { }
            });
    }

    let productToDelete = null;
    let categoryToDelete = null;

    function deleteProduct(id, name) {
        productToDelete = id;
        categoryToDelete = null;
        document.getElementById('deleteModalTitle').textContent = 'Xóa sản phẩm?';
        document.getElementById('deleteItemType').textContent = 'sản phẩm';
        document.getElementById('deleteProductName').textContent = name;
        openModal('deleteConfirmModal');

        const btnDelete = document.getElementById('btnConfirmDelete');
        btnDelete.onclick = confirmDelete;
    }

    function deleteCategory(id, name) {
        categoryToDelete = id;
        productToDelete = null;
        document.getElementById('deleteModalTitle').textContent = 'Xóa nhà cung cấp?';
        document.getElementById('deleteItemType').textContent = 'nhà cung cấp';
        document.getElementById('deleteProductName').textContent = name;
        openModal('deleteConfirmModal');

        const btnDelete = document.getElementById('btnConfirmDelete');
        btnDelete.onclick = confirmDelete;
    }

    function confirmDelete() {
        const btnDelete = document.getElementById('btnConfirmDelete');
        btnDelete.disabled = true;
        btnDelete.innerHTML = '<span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span> Đang xóa...';

        const url = productToDelete ? `/admin/products/${productToDelete}` : `/admin/category/${categoryToDelete}`;

        fetch(url, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi xóa');
                    closeModal('deleteConfirmModal');
                    btnDelete.disabled = false;
                    btnDelete.innerHTML = 'Xác nhận xóa vĩnh viễn';
                }
            });
    }

    function updateProductCategory(id, categoryId) {
        fetch('{{ route("admin.products.update-category") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                product_id: id,
                category_id: categoryId
            })
        }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
    }

    // --- FILTER DROPDOWN FUNCTIONS ---
    function toggleFilter(event) {
        event.stopPropagation();
        const dropdown = document.getElementById('filterDropdown');
        if (dropdown.style.display === 'none' || dropdown.style.display === '') {
            dropdown.style.display = 'block';
        }
    }

    function closeFilter() {
        const dropdown = document.getElementById('filterDropdown');
        if (dropdown) dropdown.style.display = 'none';
    }

    function filterProducts() {
        const searchValue = document.getElementById('headerSearch').value.toLowerCase().trim();
        const filterOptions = document.querySelectorAll('.filter-option');

        filterOptions.forEach(option => {
            const productName = option.dataset.productName;
            const matches = productName.includes(searchValue);
            option.style.display = matches ? 'flex' : 'none';

            const cb = option.querySelector('.product-filter-checkbox');
            if (searchValue !== '') {
                cb.checked = matches;
            } else {
                cb.checked = true;
            }
        });

        applyFilter();
    }

    function toggleAllFilters(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll('.product-filter-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = selectAllCheckbox.checked;
        });
        applyFilter();
    }

    function applyFilter() {
        const checkboxes = document.querySelectorAll('.product-filter-checkbox');
        const selectedProductIds = new Set();

        checkboxes.forEach(cb => {
            if (cb.checked) {
                selectedProductIds.add(cb.dataset.productId);
            }
        });

        const productRows = document.querySelectorAll('.product-row');
        productRows.forEach(row => {
            const productId = row.dataset.productId;
            if (selectedProductIds.has(productId)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        const categoryRows = document.querySelectorAll('tr.category-row');
        categoryRows.forEach(catRow => {
            const catId = catRow.dataset.categoryId;
            const hasVisibleProduct = Array.from(document.querySelectorAll(`.product-row[data-category-id="${catId}"]`))
                .some(row => row.style.display !== 'none');

            catRow.style.display = hasVisibleProduct ? '' : 'none';
        });

        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        const someChecked = Array.from(checkboxes).some(cb => cb.checked);
        const selectAllCheckbox = document.getElementById('selectAllFilter');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        }
    }

    document.addEventListener('click', function (event) {
        const dropdown = document.getElementById('filterDropdown');
        const headerSearch = document.getElementById('headerSearch');
        if (dropdown && !dropdown.contains(event.target) && !headerSearch.contains(event.target)) {
            closeFilter();
        }
    });
</script>
@endpush
@endsection