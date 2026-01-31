<!-- Personal Information Modal -->
<div id="personalInfoModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            onclick="hideModal('personalInfoModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900" id="modal-title">Thông tin cá nhân</h3>
                    <button type="button" onclick="hideModal('personalInfoModal')"
                        class="text-gray-400 hover:text-gray-500">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="profileUpdateForm" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Họ và tên</label>
                        <input type="text" name="full_name" value="{{ auth()->user()->full_name }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary h-11">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ auth()->user()->email }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary h-11">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Khoa / Phòng</label>
                        <input type="text" value="{{ auth()->user()->department->department_name ?? 'N/A' }}" disabled
                            class="w-full rounded-lg border-gray-200 bg-gray-50 text-gray-500 h-11 cursor-not-allowed">
                    </div>
                    <div id="profileError" class="hidden text-sm text-red-600 font-medium"></div>
                    <div id="profileSuccess" class="hidden text-sm text-green-600 font-medium"></div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3">
                <button type="button" onclick="submitProfileUpdate()"
                    class="inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2.5 bg-primary text-base font-bold text-white hover:bg-blue-700 focus:outline-none sm:text-sm transition-all">Lưu
                    thay đổi</button>
                <button type="button" onclick="hideModal('personalInfoModal')"
                    class="inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-6 py-2.5 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none sm:text-sm transition-all">Hủy</button>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div id="changePasswordModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            onclick="hideModal('changePasswordModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900" id="modal-title">Đổi mật khẩu</h3>
                    <button type="button" onclick="hideModal('changePasswordModal')"
                        class="text-gray-400 hover:text-gray-500">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="passwordUpdateForm" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Mật khẩu hiện tại</label>
                        <input type="password" name="current_password"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary h-11">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Mật khẩu mới</label>
                        <input type="password" name="new_password"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary h-11">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Xác nhận mật khẩu mới</label>
                        <input type="password" name="new_password_confirmation"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary h-11">
                    </div>
                    <div id="passwordError" class="hidden text-sm text-red-600 font-medium"></div>
                    <div id="passwordSuccess" class="hidden text-sm text-green-600 font-medium"></div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3">
                <button type="button" onclick="submitPasswordUpdate()"
                    class="inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2.5 bg-primary text-base font-bold text-white hover:bg-blue-700 focus:outline-none sm:text-sm transition-all">Đổi
                    mật khẩu</button>
                <button type="button" onclick="hideModal('changePasswordModal')"
                    class="inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-6 py-2.5 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none sm:text-sm transition-all">Hủy</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.body.style.overflow = 'auto';
        // Reset forms
        if (id === 'personalInfoModal') {
            document.getElementById('profileError').classList.add('hidden');
            document.getElementById('profileSuccess').classList.add('hidden');
        } else if (id === 'changePasswordModal') {
            document.getElementById('passwordUpdateForm').reset();
            document.getElementById('passwordError').classList.add('hidden');
            document.getElementById('passwordSuccess').classList.add('hidden');
        }
    }

    async function submitProfileUpdate() {
        const form = document.getElementById('profileUpdateForm');
        const errorDiv = document.getElementById('profileError');
        const successDiv = document.getElementById('profileSuccess');
        const formData = new FormData(form);

        errorDiv.classList.add('hidden');
        successDiv.classList.add('hidden');

        try {
            const response = await fetch('{{ route('profile.update') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (response.ok) {
                successDiv.innerText = data.message;
                successDiv.classList.remove('hidden');
                // Update names in dropdowns if needed
                setTimeout(() => {
                    location.reload(); // Simple way to update UI
                }, 1000);
            } else {
                errorDiv.innerText = data.message || Object.values(data.errors).flat()[0];
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            errorDiv.innerText = 'Đã có lỗi xảy ra. Vui lòng thử lại sau.';
            errorDiv.classList.remove('hidden');
        }
    }

    async function submitPasswordUpdate() {
        const form = document.getElementById('passwordUpdateForm');
        const errorDiv = document.getElementById('passwordError');
        const successDiv = document.getElementById('passwordSuccess');
        const formData = new FormData(form);

        errorDiv.classList.add('hidden');
        successDiv.classList.add('hidden');

        try {
            const response = await fetch('{{ route('password.update') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (response.ok) {
                successDiv.innerText = data.message;
                successDiv.classList.remove('hidden');
                setTimeout(() => {
                    hideModal('changePasswordModal');
                }, 1500);
            } else {
                errorDiv.innerText = data.message || Object.values(data.errors).flat()[0];
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            errorDiv.innerText = 'Đã có lỗi xảy ra. Vui lòng thử lại sau.';
            errorDiv.classList.remove('hidden');
        }
    }
</script>