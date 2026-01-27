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

        <!-- Search Section -->
        <section class="relative">
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-slate-800">
                <div class="relative group">
                    <span
                        class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">search</span>
                    <input id="searchInput"
                        class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-slate-800 border-transparent focus:border-primary focus:ring-0 rounded-xl text-lg transition-all"
                        placeholder="Tìm kiếm sản phẩm (Nhập tên)..." type="text" autocomplete="off"
                        onfocus="showInitialProducts()" oninput="handleSearchInput(this.value)" />
                </div>
            </div>

            <!-- Search Results Dropdown -->
            <div id="searchDropdown"
                class="search-dropdown absolute top-full left-0 right-0 mt-2 bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden z-[60]">
                <div id="searchResults" class="max-h-64 overflow-y-auto hide-scrollbar p-2 space-y-1">
                    <!-- Results injected here -->
                </div>
            </div>
        </section>

        <!-- Details Form -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800">
            <label class="block text-sm font-bold mb-2">Ghi chú cho yêu cầu:</label>
            <textarea id="requestNote"
                class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg p-3" rows="2"
                placeholder="Nhập ghi chú (nếu có)..."></textarea>
        </div>

        <!-- Products Table -->
        <section
            class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-4 font-semibold text-sm w-12 text-center">STT</th>
                            <th class="px-4 py-4 font-semibold text-sm">Tên hàng</th>
                            <th class="px-4 py-4 font-semibold text-sm w-24 text-center">ĐVT</th>
                            <th class="px-4 py-4 font-semibold text-sm w-44 text-center">Số Lượng</th>
                            <th class="px-4 py-4 font-semibold text-sm w-32 text-right">Đơn giá</th>
                            <th class="px-4 py-4 font-semibold text-sm w-36 text-right">Thành Tiền</th>
                            <th class="px-4 py-4 font-semibold text-sm w-12 text-center"></th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        <tr id="emptyState">
                            <td colspan="7" class="px-4 py-20 text-center text-slate-400">
                                <span class="material-symbols-outlined text-6xl mb-4">inventory_2</span>
                                <p>Chưa có mặt hàng nào được chọn.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Summary Footer -->
            <div
                class="sticky bottom-0 bg-slate-50 dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 p-4 px-6 flex items-center justify-between">
                <div class="flex gap-6">
                    <div class="text-sm">
                        <span class="text-slate-500">Số lượng:</span>
                        <span id="totalQty" class="font-bold ml-1">0</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-lg font-medium text-slate-500">TỔNG CỘNG:</span>
                    <span id="grandTotal" class="text-2xl font-black text-primary font-mono tracking-tighter">0 VNĐ</span>
                </div>
            </div>
        </section>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-4 pt-4">
            <button onclick="submitRequest()"
                class="px-10 py-3.5 bg-primary text-white rounded-xl font-bold hover:bg-sky-700 shadow-lg shadow-sky-500/20 active:scale-95 transition-all flex items-center gap-2">
                Gửi phiếu yêu cầu <span class="material-symbols-outlined">send</span>
            </button>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"></script>
    <script>
        let selectedProducts = [];
        const initialProducts = @json($initialProducts ?? []);

        function showInitialProducts() {
            const dropdown = document.getElementById('searchDropdown');
            const resultsContainer = document.getElementById('searchResults');
            const input = document.getElementById('searchInput');

            if (input.value.trim().length > 0) return; // Don't override if searching

            resultsContainer.innerHTML = '';

            if (initialProducts.length > 0) {
                const title = document.createElement('div');
                title.className = "px-4 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider";
                title.textContent = "Gợi ý cho bạn";
                resultsContainer.appendChild(title);

                initialProducts.forEach(p => {
                    const div = document.createElement('div');
                    div.className = "w-full text-left px-4 py-3 rounded-lg flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer group transition-colors";
                    div.innerHTML = `
                            <div>
                                <div class="font-medium">${p.product_name}</div>
                                <div class="text-xs text-slate-400">Code: ${p.product_code || ''} • ĐVT: ${p.unit}</div>
                            </div>
                            <span class="text-sm font-mono text-slate-500">${parseInt(p.unit_price).toLocaleString()} đ</span>
                        `;
                    div.onclick = () => addProduct(p);
                    resultsContainer.appendChild(div);
                });
                dropdown.classList.add('active');
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            const searchSection = document.querySelector('section.relative');
            if (!searchSection.contains(e.target)) {
                document.getElementById('searchDropdown').classList.remove('active');
            }
        });

        // Debounce function
        function debounce(func, timeout = 300) {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => { func.apply(this, args); }, timeout);
            };
        }

        const handleSearchInput = debounce(async (value) => {
            const dropdown = document.getElementById('searchDropdown');
            const resultsContainer = document.getElementById('searchResults');

            if (value.length < 1) {
                showInitialProducts();
                return;
            }

            try {
                const response = await axios.get('{{ route("department.products.search") }}', { params: { q: value } });
                const products = response.data;

                resultsContainer.innerHTML = '';
                if (products.length > 0) {
                    products.forEach(p => {
                        const div = document.createElement('div');
                        div.className = "w-full text-left px-4 py-3 rounded-lg flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer group transition-colors";
                        div.innerHTML = `
                            <div>
                                <div class="font-medium">${p.product_name}</div>
                                <div class="text-xs text-slate-400">Code: ${p.product_code || ''} • ĐVT: ${p.unit}</div>
                            </div>
                            <span class="text-sm font-mono text-slate-500">${parseInt(p.unit_price).toLocaleString()} đ</span>
                        `;
                        div.onclick = () => addProduct(p);
                        resultsContainer.appendChild(div);
                    });
                    dropdown.classList.add('active');
                } else {
                    resultsContainer.innerHTML = '<div class="p-4 text-center text-slate-500">Không tìm thấy sản phẩm</div>';
                    dropdown.classList.add('active');
                }
            } catch (error) {
                console.error(error);
            }
        });

        function addProduct(product) {
            // Check if exists
            const existing = selectedProducts.find(p => p.product_id === product.product_id);
            if (existing) {
                existing.quantity += 1;
            } else {
                selectedProducts.push({ ...product, quantity: 1 });
            }
            renderTable();
            document.getElementById('searchDropdown').classList.remove('active');
            document.getElementById('searchInput').value = '';
        }

        function renderTable() {
            const tbody = document.getElementById('productTableBody');
            const emptyState = document.getElementById('emptyState');

            if (selectedProducts.length === 0) {
                tbody.innerHTML = '';
                tbody.appendChild(emptyState);
                emptyState.classList.remove('hidden');
                updateSummary();
                return;
            }

            tbody.innerHTML = '';

            selectedProducts.forEach((p, index) => {
                const total = p.quantity * p.unit_price;
                const tr = document.createElement('tr');
                tr.className = 'border-b border-slate-100 dark:border-slate-800';
                tr.innerHTML = `
                    <td class="px-4 py-3 text-center text-slate-400 text-sm">${index + 1}</td>
                    <td class="px-4 py-3 font-medium">${p.product_name}</td>
                    <td class="px-4 py-3 text-center text-sm">${p.unit}</td>
                    <td class="px-4 py-3">
                        <input type="number" min="1" value="${p.quantity}" 
                            class="w-20 text-center bg-transparent border border-slate-200 rounded p-1"
                            onchange="updateQuantity(${p.product_id}, this.value)">
                    </td>
                    <td class="px-4 py-3 text-right text-sm font-mono">${parseInt(p.unit_price).toLocaleString()}</td>
                    <td class="px-4 py-3 text-right text-sm font-bold font-mono text-primary">${total.toLocaleString()}</td>
                    <td class="px-4 py-3 text-center">
                         <button onclick="removeProduct(${p.product_id})" class="text-slate-400 hover:text-red-500">
                            <span class="material-symbols-outlined">delete</span>
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
                product.quantity = parseInt(qty);
                renderTable();
            }
        }

        function removeProduct(id) {
            selectedProducts = selectedProducts.filter(p => p.product_id !== id);
            renderTable();
        }

        function updateSummary() {
            const totalQty = selectedProducts.reduce((sum, p) => sum + p.quantity, 0);
            const grandTotal = selectedProducts.reduce((sum, p) => sum + (p.quantity * p.unit_price), 0);

            document.getElementById('totalQty').textContent = totalQty;
            document.getElementById('grandTotal').textContent = grandTotal.toLocaleString() + ' VNĐ';
        }

        async function submitRequest() {
            if (selectedProducts.length === 0) return alert('Vui lòng chọn ít nhất một sản phẩm');
            const note = document.getElementById('requestNote').value;

            if (!confirm('Xác nhận gửi yêu cầu này?')) return;

            try {
                const payload = {
                    items: selectedProducts.map(p => ({
                        product_id: p.product_id,
                        quantity: p.quantity
                    })),
                    note: note
                };

                const res = await axios.post('{{ route("department.request.store") }}', payload);

                if (res.data.success) {
                    alert(res.data.message);
                    window.location.href = res.data.redirect || '{{ route("department.dashboard") }}';
                }
            } catch (error) {
                console.error(error);
                alert('Có lỗi xảy ra: ' + (error.response?.data?.message || error.message));
            }
        }
    </script>
@endsection