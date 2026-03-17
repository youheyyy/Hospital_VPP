<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nhập liệu nhanh dạng lưới - {{ $selectedMonth }}</title>
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

        .grid-table {
            @apply border-collapse w-full text-[13px];
        }

        .grid-table th,
        .grid-table td {
            @apply border border-slate-200;
        }

        /* Sticky header */
        .grid-table thead th {
            @apply sticky top-0 bg-slate-100 z-20 font-bold p-2 shadow-sm;
        }

        /* Sticky first column */
        .grid-table .sticky-col {
            @apply sticky left-0 bg-white z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)];
        }

        /* Intersection of sticky row/col */
        .grid-table thead th.sticky-col {
            @apply z-30 bg-slate-200;
        }

        .cell-input {
            @apply w-full border-none p-1 text-right focus:ring-2 focus:ring-indigo-500 focus:bg-indigo-50 transition-all outline-none bg-transparent h-full;
        }

        .product-row:hover .sticky-col {
            @apply bg-slate-50;
        }

        .product-row:hover {
            @apply bg-slate-50;
        }

        .saving-indicator {
            @apply absolute right-0 top-0 text-[10px] p-0.5 pointer-events-none;
        }
    </style>
</head>

<body class="flex flex-col h-screen overflow-hidden">
    <!-- Header -->
    <header class="bg-white border-b border-slate-100 px-6 py-4 flex justify-between items-center z-40">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.consolidated', ['month' => $selectedMonth]) }}"
                class="text-slate-400 hover:text-slate-600 transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                    Công cụ Nhập liệu Grid
                    <span
                        class="px-2 py-0.5 bg-indigo-100 text-indigo-600 text-[10px] rounded-full uppercase tracking-wider">Fast
                        Entry</span>
                </h1>
                <p class="text-xs text-slate-400">Tháng {{ $selectedMonth }} • Nhập nhanh số lượng cho 20 khoa/phòng</p>
            </div>
        </div>

        <div class="flex items-center gap-3" x-data="monthPicker('{{ $selectedMonth }}')">
            <!-- Smart Month Picker -->
            <div class="relative">
                <div
                    class="flex items-center bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-indigo-500 transition-all">
                    <input type="text" x-model="displayMonth" @keydown.enter="submitMonth()" @blur="formatAndSubmit()"
                        placeholder="MM/YYYY"
                        class="w-24 px-3 py-1.5 text-xs border-none focus:ring-0 text-center font-bold text-slate-700"
                        maxlength="7">
                    <button @click="showPicker = !showPicker"
                        class="px-2 py-1.5 hover:bg-slate-50 border-l border-slate-100 flex items-center text-slate-400">
                        <span class="material-symbols-outlined text-sm">calendar_month</span>
                    </button>
                </div>

                <!-- Month Picker Dropdown -->
                <div x-show="showPicker" @click.away="showPicker = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="absolute right-0 mt-2 p-4 bg-white border border-slate-200 shadow-2xl rounded-2xl z-50 w-64"
                    style="display: none;">

                    <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-50">
                        <button @click="changeYear(-1)" class="p-1 hover:bg-slate-100 rounded-lg"><span
                                class="material-symbols-outlined text-sm">chevron_left</span></button>
                        <span class="font-bold text-slate-900" x-text="pickerYear"></span>
                        <button @click="changeYear(1)" class="p-1 hover:bg-slate-100 rounded-lg"><span
                                class="material-symbols-outlined text-sm">chevron_right</span></button>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="m in 12">
                            <button @click="selectMonth(m)" class="py-2 text-xs rounded-xl transition-all"
                                :class="parseInt(pickerMonth) == m ? 'bg-indigo-600 text-white font-bold' : 'hover:bg-indigo-50 text-slate-600 font-medium'"
                                x-text="'Th ' + (m < 10 ? '0' + m : m)">
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="relative">
                <input type="text" id="searchProduct" placeholder="Tìm sản phẩm..."
                    class="pl-9 pr-4 py-1.5 border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 w-64"
                    onkeyup="filterRows()">
                <span
                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
            </div>
        </div>
    </header>

    <!-- Matrix Table Container -->
    <div class="flex-1 overflow-auto bg-white" x-data="gridApp()">
        <table class="grid-table min-w-max" id="mainGrid">
            <thead>
                <tr>
                    <th class="sticky-col w-[40px] text-center">STT</th>
                    <th class="sticky-col left-[40px] w-[300px] text-left">Tên sản phẩm</th>
                    <th class="w-[60px] text-center">ĐVT</th>
                    @foreach($departments as $dept)
                        <th class="w-[100px] text-center bg-indigo-50/50 min-w-[80px]">{{ $dept->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php $stt = 0; @endphp
                @foreach($products as $categoryId => $catProducts)
                    @php $category = $categories->firstWhere('id', $categoryId); @endphp
                    <tr class="bg-indigo-600 text-white font-bold uppercase text-[11px] tracking-widest category-row">
                        <td colspan="{{ 3 + $departments->count() }}" class="px-4 py-2 sticky-col bg-indigo-600">
                            {{ $category->name ?? 'CHUNG' }}
                        </td>
                    </tr>
                    @foreach($catProducts as $product)
                        @php $stt++; @endphp
                        <tr class="product-row" data-name="{{ strtolower($product->name) }}">
                            <td class="text-center text-slate-400 sticky-col">{{ $stt }}</td>
                            <td class="sticky-col left-[40px] font-medium px-4 py-2">{{ $product->name }}</td>
                            <td class="text-center text-slate-400">{{ $product->unit }}</td>
                            @foreach($departments as $dept)
                                @php
                                    $order = $product->monthlyOrders->firstWhere('department_id', $dept->id);
                                    $qty = $order ? $order->quantity : '';
                                @endphp
                                <td class="relative">
                                    <input type="number" step="0.1" value="{{ $qty }}"
                                        @change="saveQuantity($event, '{{ $product->id }}', '{{ $dept->id }}')" class="cell-input"
                                        placeholder="0">
                                    <div class="saving-indicator hidden">
                                        <span class="material-symbols-outlined text-[14px] text-emerald-500">check_circle</span>
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        function filterRows() {
            let query = document.getElementById('searchProduct').value.toLowerCase();
            let rows = document.querySelectorAll('.product-row');
            rows.forEach(row => {
                let name = row.getAttribute('data-name');
                row.style.display = name.includes(query) ? '' : 'none';
            });
        }

        function gridApp() {
            return {
                saveQuantity(event, productId, deptId) {
                    let input = event.target;
                    let value = input.value;
                    let cell = input.closest('td');
                    let indicator = cell.querySelector('.saving-indicator');

                    // Animation feedback
                    input.classList.add('bg-indigo-50');

                    fetch('{{ route("admin.grid-entry.update") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            department_id: deptId,
                            month: '{{ $selectedMonth }}',
                            quantity: value
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                indicator.classList.remove('hidden');
                                setTimeout(() => {
                                    indicator.classList.add('hidden');
                                    input.classList.remove('bg-indigo-50');
                                }, 1000);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            input.classList.add('bg-red-50');
                            alert('Lỗi lưu dữ liệu. Vui lòng thử lại.');
                        });
                }
            }
        }

        function monthPicker(initialMonth) {
            let [m, y] = initialMonth.split('/');
            return {
                displayMonth: initialMonth,
                pickerMonth: m,
                pickerYear: y,
                showPicker: false,

                changeYear(dir) {
                    this.pickerYear = parseInt(this.pickerYear) + dir;
                },

                selectMonth(m) {
                    this.pickerMonth = m < 10 ? '0' + m : m;
                    this.submitMonth();
                },

                formatAndSubmit() {
                    // Basic validation for MM/YYYY
                    if (/^\d{1,2}\/\d{4}$/.test(this.displayMonth)) {
                        let parts = this.displayMonth.split('/');
                        let mm = parts[0].padStart(2, '0');
                        this.displayMonth = mm + '/' + parts[1];
                        this.submitMonth();
                    }
                },

                submitMonth() {
                    let finalMonth = this.displayMonth;
                    if (this.showPicker) {
                        finalMonth = this.pickerMonth + '/' + this.pickerYear;
                    }
                    window.location.href = "{{ route('admin.grid-entry') }}?month=" + encodeURIComponent(finalMonth);
                }
            }
        }
    </script>
</body>

</html>