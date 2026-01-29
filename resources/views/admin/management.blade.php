@extends('layouts.admin')

@section('title', 'Chức Năng Quản Lý | Hệ Thống Vật Tư Y Tế')

@section('page-title', 'Chức năng quản lý')

@section('content')
    <div x-data="{ 
        activeTab: 'categories',
        showModal: false,
        modalTitle: '',
        modalAction: '',
        modalMethod: 'POST',
        formData: {},

        openAddModal(tab) {
            this.modalAction = this.getStoreUrl(tab);
            this.modalMethod = 'POST';
            this.modalTitle = 'Thêm mới ' + this.getTabLabel(tab);
            this.formData = {};
            this.showModal = true;
        },

        openEditModal(tab, item) {
            this.modalAction = this.getUpdateUrl(tab, item);
            this.modalMethod = 'PUT';
            this.modalTitle = 'Chỉnh sửa ' + this.getTabLabel(tab);
            this.formData = {...item};
            this.showModal = true;
        },

        getTabLabel(tab) {
            const labels = {
                categories: 'Danh mục',
                suppliers: 'Nhà cung cấp',
                departments: 'Khoa phòng',
                users: 'Tài khoản'
            };
            return labels[tab];
        },

        getStoreUrl(tab) {
            const urls = {
                categories: '{{ route('admin.management.categories.store') }}',
                suppliers: '{{ route('admin.management.suppliers.store') }}',
                departments: '{{ route('admin.management.departments.store') }}',
                users: '{{ route('admin.management.users.store') }}'
            };
            return urls[tab];
        },

        getUpdateUrl(tab, item) {
            const id = item.category_id || item.supplier_id || item.department_id || item.user_id;
            // Fix for 500 error: manually build URL to avoid route missing parameter during Blade compilation if id is empty
            const baseUrls = {
                categories: '/admin/management/categories/',
                suppliers: '/admin/management/suppliers/',
                departments: '/admin/management/departments/',
                users: '/admin/management/users/'
            };
            return baseUrls[tab] + id;
        }
    }">
        <!-- Tabs Header -->
        <div class="flex border-b border-slate-200 dark:border-slate-800 mb-8 overflow-x-auto">
            <button @click="activeTab = 'categories'"
                :class="activeTab === 'categories' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-500 font-medium hover:text-slate-700'"
                class="px-6 py-3 border-b-2 text-sm transition-all whitespace-nowrap">
                Quản lý Danh mục
            </button>
            <button @click="activeTab = 'suppliers'"
                :class="activeTab === 'suppliers' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-500 font-medium hover:text-slate-700'"
                class="px-6 py-3 border-b-2 text-sm transition-all whitespace-nowrap">
                Quản lý Nhà cung cấp
            </button>
            <button @click="activeTab = 'departments'"
                :class="activeTab === 'departments' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-500 font-medium hover:text-slate-700'"
                class="px-6 py-3 border-b-2 text-sm transition-all whitespace-nowrap">
                Quản lý Khoa/Phòng
            </button>
            <button @click="activeTab = 'users'"
                :class="activeTab === 'users' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-500 font-medium hover:text-slate-700'"
                class="px-6 py-3 border-b-2 text-sm transition-all whitespace-nowrap">
                Quản lý Tài khoản
            </button>
        </div>

        <!-- Tab Contents -->
        <div
            class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">

            <!-- Tab: Categories -->
            <div x-show="activeTab === 'categories'" class="p-0">
                <div
                    class="p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/30">
                    <h3 class="font-bold text-slate-800 dark:text-white">Danh mục vật tư</h3>
                    <button @click="openAddModal('categories')"
                        class="px-4 py-2 bg-primary text-white text-sm font-bold rounded-lg">+ Thêm mới</button>
                </div>
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Mã</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Tên danh mục</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Nhà cung cấp chính</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($categories as $cat)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 text-sm font-bold text-primary">{{ $cat->category_code }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ $cat->category_name }}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">{{ $cat->supplier->supplier_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="openEditModal('categories', {{ json_encode($cat) }})"
                                        class="p-1.5 text-slate-400 hover:text-primary transition-colors"><span
                                            class="material-symbols-outlined">edit</span></button>
                                    <form action="{{ route('admin.management.categories.destroy', $cat->category_id) }}"
                                        method="POST" class="inline" onsubmit="return confirm('Xác nhận xóa?')">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 text-slate-400 hover:text-red-500 transition-colors"><span
                                                class="material-symbols-outlined">delete</span></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Tab: Suppliers -->
            <div x-show="activeTab === 'suppliers'" class="p-0">
                <div
                    class="p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/30">
                    <h3 class="font-bold text-slate-800 dark:text-white">Danh sách Nhà cung cấp</h3>
                    <button @click="openAddModal('suppliers')"
                        class="px-4 py-2 bg-primary text-white text-sm font-bold rounded-lg">+ Thêm mới</button>
                </div>
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Mã NCC</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Tên nhà cung cấp</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($suppliers as $sup)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 text-sm font-bold text-primary">{{ $sup->supplier_code }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ $sup->supplier_name }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="openEditModal('suppliers', {{ json_encode($sup) }})"
                                        class="p-1.5 text-slate-400 hover:text-primary transition-colors"><span
                                            class="material-symbols-outlined">edit</span></button>
                                    <form action="{{ route('admin.management.suppliers.destroy', $sup->supplier_id) }}"
                                        method="POST" class="inline" onsubmit="return confirm('Xác nhận xóa?')">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 text-slate-400 hover:text-red-500 transition-colors"><span
                                                class="material-symbols-outlined">delete</span></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Tab: Departments -->
            <div x-show="activeTab === 'departments'" class="p-0">
                <div
                    class="p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/30">
                    <h3 class="font-bold text-slate-800 dark:text-white">Danh sách Khoa/Phòng</h3>
                    <button @click="openAddModal('departments')"
                        class="px-4 py-2 bg-primary text-white text-sm font-bold rounded-lg">+ Thêm mới</button>
                </div>
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Mã Khoa</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Tên khoa/phòng</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($departments as $dept)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 text-sm font-bold text-primary">{{ $dept->department_code }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ $dept->department_name }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="openEditModal('departments', {{ json_encode($dept) }})"
                                        class="p-1.5 text-slate-400 hover:text-primary transition-colors"><span
                                            class="material-symbols-outlined">edit</span></button>
                                    <form action="{{ route('admin.management.departments.destroy', $dept->department_id) }}"
                                        method="POST" class="inline" onsubmit="return confirm('Xác nhận xóa?')">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 text-slate-400 hover:text-red-500 transition-colors"><span
                                                class="material-symbols-outlined">delete</span></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Tab: Users -->
            <div x-show="activeTab === 'users'" class="p-0">
                <div
                    class="p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/30">
                    <h3 class="font-bold text-slate-800 dark:text-white">Danh sách Người dùng</h3>
                    <button @click="openAddModal('users')"
                        class="px-4 py-2 bg-primary text-white text-sm font-bold rounded-lg">+ Thêm mới</button>
                </div>
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Username</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Họ tên</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Khoa phòng</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Vai trò</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($users as $user)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 text-sm font-bold text-primary">{{ $user->username }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ $user->full_name }}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ $user->department->department_name ?? 'Phòng Admin' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] uppercase font-bold {{ $user->role_code === 'ADMIN' ? 'bg-red-100 text-red-600' : ($user->role_code === 'DEPARTMENT' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600') }}">
                                        {{ $user->role_code }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="openEditModal('users', {{ json_encode($user) }})"
                                        class="p-1.5 text-slate-400 hover:text-primary transition-colors"><span
                                            class="material-symbols-outlined">edit</span></button>
                                    <form action="{{ route('admin.management.users.destroy', $user->user_id) }}" method="POST"
                                        class="inline" onsubmit="return confirm('Xác nhận xóa?')">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 text-slate-400 hover:text-red-500 transition-colors"><span
                                                class="material-symbols-outlined">delete</span></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Generic Modal for Management Functionality -->
        <div x-show="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
            style="display: none;">
            <div @click.away="showModal = false"
                class="bg-white dark:bg-slate-900 rounded-xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white" x-text="modalTitle"></h3>
                    <button @click="showModal = false"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><span
                            class="material-symbols-outlined">close</span></button>
                </div>
                <form :action="modalAction" method="POST" class="p-6 space-y-4">
                    @csrf
                    <template x-if="modalMethod === 'PUT'"><input type="hidden" name="_method" value="PUT"></template>

                    <!-- Category Fields -->
                    <template x-if="activeTab === 'categories'">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Mã danh
                                    mục</label>
                                <input type="text" name="category_code" x-model="formData.category_code" required
                                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Tên danh
                                    mục</label>
                                <input type="text" name="category_name" x-model="formData.category_name" required
                                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nhà cung
                                    cấp chính</label>
                                <select name="supplier_id" x-model="formData.supplier_id"
                                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg">
                                    <option value="">Chọn NCC</option>
                                    @foreach($suppliers as $sup)
                                        <option value="{{ $sup->supplier_id }}">{{ $sup->supplier_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </template>

                    <!-- Supplier Fields -->
                    <template x-if="activeTab === 'suppliers'">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Mã
                                    NCC</label>
                                <input type="text" name="supplier_code" x-model="formData.supplier_code" required
                                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Tên
                                    NCC</label>
                                <input type="text" name="supplier_name" x-model="formData.supplier_name" required
                                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg">
                            </div>
                        </div>
                    </template>

                    <!-- Department Fields -->
                    <template x-if="activeTab === 'departments'">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Mã
                                    Khoa</label>
                                <input type="text" name="department_code" x-model="formData.department_code" required
                                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Tên
                                    Khoa</label>
                                <input type="text" name="department_name" x-model="formData.department_name" required
                                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg">
                            </div>
                        </div>
                    </template>

                    <!-- User Fields -->
                    <template x-if="activeTab === 'users'">
                        <div class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Username</label>
                                <input type="text" name="username" x-model="formData.username"
                                    :disabled="modalMethod === 'PUT'" required
                                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg disabled:opacity-50">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Họ
                                    tên</label>
                                <input type="text" name="full_name" x-model="formData.full_name" required
                                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Mật khẩu
                                    (để trống nếu không đổi)</label>
                                <input type="password" name="password"
                                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Vai
                                    trò</label>
                                <select name="role_code" x-model="formData.role_code" required
                                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg">
                                    <option value="ADMIN">ADMIN</option>
                                    <option value="DEPARTMENT">DEPARTMENT (Trưởng khoa)</option>
                                    <option value="BUYER">BUYER (Nhà thầu/Thu mua)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Khoa
                                    phòng</label>
                                <select name="department_id" x-model="formData.department_id"
                                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg">
                                    <option value="">Không có/Tất cả (Admin)</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </template>

                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="showModal = false"
                            class="flex-1 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg font-bold">Hủy</button>
                        <button type="submit" class="flex-1 py-2 bg-primary text-white rounded-lg font-bold">Lưu thay
                            đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection