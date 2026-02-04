<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SuperAdminController extends Controller
{
    /**
     * Display user management page
     */
    public function users()
    {
        $users = User::with('department')->orderBy('created_at', 'desc')->get();
        $departments = Department::where('is_active', true)->get();
        
        return view('superadmin.users', compact('users', 'departments'));
    }

    /**
     * Store a new user
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:SuperAdmin,Admin,Department',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('superadmin.users')->with('success', 'Người dùng đã được tạo thành công!');
    }

    /**
     * Update user information
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:SuperAdmin,Admin,Department',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
        ]);

        $user->update($validated);

        return redirect()->route('superadmin.users')->with('success', 'Thông tin người dùng đã được cập nhật!');
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return redirect()->route('superadmin.users')->with('success', 'Mật khẩu đã được thay đổi!');
    }

    /**
     * Reset user password to default
     */
    public function resetPassword(User $user)
    {
        $defaultPassword = 'password'; // Default password
        
        $user->update([
            'password' => Hash::make($defaultPassword)
        ]);

        return redirect()->route('superadmin.users')->with('success', 'Mật khẩu đã được reset về "password"!');
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('superadmin.users')->with('error', 'Không thể xóa tài khoản của chính bạn!');
        }

        $user->delete();

        return redirect()->route('superadmin.users')->with('success', 'Người dùng đã được xóa!');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        $user->update([
            'is_active' => !$user->is_active
        ]);

        $status = $user->is_active ? 'kích hoạt' : 'vô hiệu hóa';
        return redirect()->route('superadmin.users')->with('success', "Tài khoản đã được {$status}!");
    }

    /**
     * Display data management page
     */
    public function dataManagement()
    {
        // Get list of backups
        $backupPath = storage_path('app/backups');
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $backups = collect(scandir($backupPath))
            ->filter(fn($file) => str_ends_with($file, '.sql'))
            ->map(function($file) use ($backupPath) {
                $fullPath = $backupPath . '/' . $file;
                return [
                    'name' => $file,
                    'size' => filesize($fullPath),
                    'date' => date('d/m/Y H:i:s', filemtime($fullPath))
                ];
            })
            ->sortByDesc('date')
            ->values();

        return view('superadmin.data-management', compact('backups'));
    }

    /**
     * Create database backup
     */
    public function createBackup()
    {
        try {
            $backupPath = storage_path('app/backups');
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $filename = 'backup_' . date('Y-m-d_His') . '.sql';
            $filepath = $backupPath . '/' . $filename;

            // Get database credentials
            $host = config('database.connections.mysql.host');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            // Create mysqldump command
            $command = sprintf(
                'mysqldump -h %s -u %s -p%s %s > %s',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($filepath)
            );

            exec($command, $output, $returnVar);

            if ($returnVar === 0) {
                return redirect()->route('superadmin.data-management')->with('success', 'Backup đã được tạo thành công!');
            } else {
                return redirect()->route('superadmin.data-management')->with('error', 'Không thể tạo backup!');
            }
        } catch (\Exception $e) {
            return redirect()->route('superadmin.data-management')->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Import data from Excel
     */
    public function importData(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
            'import_type' => 'required|in:products,categories,departments'
        ]);

        try {
            $file = $request->file('excel_file');
            $importType = $request->input('import_type');

            // Load Excel file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Remove header row
            array_shift($rows);

            $imported = 0;

            switch ($importType) {
                case 'products':
                    foreach ($rows as $row) {
                        if (!empty($row[0])) { // Check if name exists
                            \App\Models\Product::updateOrCreate(
                                ['name' => $row[0]],
                                [
                                    'unit' => $row[1] ?? '',
                                    'price' => $row[2] ?? 0,
                                    'category_id' => $row[3] ?? null,
                                ]
                            );
                            $imported++;
                        }
                    }
                    break;

                case 'categories':
                    foreach ($rows as $row) {
                        if (!empty($row[0])) {
                            \App\Models\Category::updateOrCreate(
                                ['name' => $row[0]],
                                [
                                    'display_order' => $row[1] ?? 0,
                                ]
                            );
                            $imported++;
                        }
                    }
                    break;

                case 'departments':
                    foreach ($rows as $row) {
                        if (!empty($row[0])) {
                            \App\Models\Department::updateOrCreate(
                                ['name' => $row[0]],
                                [
                                    'code' => $row[1] ?? strtoupper(substr($row[0], 0, 3)),
                                ]
                            );
                            $imported++;
                        }
                    }
                    break;
            }

            return redirect()->route('superadmin.data-management')->with('success', "Đã import {$imported} bản ghi thành công!");
        } catch (\Exception $e) {
            return redirect()->route('superadmin.data-management')->with('error', 'Lỗi import: ' . $e->getMessage());
        }
    }

    /**
     * Export data template
     */
    public function exportTemplate($type)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        switch ($type) {
            case 'products':
                $sheet->setCellValue('A1', 'Tên sản phẩm');
                $sheet->setCellValue('B1', 'Đơn vị');
                $sheet->setCellValue('C1', 'Đơn giá');
                $sheet->setCellValue('D1', 'ID Danh mục');
                $filename = 'template_products.xlsx';
                break;

            case 'categories':
                $sheet->setCellValue('A1', 'Tên danh mục');
                $sheet->setCellValue('B1', 'Thứ tự hiển thị');
                $filename = 'template_categories.xlsx';
                break;

            case 'departments':
                $sheet->setCellValue('A1', 'Tên khoa/phòng');
                $sheet->setCellValue('B1', 'Mã');
                $filename = 'template_departments.xlsx';
                break;

            default:
                abort(404);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
