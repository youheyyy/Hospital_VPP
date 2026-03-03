<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nhập liệu nhanh Grid - Tháng {{ $selectedMonth }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style type="text/tailwindcss">
        @layer base {
            body { @apply bg-slate-50 text-slate-900 antialiased font-sans; }
        }

        .excel-table {
            @apply border-collapse w-full text-[13px];
        }

        .excel-table th,
        .excel-table td {
            @apply border border-slate-200 p-2;
        }

        .excel-table th {
            @apply bg-slate-100 font-bold text-center text-slate-700 sticky top-0 z-10 shadow-sm;
        }

        .category-header {
            @apply bg-indigo-600 text-white font-bold text-left uppercase text-[11px] tracking-wider;
        }

        .cell-input {
            @apply w-full border-none p-1 text-right focus:ring-2 focus:ring-indigo-500 focus:bg-indigo-50 transition-all outline-none bg-transparent h-full min-w-[80px];
        }

        .sidebar-item {
            @apply flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition-all duration-200 cursor-pointer;
        }

        .sidebar-item.active {
            @apply bg-indigo-600 text-white shadow-lg shadow-indigo-200;
        }
    </style>
</head>

<body class="bg-slate-50 overflow-hidden">
    <div class="flex h-screen" x-data="gridApp()">
        <!-- Sidebar -->
        <aside
            class="w-64 flex-shrink-0 bg-white border-r border-slate-100 flex flex-col py-8 px-4 gap-4 no-print shadow-[4px_0_24px_rgba(0,0,0,0.02)] z-20 relative">
            <div class="mb-8 px-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-mint-500 rounded-2xl flex items-center justify-center shadow-lg shadow-mint-100">
                        <span class="material-symbols-outlined text-white"
                            style="background-color: #10b981; border-radius: 9999px; padding: 4px;">inventory_2</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900">VPP Admin</h2>
                        <p class="text-[10px] text-slate-400 font-medium">Quản trị hệ thống</p>
                    </div>
                </div>
            </div>
            <nav class="flex flex-col gap-2 flex-1">
                <a class="sidebar-item" href="{{ route('admin.dashboard') }}">
                    <span class="material-symbols-outlined">grid_view</span>
                    <span class="text-sm font-bold">Tổng quan</span>
                </a>
                <a class="sidebar-item active" href="{{ route('admin.consolidated') }}">
                    <span class="material-symbols-outlined">assignment</span>
                    <span class="text-sm font-bold">Tổng hợp yêu cầu</span>
                </a>
            </nav>
            <div class="mt-auto px-4">
                <div class="bg-slate-50 rounded-2xl p-4 mb-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <p class="text-sm font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-slate-400 uppercase">{{ auth()->user()->role }}</p>
                        </div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 transition-colors text-sm font-bold text-slate-600">
                        <span class="material-symbols-outlined text-lg">logout</span>
                        <span>Đăng xuất</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden bg-white relative z-10">
            <!-- Header -->
            <header
                class="bg-white px-6 py-4 flex justify-between items-center gap-3 z-30 shadow-sm border-b border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            Nhập liệu: <span x-text="getDeptName()" class="text-indigo-600"></span>
                        </h1>
                        <p class="text-xs text-slate-500 font-medium">Tháng {{ $selectedMonth }} • Tự động tính Thành
                            tiền ngay khi nhập</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 flex-shrink-0">
                    <!-- Khoa Selector -->
                    <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                        <span class="material-symbols-outlined text-slate-400 text-sm">domain</span>
                        <select x-model="selectedDept"
                            class="bg-transparent border-none text-sm font-bold text-slate-700 py-1 pr-8 focus:ring-0 cursor-pointer">
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search Box -->
                    <div class="relative">
                        <input type="text" x-model="searchQuery" placeholder="Tìm kiếm sản phẩm..."
                            class="pl-9 pr-4 py-2 border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 w-64 shadow-sm">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                    </div>

                    <!-- Add Product Button -->
                    <button @click="showAddModal = true"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 flex items-center gap-2 transition-colors shadow-sm font-bold text-sm">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Thêm sản phẩm mới
                    </button>
                </div>
            </header>

            <!-- Table Area -->
            <div class="flex-1 overflow-auto bg-slate-50/50 p-6 relative">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <table class="excel-table w-full relative">
                        <thead>
                            <tr>
                                <th class="w-[50px] text-center">STT</th>
                                <th class="text-left">Tên sản phẩm</th>
                                <th class="w-[80px] text-center">ĐVT</th>
                                <th class="w-[120px] text-center text-indigo-700 bg-indigo-50/50">Số lượng</th>
                                <th class="w-[120px] text-right">Đơn giá</th>
                                <th class="w-[150px] text-right text-emerald-700 bg-emerald-50/50">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $stt = 0; @endphp
                            @foreach($categories as $category)
                                @if(isset($products[$category->id]) && count($products[$category->id]) > 0)
                                    <!-- Category Row -->
                                    <tr class="category-header category-row" data-cat-id="{{ $category->id }}">
                                        <td colspan="6" class="px-3 py-2">
                                            {{ strtoupper($category->name) }}
                                        </td>
                                    </tr>

                                    <!-- Products in Category -->
                                    @foreach($products[$category->id] as $product)
                                        @php $stt++; @endphp
                                        <tr class="hover:bg-slate-50 transition-colors product-row"
                                            x-show="matchSearch($el.dataset.name)" data-name="{{ $product->name }}"
                                            data-cat-id="{{ $category->id }}">
                                            <td class="text-center text-slate-400 text-xs">{{ $stt }}</td>
                                            <td class="font-medium text-slate-700 px-3 py-2">{{ $product->name }}</td>
                                            <td class="text-center text-slate-500">{{ $product->unit }}</td>
                                            <td class="p-0 bg-indigo-50/10 relative group">
                                                <input type="number" step="0.1"
                                                    x-model="data['{{ $product->id }}'].orders[selectedDept]"
                                                    @change="saveQuantity('{{ $product->id }}', $event.target.value)"
                                                    class="cell-input font-bold text-indigo-700 bg-transparent placeholder-slate-300"
                                                    placeholder="0" :class="{ 'opacity-50': saving['{{ $product->id }}'] }">
                                                <div x-show="saving['{{ $product->id }}']"
                                                    class="absolute right-1 top-1/2 -translate-y-1/2 pointer-events-none">
                                                    <span
                                                        class="material-symbols-outlined text-[14px] text-indigo-500 animate-spin">sync</span>
                                                </div>
                                                <div x-show="saved['{{ $product->id }}']"
                                                    class="absolute right-1 top-1/2 -translate-y-1/2 pointer-events-none">
                                                    <span
                                                        class="material-symbols-outlined text-[14px] text-emerald-500">check_circle</span>
                                                </div>
                                            </td>
                                            <td class="text-right text-slate-500 px-3 py-2">
                                                {{ number_format($product->price, 0, ',', '.') }}
                                            </td>
                                            <td class="text-right font-bold text-emerald-600 px-3 py-2 bg-emerald-50/10"
                                                x-text="formatNumber(calculateTotal('{{ $product->id }}'))">
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot class="sticky bottom-0 z-10 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                            <tr class="bg-white border-t-2 border-slate-200">
                                <td colspan="5" class="text-right font-bold text-slate-700 px-3 py-4 text-base">TỔNG
                                    THÀNH TIỀN (KHOA ĐANG CHỌN):</td>
                                <td class="text-right font-extrabold text-emerald-600 px-3 py-4 text-lg bg-emerald-50/30"
                                    x-text="formatNumber(departmentSubtotal)"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </main>

        <!-- Add Product Modal -->
        <div x-show="showAddModal" style="display: none;"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm"
            x-transition.opacity>

            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col"
                @click.away="showAddModal = false" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100">

                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-extrabold text-lg text-slate-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-indigo-500">add_box</span>
                        Thêm Sản Phẩm Mới
                    </h3>
                    <button @click="showAddModal = false"
                        class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-full hover:bg-slate-200">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="p-6">
                    <div class="grid gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Danh mục (*)</label>
                            <select x-model="newProduct.category_id"
                                class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-indigo-500 shadow-sm text-sm">
                                <option value="">-- Chọn danh mục --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Tên sản phẩm (*)</label>
                            <input type="text" x-model="newProduct.name"
                                class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-indigo-500 shadow-sm text-sm"
                                placeholder="Nhập tên chính xác...">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Đơn vị tính</label>
                                <input type="text" x-model="newProduct.unit"
                                    class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-indigo-500 shadow-sm text-sm"
                                    placeholder="Cái, hộp, hộp...">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Đơn giá</label>
                                <input type="number" x-model.number="newProduct.price"
                                    class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-indigo-500 shadow-sm text-sm"
                                    placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3">
                    <button @click="showAddModal = false"
                        class="px-4 py-2 rounded-xl font-bold text-slate-600 hover:bg-slate-200 transition-colors">Hủy</button>
                    <button @click="submitNewProduct"
                        class="px-5 py-2 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-200 flex items-center gap-2 transition-all relative overflow-hidden"
                        :class="{ 'opacity-70 cursor-not-allowed': isSubmittingProduct }">
                        <span x-show="!isSubmittingProduct">Lưu Sản Phẩm</span>
                        <span x-show="isSubmittingProduct" class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[16px] animate-spin">sync</span>
                            Đang lưu...
                        </span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        function gridApp() {
            // Khởi tạo data state
            @php
                $initDataArray = [];
                foreach ($products as $catProducts) {
                    foreach ($catProducts as $p) {
                        $orders = [];
                        foreach ($departments as $d) {
                            $orderObj = $p->monthlyOrders->where('department_id', $d->id)->first();
                            $orders[$d->id] = $orderObj ? $orderObj->quantity : "";
                        }
                        $initDataArray[$p->id] = [
                            'price' => (float) $p->price,
                            'orders' => $orders
                        ];
                    }
                }
            @endphp
const initData = {!! json_encode($initDataArray) !!};
            
            const deptNames = {!! json_encode($departments->pluck('name', 'id')->toArray()) !!};

                return {
                data: initData,
                selectedDept: '{{ $departments->first()->id ?? "" }}',
                searchQuery: '',
                saving: {},
            saved: {},
    
                // Modal Add Product state
                showAddModal: false,
                isSubmittingProduct: false,
                newProduct: {
                    name: '',
                    unit: '',
                    price: '',
                    category_id: ''
            },
    
            getDeptName() {
                    return deptNames[this.selectedDept] || 'Đang chọn...';
            },

            matchSearch(prodName) {
                    if (!this.searchQuery) return true;
                    return prodName.toLowerCase().includes(this.searchQuery.toLowerCase());
            },
   
            calculateTotal(productId) {
                let qty = parseFloat(this.data[productId].orders[this.selectedDept]) || 0;
                return qty * this.data[productId].price;
            },

            get departmentSubtotal() {
                let sum = 0;
                for (let pId in this.data) {
                    let qty = parseFloat(this.data[pId].orders[this.selectedDept]) || 0;
                    sum += qty * this.data[pId].price;
                }
                return sum;
            },

            formatNumber(num) {
                if (!num || num === 0) return '0';
                return num.toLocaleString('vi-VN');
            },

            saveQuantity(productId, value) {
                let qty = value === '' ? null : parseFloat(value);
                
                this.saving[productId] = true;
                
                fetch('{{ route("admin.grid-entry.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        department_id: this.selectedDept,
                        month: '{{ $selectedMonth }}',
                        quantity: qty
                    })
                })
                .then(response => response.json())
                .then(res => {
                    this.saving[productId] = false;
                    if (res.success) {
                        this.saved[productId] = true;
                        setTimeout(() => { this.saved[productId] = false; }, 1500);
                    } else {
                        alert('Lỗi lưu dữ liệu!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.saving[productId] = false;
                    alert('Lỗi kết nối khi lưu!');
                });
            },
            
            submitNewProduct() {
                if (!this.newProduct.name || !this.newProduct.category_id) {
                    alert('Vui lòng điền Danh mục và Tên sản phẩm!');
                    return;
                }
                
                this.isSubmittingProduct = true;
                
                fetch('{{ route("admin.grid-entry.product") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name: this.newProduct.name,
                        unit: this.newProduct.unit,
                        price: this.newProduct.price || 0,
                        category_id: this.newProduct.category_id
                    })
                })
                .then(res => res.json())
                .then(res => {
                    this.isSubmittingProduct = false;
                    if (res.success) {
                        alert('Thêm sản phẩm thành công! Trang sẽ tải lại để hiển thị sản phẩm mới.');
                        window.location.reload();
                    } else {
                        alert('Lỗi khi thêm sản phẩm: ' + (res.message || 'Unknown error'));
                    }
                })
                .catch(err => {
                    this.isSubmittingProduct = false;
                    alert('Lỗi kết nối mạng!');
                });
            }
        }
        }
    </script>
</body>

</html>