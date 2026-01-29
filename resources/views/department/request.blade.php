@extends('layouts.department')

@section('title', 'Tạo Phiếu Yêu Cầu')

@section('styles')
<style>
    /* Compact Layout Styles */
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.05);
    }
    
    .table-container {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #f1f5f9;
    }

    .custom-table th {
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.025em;
        padding: 0.6rem 1rem;
        background-color: #f8fafc;
    }

    .custom-table td {
        padding: 0.5rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f8fafc;
        font-size: 0.85rem;
    }

    .btn-add {
        background-color: #eff6ff;
        color: #2563eb;
        font-weight: 700;
        padding: 0.3rem 0.8rem;
        border-radius: 6px;
        font-size: 0.75rem;
        transition: all 0.2s;
    }

    .btn-add:hover {
        background-color: #2563eb;
        color: white;
    }

    .search-input {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .search-input:focus {
        background-color: white;
        border-color: #3b82f6;
    }

    .price-text {
        color: #2563eb;
        font-weight: 600;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }
    
    .total-card {
        background-color: #f8fafc;
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
    }

    /* Hide scrollbars but keep functionality */
    body { overflow: hidden; }
    main { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }

    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Pagination Styles */
    .pagination-btn {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
        font-size: 0.75rem;
        color: #64748b;
        transition: all 0.2s;
    }
    .pagination-btn:hover:not(:disabled) {
        background-color: #f1f5f9;
        color: #1e293b;
    }
    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 space-y-3">
    
    <!-- SECTION 1: DANH MỤC VẬT TƯ -->
    <div class="glass-card rounded-xl p-4">
        <div class="flex flex-col md:flex-row gap-3 mb-3 text-slate-900 items-center">
            <div class="relative flex-1 w-full">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 !text-xl">search</span>
                <input type="text" id="searchInput" oninput="handleSearch()"
                    placeholder="Tìm kiếm mã VT hoặc tên hàng..." 
                    class="w-full pl-10 pr-4 py-2 rounded-lg search-input outline-none border-slate-200 text-sm">
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold text-slate-500 uppercase whitespace-nowrap">LOẠI SẢN PHẨM:</span>
                <select id="categoryFilter" onchange="handleSearch()" 
                    class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs outline-none focus:border-blue-500 min-w-[160px]">
                    <option value="all">Tất cả sản phẩm</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->category_id }}">{{ $cat->category_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between mb-2">
            <h3 class="flex items-center gap-2 font-bold text-slate-700 text-sm">
                <span class="material-symbols-outlined text-blue-600 !text-lg">inventory</span>
                DANH MỤC VẬT TƯ
            </h3>
            <span class="text-[9px] italic text-slate-400">Nhấp '+ Thêm' để chọn</span>
        </div>

        <div class="table-container">
            <table class="w-full custom-table text-left">
                <thead>
                    <tr>
                        <th class="w-24">Mã VT</th>
                        <th>Tên hàng</th>
                        <th class="w-20 text-center">ĐVT</th>
                        <th class="w-32 text-right">Đơn giá</th>
                        <th class="w-24 text-center">Thêm</th>
                    </tr>
                </thead>
                <tbody id="catalogTableBody">
                    {{-- JS injects rows here --}}
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-end gap-2 mt-2" id="paginationControls">
            <button onclick="changePage(-1)" id="prevBtn" class="pagination-btn flex items-center gap-1">
                <span class="material-symbols-outlined !text-sm">chevron_left</span> Trước
            </button>
            <span id="pageInfo" class="text-[10px] text-slate-500 font-bold">Trang 1/1</span>
            <button onclick="changePage(1)" id="nextBtn" class="pagination-btn flex items-center gap-1">
                Sau <span class="material-symbols-outlined !text-sm">chevron_right</span>
            </button>
        </div>
    </div>

    <!-- SECTION 2: CHI TIẾT PHIẾU ĐANG LẬP -->
    <div class="glass-card rounded-xl p-4">
        <h3 class="flex items-center gap-2 font-bold text-slate-700 mb-2 text-sm">
            <span class="material-symbols-outlined text-blue-600 !text-lg">description</span>
            CHI TIẾT PHIẾU ĐANG LẬP
        </h3>

        <div class="table-container mb-3" style="max-height: 220px; overflow-y: auto;">
            <table class="w-full custom-table text-left text-sm">
                <thead class="sticky top-0 z-10">
                    <tr>
                        <th class="w-10 text-center">STT</th>
                        <th>Tên hàng / Mã VT</th>
                        <th class="w-20 text-center">ĐVT</th>
                        <th class="w-28 text-center">Số lượng</th>
                        <th class="w-28 text-right">Đơn giá</th>
                        <th class="w-28 text-right">Thành tiền</th>
                        <th class="w-10 text-center">Xóa</th>
                    </tr>
                </thead>
                <tbody id="selectedTableBody">
                    <tr id="emptyState">
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            <div class="flex flex-col items-center">
                                <span class="material-symbols-outlined text-3xl mb-1">draft</span>
                                <p class="text-xs">Chưa có mặt hàng nào được chọn.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col md:flex-row items-center justify-between gap-4 total-card">
            <div class="flex items-center gap-6">
                <div>
                    <p class="text-[9px] text-slate-500 uppercase font-bold">Mặt hàng</p>
                    <p class="text-sm font-bold text-slate-800" id="totalItems">00 sản phẩm</p>
                </div>
                <div class="h-6 w-[1px] bg-slate-200 hidden md:block"></div>
                <div>
                    <p class="text-[9px] text-slate-500 uppercase font-bold">Tổng cộng (VND)</p>
                    <p class="text-xl font-black text-blue-700 font-mono tracking-tight" id="grandTotal">0</p>
                </div>
            </div>
            
            <button onclick="submitRequest()"
                class="w-full md:w-auto px-10 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 shadow-md shadow-blue-500/20 transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined !text-lg">send</span>
                GỬI PHIẾU YÊU CẦU
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"></script>
<script>
    let allProducts = @json($initialProducts);
    let filteredProducts = [...allProducts];
    let selectedProducts = [];
    
    let currentPage = 1;
    const itemsPerPage = 5;

    // Initialization
    renderCatalog();

    function handleSearch() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        const categoryId = document.getElementById('categoryFilter').value;
        
        filteredProducts = allProducts.filter(p => {
            const matchesQuery = p.product_name.toLowerCase().includes(query) || 
                               (p.product_code && p.product_code.toLowerCase().includes(query));
            const matchesCategory = categoryId === 'all' || p.category_id == categoryId;
            return matchesQuery && matchesCategory;
        });

        currentPage = 1;
        renderCatalog();
    }

    function renderCatalog() {
        const tbody = document.getElementById('catalogTableBody');
        tbody.innerHTML = '';
        
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const pageItems = filteredProducts.slice(startIndex, endIndex);

        if (pageItems.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="py-4 text-center text-slate-400 text-xs">Không tìm thấy sản phẩm</td></tr>';
        } else {
            pageItems.forEach(p => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-[10px] text-slate-500 font-mono">${p.product_code || ''}</td>
                    <td class="font-bold text-slate-800">${p.product_name}</td>
                    <td class="text-center text-xs text-slate-600">${p.unit}</td>
                    <td class="text-right price-text text-sm">${parseInt(p.unit_price).toLocaleString()}</td>
                    <td class="text-center">
                        <button onclick='addProduct(${JSON.stringify(p)})' class="btn-add">+ THÊM</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        updatePagination();
    }

    function updatePagination() {
        const totalPages = Math.ceil(filteredProducts.length / itemsPerPage) || 1;
        document.getElementById('pageInfo').textContent = `Trang ${currentPage}/${totalPages}`;
        document.getElementById('prevBtn').disabled = currentPage === 1;
        document.getElementById('nextBtn').disabled = currentPage === totalPages;
    }

    function changePage(dir) {
        currentPage += dir;
        renderCatalog();
    }

    function addProduct(product) {
        const existing = selectedProducts.find(p => p.product_id === product.product_id);
        if (existing) {
            existing.quantity += 1;
        } else {
            selectedProducts.push({ ...product, quantity: 1 });
        }
        renderSelectedTable();
    }

    function renderSelectedTable() {
        const tbody = document.getElementById('selectedTableBody');
        const emptyState = document.getElementById('emptyState');

        if (selectedProducts.length === 0) {
            tbody.innerHTML = '';
            tbody.appendChild(emptyState);
            updateSummary();
            return;
        }

        tbody.innerHTML = '';
        selectedProducts.forEach((p, index) => {
            const total = p.quantity * p.unit_price;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center text-slate-400 text-[10px]">${index + 1}</td>
                <td>
                    <div class="font-bold text-slate-800 text-xs">${p.product_name}</div>
                    <div class="text-[9px] text-slate-400 font-mono">${p.product_code || ''}</div>
                </td>
                <td class="text-center text-slate-600 text-xs">${p.unit}</td>
                <td class="text-center">
                    <input type="number" min="1" value="${p.quantity}" 
                        onchange="updateQuantity(${p.product_id}, this.value)"
                        class="w-16 text-center bg-slate-50 border border-slate-100 rounded-lg py-0.5 outline-none focus:border-blue-400 text-xs text-slate-900">
                </td>
                <td class="text-right font-mono text-slate-500 text-xs">${parseInt(p.unit_price).toLocaleString()}</td>
                <td class="text-right font-bold text-slate-800 font-mono text-xs">${total.toLocaleString()}</td>
                <td class="text-center">
                    <button onclick="removeProduct(${p.product_id})" class="text-red-400 hover:text-red-600 transition-colors">
                        <span class="material-symbols-outlined !text-lg">delete</span>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
        updateSummary();
    }

    function updateQuantity(id, qty) {
        const product = selectedProducts.find(p => p.product_id === id);
        if (product) {
            product.quantity = Math.max(1, parseInt(qty) || 1);
            renderSelectedTable();
        }
    }

    function removeProduct(id) {
        selectedProducts = selectedProducts.filter(p => p.product_id !== id);
        renderSelectedTable();
    }

    function updateSummary() {
        const totalItems = selectedProducts.length;
        const grandTotal = selectedProducts.reduce((sum, p) => sum + (p.quantity * p.unit_price), 0);

        document.getElementById('totalItems').textContent = totalItems.toString().padStart(2, '0') + ' sản phẩm';
        document.getElementById('grandTotal').textContent = grandTotal.toLocaleString();
    }

    async function submitRequest() {
        if (selectedProducts.length === 0) return alert('Vui lòng chọn ít nhất một sản phẩm');

        if (!confirm('Xác nhận gửi yêu cầu này?')) return;

        try {
            const payload = {
                items: selectedProducts.map(p => ({
                    product_id: p.product_id,
                    quantity: p.quantity
                })),
                note: "" // Note removed from UI, sending empty
            };

            const res = await axios.post('{{ route("department.request.store") }}', payload);

            if (res.data.success) {
                alert(res.data.message);
                window.location.href = res.data.redirect;
            }
        } catch (error) {
            console.error(error);
            alert('Có lỗi xảy ra: ' + (error.response?.data?.message || error.message));
        }
    }
</script>
@endsection