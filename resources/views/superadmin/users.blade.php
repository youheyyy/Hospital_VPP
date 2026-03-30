@extends('layouts.superadmin')

@section('title', 'Quản lý người dùng')

@section('styles')
<style>
    .modal {
        display: none;
    }

    .modal.active {
        display: flex;
    }
</style>
@endsection

@section('content')
<!-- Header -->
<header class="bg-white border-b px-8 py-4 sticky top-0 z-30">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Quản lý người dùng</h1>
            <p class="text-sm text-gray-500">Quản lý tất cả người dùng trong hệ thống</p>
        </div>
        <button onclick="openModal('addUserModal')"
            class="px-5 py-2.5 bg-purple-600 text-white rounded-xl shadow-lg shadow-purple-200 hover:bg-purple-700 hover:shadow-purple-300 transition-all flex items-center gap-2 font-semibold">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Thêm người dùng
        </button>
    </div>
</header>

<!-- Success/Error Messages -->
<div class="px-8 mt-4">
    @if(session('success'))
    <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-center gap-2">
        <span class="material-symbols-outlined text-[20px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-2">
        <span class="material-symbols-outlined text-[20px]">error</span>
        {{ session('error') }}
    </div>
    @endif
</div>

<!-- Users Table -->
<div class="p-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50/50 border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Tên</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Email</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Vai trò</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Khoa/Phòng</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Trạng thái</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="size-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-sm">
                                {{ mb_strtoupper(mb_substr($user->name, 0, 2, 'UTF-8'), 'UTF-8') }}
                            </div>
                            <span class="font-bold text-slate-900">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 font-medium">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        <span
                            class="px-3 py-1 text-[10px] font-bold rounded-full tracking-wider uppercase
                                                    {{ $user->role === 'SuperAdmin' ? 'bg-purple-100 text-purple-800' : '' }}
                                                    {{ $user->role === 'Admin' ? 'bg-blue-100 text-blue-800' : '' }}
                                                    {{ $user->role === 'Department' ? 'bg-green-100 text-green-800' : '' }}">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                        {{ $user->department ? $user->department->name : '-' }}
                    </td>
                    <td class="px-6 py-4">
                        <form action="{{ route('superadmin.users.toggle-status', $user) }}" method="POST"
                            class="inline">
                            @csrf
                            <button type="submit"
                                class="px-2.5 py-1 text-[10px] font-bold rounded-full tracking-wider uppercase transition-colors
                                                        {{ $user->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                {{ $user->is_active ? 'Hoạt động' : 'Vô hiệu' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button onclick='openEditModal(@json($user))'
                                class="p-2 text-blue-500 hover:bg-blue-50 rounded-xl transition-colors" title="Sửa">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>

                            <form action="{{ route('superadmin.users.reset-password', $user) }}" method="POST"
                                class="inline" onsubmit="return confirm('Reset mật khẩu về mặc định?')">
                                @csrf
                                <button type="submit"
                                    class="p-2 text-orange-500 hover:bg-orange-50 rounded-xl transition-colors"
                                    title="Reset mật khẩu">
                                    <span class="material-symbols-outlined text-[20px]">lock_reset</span>
                                </button>
                            </form>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('superadmin.users.delete', $user) }}" method="POST" class="inline"
                                onsubmit="return confirm('Xóa người dùng này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="p-2 text-red-500 hover:bg-red-50 rounded-xl transition-colors" title="Xóa">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                        Chưa có người dùng nào trong hệ thống
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modals -->
<div id="addUserModal" class="modal fixed inset-0 bg-slate-900/40 backdrop-blur-sm items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl">
        <h3 class="text-xl font-bold text-slate-900 mb-6">Thêm người dùng mới</h3>
        <form action="{{ route('superadmin.users.store') }}" method="POST">
            @csrf
            <div class="space-y-4 text-left">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Họ và tên</label>
                    <input type="text" name="name" required
                        class="w-full border-gray-200 rounded-xl focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Email</label>
                    <input type="email" name="email" required
                        class="w-full border-gray-200 rounded-xl focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Mật khẩu</label>
                    <input type="password" name="password" required
                        class="w-full border-gray-200 rounded-xl focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Xác nhận mật khẩu</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full border-gray-200 rounded-xl focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Vai trò</label>
                    <select name="role" required
                        class="w-full border-gray-200 rounded-xl focus:ring-purple-500 focus:border-purple-500"
                        onchange="toggleDepartment(this)">
                        <option value="SuperAdmin">SuperAdmin</option>
                        <option value="Admin">Admin</option>
                        <option value="Department">Department</option>
                    </select>
                </div>
                <div id="departmentField" style="display:none">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Khoa/Phòng</label>
                    <select name="department_id"
                        class="w-full border-gray-200 rounded-xl focus:ring-purple-500 focus:border-purple-500">
                        <option value="">-- Chọn khoa/phòng --</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-3 mt-8">
                <button type="button" onclick="closeModal('addUserModal')"
                    class="flex-1 px-4 py-2 border border-gray-200 font-bold text-gray-400 hover:bg-gray-50 rounded-xl transition-colors">Hủy</button>
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-purple-600 text-white font-bold rounded-xl hover:bg-purple-700 transition-all shadow-lg shadow-purple-100">Thêm</button>
            </div>
        </form>
    </div>
</div>

<div id="editUserModal" class="modal fixed inset-0 bg-slate-900/40 backdrop-blur-sm items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl">
        <h3 class="text-xl font-bold text-slate-900 mb-6 font-bold">Sửa thông tin</h3>
        <form id="editUserForm" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4 text-left">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tên</label>
                    <input type="text" name="name" id="edit_name" required class="w-full border-gray-200 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Email</label>
                    <input type="email" name="email" id="edit_email" required class="w-full border-gray-200 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Vai trò</label>
                    <select name="role" id="edit_role" required class="w-full border-gray-200 rounded-xl"
                        onchange="toggleDepartmentEdit(this)">
                        <option value="SuperAdmin">SuperAdmin</option>
                        <option value="Admin">Admin</option>
                        <option value="Department">Department</option>
                    </select>
                </div>
                <div id="departmentFieldEdit" style="display:none">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Khoa/Phòng</label>
                    <select name="department_id" id="edit_department_id" class="w-full border-gray-200 rounded-xl">
                        <option value="">-- Chọn khoa/phòng --</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-3 mt-8">
                <button type="button" onclick="closeModal('editUserModal')"
                    class="flex-1 px-4 py-2 font-bold text-gray-400 border border-gray-200 rounded-xl">Hủy</button>
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-blue-600 font-bold text-white rounded-xl shadow-lg shadow-blue-100">Cập
                    nhật</button>
            </div>
        </form>
    </div>
</div>


@endsection

@section('scripts')
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



    function toggleDepartment(select) {
        const deptField = document.getElementById('departmentField');
        deptField.style.display = select.value === 'Department' ? 'block' : 'none';
    }

    function toggleDepartmentEdit(select) {
        const deptField = document.getElementById('departmentFieldEdit');
        deptField.style.display = select.value === 'Department' ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.querySelector('#addUserModal select[name="role"]');
        if (roleSelect) toggleDepartment(roleSelect);
    });
</script>
@endsection