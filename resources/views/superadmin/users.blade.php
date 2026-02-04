<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quản lý người dùng - SuperAdmin</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
        .modal { display: none; }
        .modal.active { display: flex; }
    </style>
</head>

<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
            <div class="p-6 border-b">
                <div class="flex items-center gap-3">
                    <div class="bg-purple-600 p-2 rounded-lg text-white">
                        <span class="material-symbols-outlined">admin_panel_settings</span>
                    </div>
                    <h2 class="font-bold text-lg">SuperAdmin</h2>
                </div>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('superadmin.users') }}" class="flex items-center gap-3 px-4 py-3 bg-purple-600 text-white rounded-lg">
                    <span class="material-symbols-outlined">group</span>
                    <span>Quản lý người dùng</span>
                </a>
                <a href="{{ route('superadmin.data-management') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <span class="material-symbols-outlined">database</span>
                    <span>Quản lý dữ liệu</span>
                </a>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>
            </nav>
            <div class="p-4 border-t">
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full text-xs text-gray-500 hover:text-purple-600 text-left px-2 py-1">
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <!-- Header -->
            <header class="bg-white border-b px-8 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Quản lý người dùng</h1>
                        <p class="text-sm text-gray-500">Quản lý tất cả người dùng trong hệ thống</p>
                    </div>
                    <button onclick="openModal('addUserModal')" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">add</span>
                        Thêm người dùng
                    </button>
                </div>
            </header>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mx-8 mt-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mx-8 mt-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Users Table -->
            <div class="p-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tên</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Vai trò</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Khoa/Phòng</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Trạng thái</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="size-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-sm">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            <span class="font-medium">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $user->role === 'SuperAdmin' ? 'bg-purple-100 text-purple-800' : '' }}
                                            {{ $user->role === 'Admin' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $user->role === 'Department' ? 'bg-green-100 text-green-800' : '' }}">
                                            {{ $user->role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $user->department ? $user->department->name : '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <form action="{{ route('superadmin.users.toggle-status', $user) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 text-xs font-semibold rounded-full 
                                                {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $user->is_active ? 'Hoạt động' : 'Vô hiệu' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick='openEditModal(@json($user))' class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Sửa">
                                                <span class="material-symbols-outlined text-sm">edit</span>
                                            </button>
                                            <button onclick='openChangePasswordModal({{ $user->id }}, "{{ $user->name }}")' class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Đổi mật khẩu">
                                                <span class="material-symbols-outlined text-sm">key</span>
                                            </button>
                                            <form action="{{ route('superadmin.users.reset-password', $user) }}" method="POST" class="inline" onsubmit="return confirm('Reset mật khẩu về mặc định?')">
                                                @csrf
                                                <button type="submit" class="p-2 text-orange-600 hover:bg-orange-50 rounded-lg" title="Reset mật khẩu">
                                                    <span class="material-symbols-outlined text-sm">lock_reset</span>
                                                </button>
                                            </form>
                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('superadmin.users.delete', $user) }}" method="POST" class="inline" onsubmit="return confirm('Xóa người dùng này?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Xóa">
                                                        <span class="material-symbols-outlined text-sm">delete</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        Chưa có người dùng nào
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">Thêm người dùng mới</h3>
            <form action="{{ route('superadmin.users.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên</label>
                        <input type="text" name="name" required class="w-full border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" required class="w-full border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu</label>
                        <input type="password" name="password" required class="w-full border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu</label>
                        <input type="password" name="password_confirmation" required class="w-full border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vai trò</label>
                        <select name="role" required class="w-full border-gray-300 rounded-lg" onchange="toggleDepartment(this)">
                            <option value="SuperAdmin">SuperAdmin</option>
                            <option value="Admin">Admin</option>
                            <option value="Department">Department</option>
                        </select>
                    </div>
                    <div id="departmentField">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Khoa/Phòng</label>
                        <select name="department_id" class="w-full border-gray-300 rounded-lg">
                            <option value="">-- Chọn khoa/phòng --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeModal('addUserModal')" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Hủy
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Thêm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">Sửa thông tin người dùng</h3>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên</label>
                        <input type="text" name="name" id="edit_name" required class="w-full border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="edit_email" required class="w-full border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vai trò</label>
                        <select name="role" id="edit_role" required class="w-full border-gray-300 rounded-lg" onchange="toggleDepartmentEdit(this)">
                            <option value="SuperAdmin">SuperAdmin</option>
                            <option value="Admin">Admin</option>
                            <option value="Department">Department</option>
                        </select>
                    </div>
                    <div id="departmentFieldEdit">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Khoa/Phòng</label>
                        <select name="department_id" id="edit_department_id" class="w-full border-gray-300 rounded-lg">
                            <option value="">-- Chọn khoa/phòng --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeModal('editUserModal')" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Hủy
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">Đổi mật khẩu - <span id="password_user_name"></span></h3>
            <form id="changePasswordForm" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới</label>
                        <input type="password" name="password" required class="w-full border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu</label>
                        <input type="password" name="password_confirmation" required class="w-full border-gray-300 rounded-lg">
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeModal('changePasswordModal')" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Hủy
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Đổi mật khẩu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        function openEditModal(user) {
            document.getElementById('editUserForm').action = `/superadmin/users/${user.id}`;
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_department_id').value = user.department_id || '';
            toggleDepartmentEdit(document.getElementById('edit_role'));
            openModal('editUserModal');
        }

        function openChangePasswordModal(userId, userName) {
            document.getElementById('changePasswordForm').action = `/superadmin/users/${userId}/change-password`;
            document.getElementById('password_user_name').textContent = userName;
            openModal('changePasswordModal');
        }

        function toggleDepartment(select) {
            const deptField = document.getElementById('departmentField');
            deptField.style.display = select.value === 'Department' ? 'block' : 'none';
        }

        function toggleDepartmentEdit(select) {
            const deptField = document.getElementById('departmentFieldEdit');
            deptField.style.display = select.value === 'Department' ? 'block' : 'none';
        }

        // Initialize department field visibility
        document.addEventListener('DOMContentLoaded', function() {
            toggleDepartment(document.querySelector('#addUserModal select[name="role"]'));
        });
    </script>
</body>

</html>
