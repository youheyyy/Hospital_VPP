@extends('layouts.admin')

@section('title', 'Quản Lý Sản Phẩm | Hệ Thống Vật Tư Y Tế')

@section('page-title', 'Quản lý Sản phẩm')

@section('content')
    <div x-data="{ 
        showAddModal: false, 
        showEditModal: false,
        showDeleteModal: false,
        selectedProduct: null,
        deleteUrl: '',
        editData: {
            id: '',
            name: '',
            category_id: '',
            unit: '',
            unit_price: ''
        },
        openEditModal(product) {
            this.selectedProduct = product;
            this.editData = {
                id: product.product_id,
                name: product.product_name,
                category_id: product.category_id,
                unit: product.unit,
                unit_price: product.unit_price
            };
            this.showEditModal = true;
        },
        openDeleteModal(id) {
            this.deleteUrl = '/admin/product/' + id;
            this.showDeleteModal = true;
        }
    }">
        <!-- Page Heading Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div class="flex flex-col gap-1">
                <p class="text-slate-500 dark:text-slate-400 text-sm">Danh mục vật tư văn phòng phẩm toàn bệnh viện.</p>
            </div>
            <div class="flex gap-3">
                <button
                    class="flex items-center justify-center gap-2 rounded-lg h-11 px-6 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
                    <span class="material-symbols-outlined text-[20px]">file_download</span>
                    Xuất báo cáo
                </button>
                <button @click="showAddModal = true"
                    class="flex items-center justify-center gap-2 rounded-lg h-11 px-6 bg-primary text-white text-sm font-bold hover:bg-primary/90 shadow-md shadow-primary/20 transition-all">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    Thêm sản phẩm
                </button>
            </div>
        </div>

        <!-- Table Container -->
        <div
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <!-- Table Search & Filter Bar -->
            <form action="{{ route('admin.product') }}" method="GET"
                class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center gap-4">
                <div class="relative flex-1 max-w-lg">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                        <span class="material-symbols-outlined text-[20px]">search</span>
                    </span>
                    <input name="q" value="{{ request('q') }}"
                        class="block w-full h-11 pl-10 border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white rounded-lg text-sm focus:border-primary focus:ring-primary"
                        placeholder="Tìm kiếm theo mã vật tư hoặc tên sản phẩm..." type="text" />
                </div>
                <button type="submit"
                    class="flex items-center gap-2 px-6 h-11 bg-slate-100 dark:bg-slate-800 rounded-lg text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-200 transition-all">
                    Tìm kiếm
                </button>
            </form>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Mã vật tư</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Tên sản phẩm
                            </th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Đơn
                                vị tính</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Đơn giá</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Danh mục</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Hành
                                động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($products as $product)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-5 text-sm font-medium text-primary">{{ $product->product_code }}</td>
                                <td class="px-6 py-5 text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ $product->product_name }}</td>
                                <td class="px-6 py-5 text-center">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white">{{ $product->unit }}</span>
                                </td>
                                <td class="px-6 py-5 text-sm text-slate-900 dark:text-slate-300 font-medium">
                                    {{ number_format($product->unit_price, 0, ',', '.') }} VNĐ</td>
                                <td class="px-6 py-5 text-sm text-slate-500">{{ $product->category->category_name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openEditModal({{ json_encode($product) }})"
                                            class="p-2 text-slate-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-all"
                                            title="Chỉnh sửa">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </button>
                                        <button @click="openDeleteModal({{ $product->product_id }})"
                                            class="p-2 text-slate-500 hover:text-red-500 hover:bg-red-50/50 rounded-lg transition-all"
                                            title="Xóa">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-500 italic">Không có dữ liệu phù hợp.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <p class="text-sm text-slate-500">Hiển thị {{ $products->firstItem() ?? 0 }} -
                    {{ $products->lastItem() ?? 0 }} trên {{ $products->total() }} sản phẩm</p>
                <div>
                    {{ $products->links() }}
                </div>
            </div>
        </div>

        <!-- MODALS -->

        <!-- Add Product Modal -->
        <div x-show="showAddModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
            style="display: none;">
            <div @click.away="showAddModal = false"
                class="bg-white dark:bg-slate-900 rounded-xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Thêm sản phẩm mới</h3>
                    <button @click="showAddModal = false"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form action="{{ route('admin.product.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Tên sản phẩm
                            *</label>
                        <input type="text" name="product_name" required
                            class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Danh mục
                            *</label>
                        <select name="category_id" required
                            class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-primary focus:border-primary">
                            <option value="">Chọn danh mục</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->category_id }}">{{ $cat->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">ĐVT</label>
                            <input type="text" name="unit"
                                class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Đơn
                                giá</label>
                            <input type="number" name="unit_price"
                                class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="showAddModal = false"
                            class="flex-1 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg font-bold">Hủy</button>
                        <button type="submit"
                            class="flex-1 py-2 bg-primary text-white rounded-lg font-bold shadow-lg shadow-primary/20">Lưu
                            sản phẩm</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Product Modal -->
        <div x-show="showEditModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
            style="display: none;">
            <div @click.away="showEditModal = false"
                class="bg-white dark:bg-slate-900 rounded-xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Chỉnh sửa sản phẩm</h3>
                    <button @click="showEditModal = false"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form :action="'/admin/product/' + editData.id" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Tên sản phẩm
                            *</label>
                        <input type="text" name="product_name" x-model="editData.name" required
                            class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Danh mục
                            *</label>
                        <select name="category_id" x-model="editData.category_id" required
                            class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-primary focus:border-primary">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->category_id }}">{{ $cat->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">ĐVT</label>
                            <input type="text" name="unit" x-model="editData.unit"
                                class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Đơn
                                giá</label>
                            <input type="number" name="unit_price" x-model="editData.unit_price"
                                class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="showEditModal = false"
                            class="flex-1 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg font-bold">Hủy</button>
                        <button type="submit"
                            class="flex-1 py-2 bg-primary text-white rounded-lg font-bold shadow-lg shadow-primary/20">Cập
                            nhật</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="showDeleteModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
            style="display: none;">
            <div @click.away="showDeleteModal = false"
                class="bg-white dark:bg-slate-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden text-center">
                <div class="p-8">
                    <div class="size-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-4xl">warning</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Xác nhận xóa?</h3>
                    <p class="text-slate-500 mb-6 text-sm">Hành động này không thể hoàn tác. Bạn có chắc chắn muốn xóa sản
                        phẩm này?</p>
                    <form :action="deleteUrl" method="POST" class="flex gap-3">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="showDeleteModal = false"
                            class="flex-1 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg font-bold">Hủy</button>
                        <button type="submit"
                            class="flex-1 py-2 bg-red-500 text-white rounded-lg font-bold shadow-lg shadow-red-200/50">Xóa
                            ngay</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection