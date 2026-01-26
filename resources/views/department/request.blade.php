@extends('layouts.department')

@section('title', 'Tạo Phiếu Yêu Cầu Văn Phòng Phẩm')

@section('styles')
<style type="text/tailwindcss">
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .search-dropdown {
        max-height: 0;
        opacity: 0;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .search-dropdown.active {
        max-height: 600px;
        opacity: 1;
    }
</style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto w-full space-y-6">
    
    <!-- Search Section with Dropdown -->
    <section class="relative">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-slate-800">
            <div class="relative group">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">search</span>
                <input 
                    id="searchInput"
                    class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-slate-800 border-transparent focus:border-primary focus:ring-0 rounded-xl text-lg transition-all" 
                    placeholder="Tìm kiếm sản phẩm hoặc thêm mới (F2)..." 
                    type="text"
                    autocomplete="off"
                    onfocus="showSearchDropdown()"
                    oninput="handleSearchInput(this.value)"/>
                <div class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-2">
                    <kbd class="hidden md:inline-flex items-center gap-1 px-2 py-1 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded text-xs text-slate-400">
                        <span class="material-symbols-outlined text-[10px]">keyboard_command_key</span> K
                    </kbd>
                </div>
            </div>
        </div>

        <!-- Search Results Dropdown -->
        <div id="searchDropdown" class="search-dropdown absolute top-full left-0 right-0 mt-2 bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden z-[60]">
            <!-- Search Results List -->
            <div id="searchResults" class="max-h-64 overflow-y-auto hide-scrollbar">
                <div class="p-2 border-b border-slate-100 dark:border-slate-800 text-xs font-semibold text-slate-400 px-4 uppercase tracking-wider">
                    Kết quả tìm kiếm
                </div>
                <div class="p-2 space-y-1">
                    <!-- Sample existing products -->
                    <button onclick="addExistingProduct('Bút bi xanh Thiên Long', 'Cây', 3500, 'VPP-101')" class="w-full text-left px-4 py-3 rounded-lg flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800 group transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-slate-400 group-hover:text-primary">inventory_2</span>
                            <div>
                                <div class="font-medium">Bút bi xanh Thiên Long</div>
                                <div class="text-xs text-slate-400">SKU: VPP-101 • ĐVT: Cây</div>
                            </div>
                        </div>
                        <span class="text-sm font-mono text-slate-500">3,500 đ</span>
                    </button>
                    <button onclick="addExistingProduct('Giấy A4 Double A', 'Ream', 95000, 'VPP-205')" class="w-full text-left px-4 py-3 rounded-lg flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800 group transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-slate-400 group-hover:text-primary">inventory_2</span>
                            <div>
                                <div class="font-medium">Giấy A4 Double A</div>
                                <div class="text-xs text-slate-400">SKU: VPP-205 • ĐVT: Ream</div>
                            </div>
                        </div>
                        <span class="text-sm font-mono text-slate-500">95,000 đ</span>
                    </button>
                    <button onclick="addExistingProduct('Bìa sơ mi lá lỗ cung tròn', 'Xấp', 25000, 'VPP-001')" class="w-full text-left px-4 py-3 rounded-lg flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800 group transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-slate-400 group-hover:text-primary">inventory_2</span>
                            <div>
                                <div class="font-medium">Bìa sơ mi lá lỗ cung tròn</div>
                                <div class="text-xs text-slate-400">SKU: VPP-001 • ĐVT: Xấp</div>
                            </div>
                        </div>
                        <span class="text-sm font-mono text-slate-500">25,000 đ</span>
                    </button>
                </div>
            </div>

            <!-- Add New Product Button -->
            <div class="p-2 border-t border-slate-100 dark:border-slate-800">
                <button onclick="showAddNewForm()" class="w-full text-left px-4 py-3 rounded-lg flex items-center justify-between hover:bg-primary/10 group transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary">add_circle</span>
                        <span class="font-medium">Thêm sản phẩm mới: <span id="searchQueryDisplay" class="text-primary italic"></span></span>
                    </div>
                    <span class="text-xs bg-primary text-white px-2 py-1 rounded">Mới</span>
                </button>
            </div>

            <!-- Add New Product Section -->
            <div id="addNewSection" class="hidden bg-slate-50 dark:bg-slate-800/50 p-6 border-t border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-primary">edit_note</span>
                    <h3 class="font-bold">Chi tiết sản phẩm mới</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase">Tên sản phẩm</label>
                        <input id="newProdName" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-primary focus:border-primary" type="text"/>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase">Đơn vị tính</label>
                        <select id="newProdUnit" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-primary focus:border-primary">
                            <option>Cái</option>
                            <option>Hộp</option>
                            <option>Thùng</option>
                            <option>Cuộn</option>
                            <option>Xấp</option>
                            <option>Cây</option>
                            <option>Ream</option>
                            <option>Tờ</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase">Danh mục</label>
                        <select id="newProdCategory" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-primary focus:border-primary">
                            <option>Văn phòng phẩm</option>
                            <option>Vật tư tiêu hao</option>
                            <option>Dụng cụ y khoa</option>
                            <option>Thiết bị văn phòng</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button onclick="cancelAddNew()" class="px-4 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">Bỏ qua</button>
                    <button onclick="confirmAddNew()" class="px-6 py-2 bg-primary text-white text-sm font-bold rounded-lg hover:bg-sky-700 shadow-md transition-all">Xác nhận &amp; Thêm</button>
                </div>
            </div>
        </div>
    </section>


    <!-- Products Table Section -->
    <section class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-4 font-semibold text-sm w-12 text-center">STT</th>
                        <th class="px-4 py-4 font-semibold text-sm">Tên hàng / Mô tả</th>
                        <th class="px-4 py-4 font-semibold text-sm w-24 text-center">ĐVT</th>
                        <th class="px-4 py-4 font-semibold text-sm w-44 text-center">Số Lượng</th>
                        <th class="px-4 py-4 font-semibold text-sm w-32 text-right">Đơn giá</th>
                        <th class="px-4 py-4 font-semibold text-sm w-36 text-right">Thành Tiền</th>
                        <th class="px-4 py-4 font-semibold text-sm w-12 text-center"></th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    <!-- Category Header -->
                    <tr class="bg-blue-50/50 dark:bg-primary/10">
                        <td class="px-4 py-2 border-y border-slate-200 dark:border-slate-800" colspan="7">
                            <div class="flex items-center gap-2 text-primary font-bold text-sm">
                                <span class="material-symbols-outlined text-sm">keyboard_arrow_down</span>
                                VĂN PHÒNG PHẨM - NHÀ SÁCH THANH VÂN
                            </div>
                        </td>
                    </tr>
                    <!-- Sample Product Row -->
                    <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                        <td class="px-4 py-3 text-center text-slate-400 text-sm">1</td>
                        <td class="px-4 py-3">
                            <div class="font-medium">Bìa sơ mi lá lỗ cung tròn</div>
                            <div class="text-xs text-slate-400">SKU: VPP-001</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">Xấp</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <button onclick="decreaseQty(this)" class="w-8 h-8 rounded bg-slate-100 dark:bg-slate-700 hover:bg-primary hover:text-white transition-colors flex items-center justify-center">
                                    <span class="material-symbols-outlined text-sm">remove</span>
                                </button>
                                <input class="w-16 h-8 text-center bg-transparent border-slate-200 dark:border-slate-700 rounded focus:ring-primary focus:border-primary p-0" type="number" value="1" onchange="updateTotal(this)"/>
                                <button onclick="increaseQty(this)" class="w-8 h-8 rounded bg-slate-100 dark:bg-slate-700 hover:bg-primary hover:text-white transition-colors flex items-center justify-center">
                                    <span class="material-symbols-outlined text-sm">add</span>
                                </button>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-mono">25,000</td>
                        <td class="px-4 py-3 text-right text-sm font-bold font-mono text-primary">25,000</td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="deleteRow(this)" class="p-1 text-slate-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all">
                                <span class="material-symbols-outlined text-xl">delete</span>
                            </button>
                        </td>
                    </tr>
                    <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                        <td class="px-4 py-3 text-center text-slate-400 text-sm">2</td>
                        <td class="px-4 py-3">
                            <div class="font-medium">Kim bấm</div>
                            <div class="text-xs text-slate-400">SKU: VPP-028</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">Hộp</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <button onclick="decreaseQty(this)" class="w-8 h-8 rounded bg-slate-100 dark:bg-slate-700 hover:bg-primary hover:text-white transition-colors flex items-center justify-center">
                                    <span class="material-symbols-outlined text-sm">remove</span>
                                </button>
                                <input class="w-16 h-8 text-center bg-transparent border-slate-200 dark:border-slate-700 rounded focus:ring-primary focus:border-primary p-0" type="number" value="2" onchange="updateTotal(this)"/>
                                <button onclick="increaseQty(this)" class="w-8 h-8 rounded bg-slate-100 dark:bg-slate-700 hover:bg-primary hover:text-white transition-colors flex items-center justify-center">
                                    <span class="material-symbols-outlined text-sm">add</span>
                                </button>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-mono">2,800</td>
                        <td class="px-4 py-3 text-right text-sm font-bold font-mono text-primary">5,600</td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="deleteRow(this)" class="p-1 text-slate-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all">
                                <span class="material-symbols-outlined text-xl">delete</span>
                            </button>
                        </td>
                    </tr>
                    <!-- Empty State (hidden by default) -->
                    <tr id="emptyState" class="hidden">
                        <td colspan="7" class="px-4 py-20 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <span class="material-symbols-outlined text-6xl mb-4">inventory_2</span>
                                <p>Chưa có mặt hàng nào được chọn. Hãy tìm kiếm ở trên.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Summary Footer -->
        <div class="sticky bottom-0 bg-slate-50 dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 p-4 px-6 flex items-center justify-between">
            <div class="flex gap-6">
                <div class="text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Số lượng mặt hàng:</span>
                    <span id="itemCount" class="font-bold ml-1 text-primary text-lg">02</span>
                </div>
                <div class="text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Tổng số lượng:</span>
                    <span id="totalQty" class="font-bold ml-1">3</span>
                </div>
                <div id="newItemBadge" class="text-sm hidden">
                    <span class="text-slate-500 dark:text-slate-400">Sản phẩm mới:</span>
                    <span id="newItemCount" class="font-bold ml-1 text-amber-600 bg-amber-100 dark:bg-amber-900/40 px-2 py-0.5 rounded">0</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-lg font-medium text-slate-500 dark:text-slate-400">TỔNG CỘNG:</span>
                <span id="grandTotal" class="text-2xl font-black text-primary font-mono tracking-tighter">30,600 VNĐ</span>
            </div>
        </div>
    </section>

    <!-- Action Buttons -->
    <div class="flex items-center justify-between pt-4">
        <button onclick="cancelRequest()" class="flex items-center gap-2 px-6 py-3 text-red-600 dark:text-red-400 font-bold hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all">
            <span class="material-symbols-outlined">delete_sweep</span> Hủy phiếu
        </button>
        <div class="flex items-center gap-4">
            <button onclick="saveDraft()" class="px-8 py-3.5 border border-slate-300 dark:border-slate-700 rounded-xl font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined">save</span> Lưu nháp
            </button>
            <button onclick="submitRequest()" class="px-10 py-3.5 bg-primary text-white rounded-xl font-bold hover:bg-sky-700 shadow-lg shadow-sky-500/20 active:scale-95 transition-all flex items-center gap-2">
                Gửi phiếu yêu cầu <span class="material-symbols-outlined">send</span>
            </button>
        </div>
    </div>

    <!-- Keyboard Shortcuts Helper -->
    <div class="fixed bottom-6 left-6 flex flex-col gap-2">
        <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-md px-5 py-2.5 rounded-full border border-slate-200 dark:border-slate-800 shadow-xl flex items-center gap-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">
            <div class="flex items-center gap-1.5">
                <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded">F2</kbd> Tìm/Thêm
            </div>
            <div class="w-1 h-1 bg-slate-300 dark:bg-slate-700 rounded-full"></div>
            <div class="flex items-center gap-1.5">
                <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded">F4</kbd> Lưu nháp
            </div>
            <div class="w-1 h-1 bg-slate-300 dark:bg-slate-700 rounded-full"></div>
            <div class="flex items-center gap-1.5">
                <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded">Enter</kbd> Gửi
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Global variables
let productCounter = 2;
let newProductCounter = 0;

// Search functionality
function showSearchDropdown() {
    document.getElementById('searchDropdown').classList.add('active');
}

function hideSearchDropdown() {
    setTimeout(() => {
        document.getElementById('searchDropdown').classList.remove('active');
        document.getElementById('addNewSection').classList.add('hidden');
    }, 200);
}

function handleSearchInput(value) {
    document.getElementById('searchQueryDisplay').textContent = `"${value}"`;
    if (value.trim() === '') {
        hideSearchDropdown();
    } else {
        showSearchDropdown();
    }
}

// Add existing product
function addExistingProduct(name, unit, price, sku) {
    productCounter++;
    const tbody = document.getElementById('productTableBody');
    const emptyState = document.getElementById('emptyState');
    
    if (emptyState && !emptyState.classList.contains('hidden')) {
        emptyState.classList.add('hidden');
    }
    
    const row = document.createElement('tr');
    row.className = 'border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group';
    row.innerHTML = `
        <td class="px-4 py-3 text-center text-slate-400 text-sm">${productCounter}</td>
        <td class="px-4 py-3">
            <div class="font-medium">${name}</div>
            <div class="text-xs text-slate-400">SKU: ${sku}</div>
        </td>
        <td class="px-4 py-3 text-sm text-center">${unit}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-center gap-1">
                <button onclick="decreaseQty(this)" class="w-8 h-8 rounded bg-slate-100 dark:bg-slate-700 hover:bg-primary hover:text-white transition-colors flex items-center justify-center">
                    <span class="material-symbols-outlined text-sm">remove</span>
                </button>
                <input class="w-16 h-8 text-center bg-transparent border-slate-200 dark:border-slate-700 rounded focus:ring-primary focus:border-primary p-0" type="number" value="1" onchange="updateTotal(this)"/>
                <button onclick="increaseQty(this)" class="w-8 h-8 rounded bg-slate-100 dark:bg-slate-700 hover:bg-primary hover:text-white transition-colors flex items-center justify-center">
                    <span class="material-symbols-outlined text-sm">add</span>
                </button>
            </div>
        </td>
        <td class="px-4 py-3 text-right text-sm font-mono" data-price="${price}">${price.toLocaleString()}</td>
        <td class="px-4 py-3 text-right text-sm font-bold font-mono text-primary">${price.toLocaleString()}</td>
        <td class="px-4 py-3 text-center">
            <button onclick="deleteRow(this)" class="p-1 text-slate-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all">
                <span class="material-symbols-outlined text-xl">delete</span>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    updateSummary();
    hideSearchDropdown();
    document.getElementById('searchInput').value = '';
}

// Show add new product form
function showAddNewForm() {
    const searchValue = document.getElementById('searchInput').value;
    document.getElementById('newProdName').value = searchValue;
    document.getElementById('addNewSection').classList.remove('hidden');
}

// Cancel add new product
function cancelAddNew() {
    document.getElementById('addNewSection').classList.add('hidden');
}

// Confirm add new product
function confirmAddNew() {
    const name = document.getElementById('newProdName').value;
    const unit = document.getElementById('newProdUnit').value;
    const category = document.getElementById('newProdCategory').value;
    
    if (!name.trim()) {
        alert('Vui lòng nhập tên sản phẩm');
        return;
    }
    
    productCounter++;
    newProductCounter++;
    const tbody = document.getElementById('productTableBody');
    
    const row = document.createElement('tr');
    row.className = 'bg-amber-50/50 dark:bg-amber-900/10 border-b border-amber-100 dark:border-amber-800/50 hover:bg-amber-100/50 dark:hover:bg-amber-900/20 transition-colors group';
    row.dataset.isNew = 'true';
    row.innerHTML = `
        <td class="px-4 py-3 text-center text-slate-400 text-sm italic font-medium">New</td>
        <td class="px-4 py-3">
            <div class="flex items-center gap-2">
                <div class="font-bold text-slate-900 dark:text-white">${name}</div>
                <span class="bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 text-[10px] px-1.5 py-0.5 rounded font-bold uppercase border border-amber-200 dark:border-amber-700">Mới</span>
            </div>
            <div class="text-xs text-amber-600 dark:text-amber-400 font-medium">Danh mục: ${category}</div>
        </td>
        <td class="px-4 py-3 text-sm text-center">${unit}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-center gap-1">
                <button onclick="decreaseQty(this)" class="w-8 h-8 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:bg-primary hover:text-white transition-colors flex items-center justify-center">
                    <span class="material-symbols-outlined text-sm font-bold">remove</span>
                </button>
                <input class="w-16 h-8 text-center bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded focus:ring-primary focus:border-primary p-0 font-bold" type="number" value="1" onchange="updateTotal(this)"/>
                <button onclick="increaseQty(this)" class="w-8 h-8 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:bg-primary hover:text-white transition-colors flex items-center justify-center">
                    <span class="material-symbols-outlined text-sm font-bold">add</span>
                </button>
            </div>
        </td>
        <td class="px-4 py-3 text-right text-sm font-mono text-slate-400 italic" data-price="0">Chờ duyệt</td>
        <td class="px-4 py-3 text-right text-sm font-bold font-mono text-amber-600">Tạm tính</td>
        <td class="px-4 py-3 text-center">
            <button onclick="deleteRow(this)" class="p-1 text-slate-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all">
                <span class="material-symbols-outlined text-xl">delete</span>
            </button>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
    updateSummary();
    cancelAddNew();
    hideSearchDropdown();
    document.getElementById('searchInput').value = '';
}

// Quantity controls
function increaseQty(btn) {
    const input = btn.previousElementSibling;
    input.value = parseInt(input.value) + 1;
    updateTotal(input);
}

function decreaseQty(btn) {
    const input = btn.nextElementSibling;
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
        updateTotal(input);
    }
}

function updateTotal(input) {
    const row = input.closest('tr');
    const priceCell = row.querySelector('td[data-price]');
    const totalCell = row.querySelectorAll('td')[5];
    const price = parseInt(priceCell.dataset.price);
    const qty = parseInt(input.value);
    const total = price * qty;
    
    if (!isNaN(total) && price > 0) {
        totalCell.textContent = total.toLocaleString();
    }
    
    updateSummary();
}

function deleteRow(btn) {
    const row = btn.closest('tr');
    if (row.dataset.isNew === 'true') {
        newProductCounter--;
    }
    row.remove();
    updateSummary();
}

// Update summary
function updateSummary() {
    const rows = document.querySelectorAll('#productTableBody tr:not([class*="bg-blue-50"]):not(#emptyState)');
    let itemCount = 0;
    let totalQty = 0;
    let grandTotal = 0;
    let newItems = 0;
    
    rows.forEach(row => {
        if (row.querySelector('input[type="number"]')) {
            itemCount++;
            const qty = parseInt(row.querySelector('input[type="number"]').value);
            totalQty += qty;
            
            const priceCell = row.querySelector('td[data-price]');
            const price = parseInt(priceCell.dataset.price);
            if (!isNaN(price) && price > 0) {
                grandTotal += price * qty;
            }
            
            if (row.dataset.isNew === 'true') {
                newItems++;
            }
        }
    });
    
    document.getElementById('itemCount').textContent = itemCount.toString().padStart(2, '0');
    document.getElementById('totalQty').textContent = totalQty;
    document.getElementById('grandTotal').textContent = grandTotal.toLocaleString() + ' VNĐ';
    
    const newItemBadge = document.getElementById('newItemBadge');
    if (newItems > 0) {
        newItemBadge.classList.remove('hidden');
        document.getElementById('newItemCount').textContent = newItems.toString().padStart(2, '0');
    } else {
        newItemBadge.classList.add('hidden');
    }
}

// Action buttons
function cancelRequest() {
    if (confirm('Bạn có chắc chắn muốn hủy phiếu yêu cầu này?')) {
        window.location.href = '{{ route("department.dashboard") }}';
    }
}

function saveDraft() {
    alert('Đã lưu nháp thành công!');
}

function submitRequest() {
    if (confirm('Bạn có chắc chắn muốn gửi phiếu yêu cầu này?')) {
        alert('Phiếu yêu cầu đã được gửi thành công!');
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'F2') {
        e.preventDefault();
        document.getElementById('searchInput').focus();
    } else if (e.key === 'F4') {
        e.preventDefault();
        saveDraft();
    } else if (e.key === 'Escape') {
        hideSearchDropdown();
    }
});

// Click outside to close dropdown
document.addEventListener('click', function(e) {
    const searchSection = document.querySelector('section.relative');
    if (!searchSection.contains(e.target)) {
        hideSearchDropdown();
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateSummary();
});
</script>
@endsection
