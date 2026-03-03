<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\MonthlyOrder;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuperAdminController extends Controller
{
    /**
     * Display dashboard with real data
     */
    public function dashboard()
    {
        $totalDepartments = Department::count();
        $totalUsers = User::count();
        $currentMonth = now()->format('m/Y');
        $monthlyOrdersCount = MonthlyOrder::where('month', $currentMonth)->sum('quantity');

        // Get recent activity (Load enough for the "View All" modal)
        $recentActivities = ActivityLog::with('user.department')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        // Get monthly order trends (Ensure 6 months are always shown)
        $monthlyTrendsData = MonthlyOrder::leftJoin('products', 'monthly_orders.product_id', '=', 'products.id')
            ->select(
                'monthly_orders.month',
                DB::raw("CAST(SUM(monthly_orders.quantity) AS UNSIGNED) as total_qty"),
                DB::raw("SUM(monthly_orders.quantity * IFNULL(products.price, 0)) as total_value")
            )
            ->whereIn('monthly_orders.month', collect(range(0, 5))->map(fn($i) => now()->subMonths($i)->format('m/Y')))
            ->groupBy('monthly_orders.month')
            ->get()
            ->keyBy('month');

        $monthlyTrends = collect(range(0, 5))->map(function ($i) use ($monthlyTrendsData) {
            $monthStr = now()->subMonths($i)->format('m/Y');
            return (object) [
                'month' => $monthStr,
                'total_qty' => $monthlyTrendsData->has($monthStr) ? (float) $monthlyTrendsData[$monthStr]->total_qty : 0,
                'total_value' => $monthlyTrendsData->has($monthStr) ? (float) $monthlyTrendsData[$monthStr]->total_value : 0
            ];
        })->reverse()->values();

        return view('superadmin.dashboard', compact(
            'totalDepartments',
            'totalUsers',
            'monthlyOrdersCount',
            'recentActivities',
            'monthlyTrends'
        ));
    }

    private function logActivity($action, $description)
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'created_at' => now()
        ]);
    }

    /**
     * Ensure a department has a user account
     * Format: CLEANNAME@hospital.com / password
     */
    private function ensureDepartmentUser(Department $department)
    {
        $cleanName = $this->cleanString($department->name);
        $email = strtoupper($cleanName) . '@hospital.com';

        // Check if user exists
        $user = User::where('email', $email)->first();

        if (!$user) {
            User::create([
                'name' => $department->name,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'Department',
                'department_id' => $department->id,
                'is_active' => true,
            ]);
            Log::info("Created auto-account for department: {$email}");
        } else {
            // Update department ID if it was null or wrong, but don't error out
            $user->update(['department_id' => $department->id, 'role' => 'Department']);
        }
    }



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
        $this->logActivity('Thêm người dùng', "Đã tạo tài khoản mới: {$validated['email']}");

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
        $this->logActivity('Cập nhật người dùng', "Đã cập nhật thông tin cho người dùng: {$user->email}");

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
        $this->logActivity('Xóa người dùng', "Đã xóa người dùng: {$user->email}");

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
        // Clear file status cache to ensure correct file sizes are obtained
        clearstatcache();

        // Get list of backups
        $backupPath = storage_path('app/backups');
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        // SELF-HEALING: Check if V2 is missing for today (e.g. if user didn't logout/login)
        $date = date('Y-m-d');
        $filenameV1 = "backup_{$date}_auto_v1.sql";
        $filenameV2 = "backup_{$date}_auto_v2.sql";

        // 1. Refresh V1 (Main Copy) on page load so user sees latest size/data
        // This ensures V1 is always "fresh" when viewing the list
        // 1. REALTIME REFRESH V1 (Force create new V1)
        // This ensures V1 is always "fresh" when viewing the list
        $host = config('database.connections.mysql.host');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $port = config('database.connections.mysql.port');

        try {
            // Force create V1 (Overwrite existing)
            $dump = new \Ifsnop\Mysqldump\Mysqldump(
                "mysql:host={$host};port={$port};dbname={$database}",
                $username,
                $password,
                [
                    'compress' => \Ifsnop\Mysqldump\Mysqldump::NONE,
                    'add-drop-table' => true
                ]
            );
            $dump->start($backupPath . '/' . $filenameV1);

            // Also ensure V2 exists (Sync if missing)
            if (!file_exists($backupPath . '/' . $filenameV2)) {
                copy($backupPath . '/' . $filenameV1, $backupPath . '/' . $filenameV2);
            }

            // FLASH SUCCESS MESSAGE FOR REALTIME UPDATE
            // session()->flash('success', 'Hệ thống đã tự động cập nhật dữ liệu mới nhất (Realtime)');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Auto refresh V1 failed: ' . $e->getMessage());
        }

        $backups = collect(scandir($backupPath))
            ->filter(fn($file) => str_ends_with($file, '.sql'))
            ->map(function ($file) use ($backupPath) {
                $fullPath = $backupPath . '/' . $file;
                return [
                    'name' => $file,
                    'size' => filesize($fullPath),
                    'date' => date('d/m/Y H:i:s', filemtime($fullPath))
                ];
            })
            ->sortByDesc('date')
            ->values();

        // Get backup config
        $configPath = storage_path('app/backup_config.json');
        $backupConfig = file_exists($configPath) ? json_decode(file_get_contents($configPath), true) : ['minutes' => 0, 'seconds' => 0];

        return view('superadmin.data-management', compact('backups', 'backupConfig'));
    }

    /**
     * Update backup configuration
     */
    public function updateBackupConfig(Request $request)
    {
        $request->validate([
            'interval_minutes' => 'required|integer|min:0',
            'interval_seconds' => 'required|integer|min:0|max:59',
        ]);

        $config = [
            'minutes' => (int) $request->interval_minutes,
            'seconds' => (int) $request->interval_seconds,
            'last_backup_at' => time() // Reset timer
        ];

        file_put_contents(storage_path('app/backup_config.json'), json_encode($config));

        // Also update the auto-backup timestamp to now to prevent immediate trigger
        // actually last_backup_at in config is enough if we use that.

        return redirect()->route('superadmin.data-management')->with('success', 'Cấu hình tự động backup đã được lưu!');
    }

    /**
     * Upload and restore backup
     */
    public function uploadBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,txt', // .sql is often text/plain
        ]);

        try {
            $file = $request->file('backup_file');
            $filename = 'upload_restore_' . date('Y-m-d_His') . '.sql';
            $path = storage_path('app/backups/' . $filename);

            // Move uploaded file to backups folder
            $file->move(storage_path('app/backups'), $filename);

            // Trigger restore
            // We can reuse the restore logic, but let's call it directly or redirect
            // Calling restoreBackup requires a filename.

            // Let's reuse restoreBackup logic by calling it internally or redirecting
            // But restoreBackup expects a filename in route.
            // Let's just call the restore logic here to avoid redirect loops or route issues.

            // --- RESTORE LOGIC (Copied/Refactored from restoreBackup) ---
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // WIPE DATABASE
            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
            }

            // Read and Execute SQL
            $sql = file_get_contents($path);
            DB::unprepared($sql);

            // Enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Re-login user (current user)
            $currentUserId = auth()->id();
            if ($currentUserId) {
                $user = User::find($currentUserId);
                if ($user) {
                    \Illuminate\Support\Facades\Auth::login($user);
                }
            }

            $this->logActivity('Restore Database', "Đã khôi phục dữ liệu từ file upload: {$file->getClientOriginalName()}");

            return redirect()->route('superadmin.data-management')->with('success', 'Đã khôi phục dữ liệu thành công từ file upload!');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Upload restore failed: ' . $e->getMessage());
            return redirect()->route('superadmin.data-management')->with('error', 'Lỗi khôi phục: ' . $e->getMessage());
        }
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
            $port = config('database.connections.mysql.port');

            try {
                $dump = new \Ifsnop\Mysqldump\Mysqldump(
                    "mysql:host={$host};port={$port};dbname={$database}",
                    $username,
                    $password,
                    [
                        'compress' => \Ifsnop\Mysqldump\Mysqldump::NONE,
                        'add-drop-table' => true
                    ]
                );
                $dump->start($filepath);

                $this->logActivity('Backup Database', "Đã tạo bản sao lưu: {$filename}");
                return redirect()->route('superadmin.data-management')->with('success', 'Backup đã được tạo thành công!');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Backup failed: ' . $e->getMessage());
                return redirect()->route('superadmin.data-management')->with('error', 'Lỗi khi tạo backup: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            return redirect()->route('superadmin.data-management')->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Download backup file
     */
    public function downloadBackup($filename)
    {
        $path = storage_path('app/backups/' . $filename);

        // SMART DOWNLOAD LOGIC:
        // Only refresh V1 (Main Copy) to provide latest data.
        // V2 (Redundant Copy) MUST remain a static snapshot of the morning (Login time) to allow restore if data is deleted during the day.
        $today = date('Y-m-d');
        if (str_contains($filename, "backup_{$today}_auto_") && !str_contains($filename, '_v2.sql')) {
            try {
                // Determine file version (v1 or v2)
                // Actually, we should force update both or just the requested one.
                // Let's update the requested file specifically.

                $host = config('database.connections.mysql.host');
                $database = config('database.connections.mysql.database');
                $username = config('database.connections.mysql.username');
                $password = config('database.connections.mysql.password');
                $port = config('database.connections.mysql.port');

                // Re-dump specifically to this file path
                $dump = new \Ifsnop\Mysqldump\Mysqldump(
                    "mysql:host={$host};port={$port};dbname={$database}",
                    $username,
                    $password,
                    [
                        'compress' => \Ifsnop\Mysqldump\Mysqldump::NONE,
                        'add-drop-table' => true
                    ]
                );
                $dump->start($path);

                // If it's v1, we should probably also update v2 to keep them in sync, 
                // but let's just focus on the file the user is downloading to be safe.
                // If the file didn't exist, start() created it.

            } catch (\Exception $e) {
                // If refresh fails, try to download existing file if it exists, otherwise error
                \Illuminate\Support\Facades\Log::error('Smart download refresh failed: ' . $e->getMessage());
            }
        }

        if (!file_exists($path)) {
            return redirect()->route('superadmin.data-management')->with('error', 'File không tồn tại!');
        }

        return response()->download($path);
    }

    /**
     * Delete backup file
     */
    public function deleteBackup($filename)
    {
        // STRICT PROTECTION: Prevent deleting auto-generated backups
        if (str_contains($filename, '_auto_')) {
            return redirect()->route('superadmin.data-management')->with('error', 'Không thể xóa bản sao lưu tự động! Đây là dữ liệu quan trọng.');
        }

        $path = storage_path('app/backups/' . $filename);

        if (file_exists($path)) {
            unlink($path);
            $this->logActivity('Xóa Backup', "Đã xóa bản sao lưu: {$filename}");
            return redirect()->route('superadmin.data-management')->with('success', 'File backup đã được xóa thành công!');
        }

        return redirect()->route('superadmin.data-management')->with('error', 'File không tồn tại!');
    }

    /**
     * Restore database from backup file
     */
    public function restoreBackup($filename)
    {
        $path = storage_path('app/backups/' . $filename);

        if (!file_exists($path)) {
            return redirect()->route('superadmin.data-management')->with('error', 'File backup không tồn tại!');
        }

        // Capture current user ID to re-login after restore (since sessions table might be wiped)
        $currentUserId = auth()->id();

        try {
            // Disable foreign key checks to prevent errors during restore
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // WIPE DATABASE: Drop all tables to ensure clean state
            // This fixes "Table already exists" error if backup file doesn't have DROP TABLE
            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
            }

            // Read the SQL file
            $sql = file_get_contents($path);

            // Execute the SQL statements
            // Note: DB::unprepared is suitable for raw SQL dumps provided they are not too massive for memory.
            // Since our DB is small/medium, this is fine and reliable.
            DB::unprepared($sql);

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // RE-LOGIN USER:
            // Since the sessions table was likely wiped or replaced, the current session is invalid.
            // We manually re-login the user to prevent them from being kicked out.
            if ($currentUserId) {
                $user = User::find($currentUserId);
                if ($user) {
                    \Illuminate\Support\Facades\Auth::login($user);
                }
            }

            $this->logActivity('Restore Database', "Đã khôi phục dữ liệu từ file: {$filename}");

            // Clear cache/session if needed, but usually not required for this simple app
            return redirect()->route('superadmin.data-management')->with('success', "Đã khôi phục dữ liệu thành công từ bản: {$filename}");

        } catch (\Exception $e) {
            // Ensure FK checks are re-enabled even if error occurs
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Exception $ex) {
            }

            \Illuminate\Support\Facades\Log::error('Restore failed: ' . $e->getMessage());
            return redirect()->route('superadmin.data-management')->with('error', 'Lỗi khi khôi phục dữ liệu: ' . $e->getMessage());
        }
    }

    /**
     * Auto backup daily (Called from LoginController)
     */
    public static function autoBackupDaily()
    {
        try {
            $backupPath = storage_path('app/backups');
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $date = date('Y-m-d');
            $filenameV1 = "backup_{$date}_auto_v1.sql";
            $filenameV2 = "backup_{$date}_auto_v2.sql";

            // Check if backup V1 for today already exists
            if (file_exists($backupPath . '/' . $filenameV1)) {
                // If V1 exists but V2 is missing (legacy case or error), create V2 from V1
                if (!file_exists($backupPath . '/' . $filenameV2)) {
                    copy($backupPath . '/' . $filenameV1, $backupPath . '/' . $filenameV2);
                }
                return; // Already backed up today
            }

            // Get database credentials
            $host = config('database.connections.mysql.host');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $port = config('database.connections.mysql.port');

            // Create mysqldump for V1
            $filepathV1 = $backupPath . '/' . $filenameV1;

            try {
                $dump = new \Ifsnop\Mysqldump\Mysqldump(
                    "mysql:host={$host};port={$port};dbname={$database}",
                    $username,
                    $password,
                    [
                        'compress' => \Ifsnop\Mysqldump\Mysqldump::NONE,
                        'add-drop-table' => true
                    ]
                );
                $dump->start($filepathV1);

                // Copy to V2 for redundancy
                copy($filepathV1, $backupPath . '/' . $filenameV2);

                \App\Models\ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'Auto Backup',
                    'description' => "Hệ thống tự động sao lưu 2 bản: {$filenameV1}, {$filenameV2}",
                    'created_at' => now()
                ]);

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Auto backup failed: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            // Silently fail or log error to file
            \Illuminate\Support\Facades\Log::error('Auto backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Import Advanced (Multi-sheet) — Two-Pass Strategy
     *
     * PASS 1: Read TỔNG HỢP sheet only → extract products, prices, categories (suppliers)
     *         Row-by-row, top-to-bottom: colored row = supplier/category, white row = product
     *
     * PASS 2: Read each department sheet → extract quantities only
     *         Match product by name, create MonthlyOrder records
     */
    public function importAdvanced(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
            'month' => 'required|date_format:Y-m',
        ]);

        try {
            set_time_limit(0);
            ini_set('memory_limit', '1024M');

            $file = $request->file('excel_file');
            $monthInput = $request->input('month');
            $monthParts = explode('-', $monthInput);
            $targetMonth = "{$monthParts[1]}/{$monthParts[0]}"; // e.g. "03/2026"

            // Read styles AND data (do NOT use setReadDataOnly(true) or colors won't be readable)
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getPathname());
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($file->getPathname());

            // ── Wipe old orders for this month (prevents "ghost" data) ──────────────
            \App\Models\MonthlyOrder::where('month', $targetMonth)->delete();
            Log::info("Import: Wiped all orders for $targetMonth.");

            // ── Pre-load departments into a hash map ─────────────────────────────────
            $deptMap = [];
            foreach (\App\Models\Department::all() as $d) {
                $deptMap[strtoupper(\Illuminate\Support\Str::slug($d->code))] = $d;
                $deptMap[strtoupper(\Illuminate\Support\Str::slug($d->name))] = $d;
            }

            $sheetNames = $spreadsheet->getSheetNames();
            $masterName = null; // will be set to the TỔNG HỢP sheet name
            $deptSheets = [];   // [sheetName => Department object]

            Log::info("Import: All sheets in file: " . implode(', ', $sheetNames));

            foreach ($sheetNames as $sheetName) {
                $cleanName = $this->cleanStringForMatch($sheetName);

                // Identify master/summary sheet
                if (str_contains($cleanName, 'TONGHOP')) {
                    $masterName = $sheetName;
                    Log::info("Import: Matched MASTER sheet -> '{$sheetName}' (Clean: {$cleanName})");
                    continue;
                }

                // Skip known non-data sheets
                if (
                    str_contains($cleanName, 'BANGTONG') ||
                    str_contains($cleanName, 'CONSOLIDATED') ||
                    in_array($cleanName, ['HIGHLIGHTS', 'GHICHU', 'SUMMARY']) ||
                    preg_match('/^SHEET\d+$/', $cleanName)
                ) {
                    Log::info("Import: Explicitly SKIPPING non-department sheet -> '{$sheetName}'");
                    continue;
                }
                // Map sheet to department
                $slug = strtoupper(\Illuminate\Support\Str::slug($sheetName));
                $dept = $deptMap[$slug] ?? null;
                if (!$dept) {
                    $dept = \App\Models\Department::where('code', $slug)
                        ->orWhere('name', $sheetName)->first();
                    if (!$dept) {
                        try {
                            $dept = \App\Models\Department::create(['code' => $slug, 'name' => $sheetName]);
                            $this->ensureDepartmentUser($dept);
                        } catch (\Exception $e) {
                            $dept = \App\Models\Department::where('code', $slug)->first();
                        }
                    }
                    if ($dept) {
                        $deptMap[$slug] = $dept;
                    }
                }
                if ($dept) {
                    $deptSheets[$sheetName] = $dept;
                }
            }

            // ════════════════════════════════════════════════════════════════════════
            // PASS 1 — Master sheet (TỔNG HỢP)
            //   Read row by row, top → bottom, left → right:
            //   • Colored cell in name column AND no ĐVT/Qty/Price → Supplier/Category row
            //   • Otherwise → Product row (save name, unit, price, category)
            // ════════════════════════════════════════════════════════════════════════
            // [productCleanKey => Product model]
            $productMap = [];

            if ($masterName) {
                $masterSheet = $spreadsheet->getSheetByName($masterName);
                if ($masterSheet) {
                    $this->parseSheetForProducts($masterSheet, $deptMap, $productMap);
                    Log::info("Import PASS 1: Processed master sheet '{$masterName}', found " . count($productMap) . " products.");
                } else {
                    Log::error("Import ERROR: Master sheet '{$masterName}' found by name but could not be loaded!");
                }
            } else {
                Log::warning("Import WARNING: NO MASTER SHEET (TONG HOP) FOUND! Products will default to 'Khoa đề xuất' and categorisation will fail.");
            }

            // ════════════════════════════════════════════════════════════════════════
            // PASS 2 — Department sheets
            //   Read row by row, top → bottom, left → right:
            //   • Same supplier/category detection (sets context for this sheet)
            //   • Product row: match by name, read Qty, create MonthlyOrder
            //   • Prices from department sheets are IGNORED (master is authoritative)
            // ════════════════════════════════════════════════════════════════════════
            $ordersToInsert = [];
            $processedDepts = count($deptSheets);
            $processedOrders = 0;

            foreach ($deptSheets as $sheetName => $dept) {
                Log::info("DEBUG IMPORT: Starting PASS 2 for sheet '{$sheetName}' (Dept ID: {$dept->id})");
                $sheet = $spreadsheet->getSheetByName($sheetName);
                if (!$sheet) {
                    Log::error("DEBUG IMPORT: Sheet '{$sheetName}' not found in spreadsheet!");
                    continue;
                }

                // formatData = false (3rd param) ensure raw numeric values for quantities
                $rows = $sheet->toArray(null, true, false, false);
                Log::info("DEBUG IMPORT: Sheet '{$sheetName}' loaded with " . count($rows) . " rows.");

                // Locate header row
                $headerIdx = $this->findHeaderRow($rows);

                // HARD-CODED COLUMNS for Hospital Format:
                $nameCol = 1;
                $unitCol = 2;
                $qtyCol = 3;
                $priceCol = 4;
                $noteCol = 6;

                Log::info("Importing sheet: {$sheetName}");

                $aggregatedOrders = []; // [product_id => ['qty' => X, 'notes' => Y]]
                $currentSheetCatId = null;

                foreach ($rows as $idx => $row) {
                    if ($idx <= $headerIdx)
                        continue;

                    $name = trim((string) ($row[$nameCol] ?? ''));
                    if (empty($name) || strlen($name) < 2)
                        continue;

                    $nameLower = mb_strtolower($name);
                    if ($this->isJunkRow($name))
                        continue;

                    $unit = trim((string) ($row[$unitCol] ?? ''));
                    $qty = $this->parsePrice($row[$qtyCol] ?? '');
                    $price = $this->parsePrice($row[$priceCol] ?? '');

                    Log::info("DEBUG ROW [{$sheetName}][Row {$idx}]: RawName='{$name}', RawQty='" . ($row[$qtyCol] ?? '') . "', RawPrice='" . ($row[$priceCol] ?? '') . "', ParsedQty={$qty}, ParsedPrice={$price}");

                    // ── Supplier/Category logic in PASS 2 (Dynamic Grouping) ──
                    $stt = trim((string) ($row[0] ?? ''));
                    // Consistent with PASS 1: empty units/price/qty often indicates a header
                    $isSupplier = empty($unit) && $price == 0 && $qty == 0 && (!is_numeric($stt) || empty($stt));

                    if ($isSupplier) {
                        if (!$this->isJunkRow($name) && strlen($name) > 3) {
                            $cat = \App\Models\Category::firstOrCreate(
                                ['name' => $name],
                                ['is_active' => true]
                            );
                            $currentSheetCatId = $cat->id;
                            Log::info("Import PASS 2 [{$sheetName}]: Detected Category Header -> '{$name}' (ID: {$currentSheetCatId})");
                            continue;
                        } else {
                            continue; // skip junk/summary row
                        }
                    }

                    if ($qty <= 0)
                        continue;

                    // Match product by clean name
                    $cleanKey = $this->cleanString($this->normalizeProductName($name));
                    $product = $productMap[$cleanKey] ?? null;

                    if (!$product) {
                        // NEW REQUIREMENT: Create product if it's new from a department
                        $catId = $currentSheetCatId ?: \App\Models\Category::firstOrCreate(['name' => 'Khoa đề xuất'])->id;

                        $product = \App\Models\Product::create([
                            'name' => $this->normalizeProductName($name),
                            'unit' => $unit ?: '',
                            'category_id' => $catId,
                            'price' => $price,
                            'is_active' => true
                        ]);
                        $productMap[$cleanKey] = $product;
                        Log::warning("Import PASS 2 [{$sheetName}]: NEW PRODUCT CREATED (Not found in MASTER) -> '{$name}' (Key: {$cleanKey}) | Category ID: {$catId} | Price: {$price}");
                    } else {
                        // Existing product: Only update price if database has 0 and sheet has value
                        if ($product->price == 0 && $price > 0) {
                            $product->update(['price' => $price]);
                            Log::info("Import PASS 2 [{$sheetName}]: Updated price for '{$name}' (0 -> {$price})");
                        }
                    }

                    Log::info("Import PASS 2 [{$sheetName}]: Product '{$name}' -> Qty: {$qty}");

                    // AGGREGATE by product_id to handle duplicates in the SAME sheet
                    if (isset($aggregatedOrders[$product->id])) {
                        $aggregatedOrders[$product->id]['quantity'] += $qty;
                        if (!empty(trim((string) ($row[$noteCol] ?? '')))) {
                            $aggregatedOrders[$product->id]['notes'] .= '; ' . trim((string) ($row[$noteCol] ?? ''));
                        }
                    } else {
                        $aggregatedOrders[$product->id] = [
                            'department_id' => $dept->id,
                            'product_id' => $product->id,
                            'month' => $targetMonth,
                            'quantity' => $qty,
                            'notes' => trim((string) ($row[$noteCol] ?? '')),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                // Push aggregated items for this department
                foreach ($aggregatedOrders as $order) {
                    $ordersToInsert[] = $order;
                    $processedOrders++;
                }
            }

            // ── Batch insert orders ───────────────────────────────────────────────
            foreach (array_chunk($ordersToInsert, 500) as $chunk) {
                \App\Models\MonthlyOrder::insert($chunk);
            }

            $this->logActivity('Import Excel', "Import {$processedOrders} dòng cho {$processedDepts} khoa, tháng {$targetMonth}");
            return redirect()->route('superadmin.data-management')
                ->with('success', "Import thành công! {$processedDepts} khoa, {$processedOrders} sản phẩm.");

        } catch (\Exception $e) {
            Log::error('Advanced Import failed: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return redirect()->route('superadmin.data-management')
                ->with('error', 'Lỗi Import: ' . $e->getMessage());
        }
    }

    /**
     * PASS 1: Parse master sheet (TỔNG HỢP) for products, prices, and categories.
     * This is the SOLE source of truth for creating products and categories.
     */
    private function parseSheetForProducts($sheet, array $deptMap, array &$productMap): void
    {
        // formatData = false (3rd param) ensure raw numeric values for prices
        $rows = $sheet->toArray(null, true, false, false);

        $headerIdx = $this->findHeaderRow($rows);

        // HARD-CODED COLUMNS for Hospital Format:
        // STT (0) | Tên (1) | ĐVT (2) | SL (3) | Đơn giá (4) | Thành tiền (5) | Ghi chú (6)
        $nameCol = 1;
        $unitCol = 2;
        $qtyCol = 3;
        $priceCol = 4;
        $noteCol = 6;

        Log::info("PASS 1 Processing: " . count($rows) . " rows found.");

        $currentCatId = null;

        foreach ($rows as $idx => $row) {
            if ($idx <= $headerIdx)
                continue;


            $name = trim((string) ($row[$nameCol] ?? ''));
            if (empty($name) || strlen($name) < 2)
                continue;

            $nameLower = mb_strtolower($name);
            if ($this->isJunkRow($name))
                continue;

            $unit = trim((string) ($row[$unitCol] ?? ''));
            $qty = $this->parsePrice($row[$qtyCol] ?? '');
            $price = $this->parsePrice($row[$priceCol] ?? '');

            // ── Supplier/Category logic ──
            // Criteria: No unit AND price = 0 AND qty = 0 AND STT is not a number
            $stt = trim((string) ($row[0] ?? ''));
            // Refined check: empty units/price/qty often indicates a header
            $isSupplier = empty($unit) && $price == 0 && $qty == 0 && (!is_numeric($stt) || empty($stt));

            if ($isSupplier) {
                // DON'T treat summary rows as suppliers
                if (!$this->isJunkRow($name) && strlen($name) > 3) {
                    $cat = \App\Models\Category::updateOrCreate(
                        ['name' => $name],
                        ['is_active' => true]
                    );
                    $currentCatId = $cat->id;
                    Log::info("PASS 1 [MASTER]: Detected Supplier/Category Header -> '{$name}' (ID: {$currentCatId})");
                    continue;
                } else {
                    continue; // skip junk/summary row
                }
            }

            // ── Product logic ──
            if (!$currentCatId) {
                $def = \App\Models\Category::firstOrCreate(['name' => 'Chung']);
                $currentCatId = $def->id;
            }

            $cleanName = $this->normalizeProductName($name);
            $cleanKey = $this->cleanString($cleanName);

            $product = \App\Models\Product::updateOrCreate(
                ['name' => $cleanName],
                [
                    'unit' => $unit ?: '',
                    'category_id' => $currentCatId,
                    'price' => $price,
                    'is_active' => true
                ]
            );
            $productMap[$cleanKey] = $product;

            Log::info("PASS 1: Product '{$cleanName}' | ParsedPrice: {$price} | Cat: {$currentCatId}");
        }
    }

    /**
     * Find the header row index (0-based) in the given rows array.
     * Looks for a row containing both "tên" and ("đvt" or "đơn vị").
     */
    private function findHeaderRow(array $rows): int
    {
        for ($r = 0; $r < min(30, count($rows)); $r++) {
            $rowStr = mb_strtolower(implode(' ', array_filter($rows[$r] ?? [])));
            if (
                str_contains($rowStr, 'tên') &&
                (str_contains($rowStr, 'đvt') || str_contains($rowStr, 'đơn vị'))
            ) {
                return $r;
            }
        }
        return 0; // fallback: assume row 0 is header
    }



    /**
     * Import data from Excel (Simple)
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

            // CHECK IF USER UPLOADED THE COMPLEX "TỔNG HỢP" FILE BY MISTAKE
            if ($spreadsheet->getSheetByName('TỔNG HỢP')) {
                return redirect()->route('superadmin.data-management')->with('error', 'Lỗi: Bạn đang dùng chức năng "Import Đơn giản" cho file "Tổng Hợp" (nhiều sheet). Vui lòng kéo xuống dưới và dùng mục "Import File Tổng Hợp (Nhiều Sheet)" để hệ thống xử lý chính xác!');
            }

            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Remove header row
            array_shift($rows);

            $imported = 0;

            switch ($importType) {
                case 'products':
                    foreach ($rows as $row) {
                        // Detect STT shift (if row[0] is numeric and small, it's likely STT)
                        $nameIdx = 0;
                        $unitIdx = 1;
                        $priceIdx = 2;
                        $catIdx = 3;

                        if (is_numeric($row[0]) && count($row) > 3) {
                            $nameIdx = 1;
                            $unitIdx = 2;
                            $priceIdx = 3;
                            $catIdx = 4;
                        }

                        $name = trim($row[$nameIdx] ?? '');
                        if (!empty($name) && strlen($name) > 2) {
                            $price = (float) str_replace([',', '.'], '', $row[$priceIdx] ?? '0');
                            \App\Models\Product::updateOrCreate(
                                ['name' => $name],
                                [
                                    'unit' => $row[$unitIdx] ?? '',
                                    'price' => $price,
                                    'category_id' => $row[$catIdx] ?? null,
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

            $this->logActivity('Import Dữ liệu', "Đã import {$imported} bản ghi loại {$importType}");
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
    // --- HELPERS ---

    private function isDeptName($name, $deptMap)
    {
        if (empty($name))
            return false;

        // 1. Strip leading numbering like "1. ", "II. ", "A. "
        $clean = preg_replace('/^([0-9]+|[IVXLC]+|[A-Z])[\.\ \-\:\)]\s*/i', '', trim($name));

        // 2. Match against known departments
        $norm = strtoupper(\Illuminate\Support\Str::slug($clean));
        if (isset($deptMap[$norm]))
            return true;

        // 3. Only match dept keywords at the START of the name
        //    ("PHÒNG MỔ" starts with "phòng" → dept)
        //    ("VĂN PHÒNG PHẨM" also starts with "văn" not "phòng" → NOT dept)
        $lower = mb_strtolower($clean);
        $deptKeywordsAtStart = ['khoa ', 'phòng ', 'ban ', 'phòng khám', 'nhà thuốc', 'trung tâm', 'bộ phận'];
        foreach ($deptKeywordsAtStart as $kw) {
            if (str_starts_with($lower, $kw))
                return true;
        }

        return false;
    }

    private function parsePrice($val)
    {
        if ($val === null || $val === '')
            return 0;

        // If it's already a number (from toArray with formatData=false)
        if (is_int($val) || is_float($val))
            return (float) $val;

        // Clean currency symbols, spaces, and potential invisible artifacts
        $original = (string) $val;
        $val = str_replace(['đ', 'VND', ' ', "\xc2\xa0", "\xa0"], '', trim($original));

        // Logic for Vietnamese format: thousands are dots, decimals are commas
        // "50.000" -> 50000
        // "1.234.567,89" -> 1234567.89
        $val = str_replace(['đ', 'VND', ' ', "\xc2\xa0", "\xa0"], '', $val);

        if (str_contains($val, ',') && str_contains($val, '.')) {
            $val = str_replace('.', '', $val); // Remove thousands separator
            $val = str_replace(',', '.', $val); // Replace decimal comma with dot
        } elseif (str_contains($val, '.')) {
            // "50.000" -> remove dot if it's a thousand separator
            // If it has 3 digits after the dot, it's definitely a thousand separator.
            if (preg_match('/\.\d{3}$/', $val)) {
                $val = str_replace('.', '', $val);
            }
        } elseif (str_contains($val, ',')) {
            // "50,000" (comma thousand) or "50,5" (decimal comma)
            // If it has 3 digits after the comma, it's likely a thousand separator.
            if (preg_match('/,\d{3}($|[^0-9])/', $val)) {
                $val = str_replace(',', '', $val);
            } else {
                $val = str_replace(',', '.', $val);
            }
        }

        $result = (float) (is_numeric($val) ? $val : 0);

        // Safety: If numeric string '50.000' was incorrectly seen by PHP as 50.0
        // and we know procurement prices for things like "Bìa còng" are > 1000
        if ($result > 0 && $result < 100 && str_contains($original, '.')) {
            // Potential 1000x error
            if (preg_match('/\.\d{3}/', $original))
                $result *= 1000;
        }

        if ($result > 0) {
            \Log::debug("DEBUG PARSE: '{$original}' -> '{$val}' -> {$result}");
        }

        return $result;
    }

    private function isJunkRow($name)
    {
        if (empty($name))
            return true;

        $h = mb_strtolower(trim($name));

        // Skip summary keywords (Crucial for fixing quantity sums)
        if ($h === 'cộng' || $h === 'tổng' || $h === 'tổng cộng')
            return true;
        if (str_contains($h, 'tổng nhóm') || str_contains($h, 'cộng nhóm'))
            return true;
        if (str_contains($h, 'tổng cộng phiếu'))
            return true;

        // Check if it's just a common Unit Name (e.g. "Cái", "Hộp")
        if ($this->isCommonUnit($name))
            return true;

        // Document headers
        if (str_starts_with($h, 'ngày') || str_contains($h, 'mẫu số'))
            return true;
        if (str_contains($h, 'bệnh viện đa khoa') || str_contains($h, 'hỗ trợ dịch vụ'))
            return true;
        if (str_contains($h, 'phiếu xuất') || str_contains($h, 'biên bản'))
            return true;
        if (str_contains($h, 'bảng kê') || str_contains($h, 'chủ tài khoản'))
            return true;
        if (str_contains($h, 'ghi chú'))
            return true;
        if (str_contains($h, 'người lập') || str_contains($h, 'trưởng phòng') || str_contains($h, 'ban giám đốc'))
            return true;

        // Technical headers
        return preg_match('/^sheet\d+$/i', $h) || preg_match('/^column\d+$/i', $h) || str_contains($h, 'sheet');
    }

    private function isCommonUnit($name)
    {
        $units = [
            'cái',
            'chiếc',
            'hộp',
            'chai',
            'lọ',
            'kg',
            'gam',
            'gram',
            'mét',
            'cây',
            'cuộn',
            'xấp',
            'ram',
            'ream',
            'bộ',
            'đôi',
            'viên',
            'liều',
            'ống',
            'túi',
            'gói',
            'thùng',
            'bịch',
            'can',
            'kít',
            'hũ',
            'quyển',
            'tờ',
            'cục',
            'lốc'
        ];
        return in_array(mb_strtolower(trim($name)), $units);
    }

    private function isJunkDept($name)
    {
        return $this->isJunkRow($name);
    }

    /**
     * Scan first 10 rows to detect columns based on Header AND Content
     */
    private function detectColumnsStrict($rows, $headerRowIndex)
    {
        $nameCol = -1;
        $unitCol = -1;
        $priceCol = -1;
        $qtyCol = -1; // Only for Step 2
        $noteCol = -1;

        $headerRow = $rows[$headerRowIndex] ?? [];

        // 1. Initial Guess by Header
        foreach ($headerRow as $cIdx => $val) {
            $vLower = mb_strtolower(trim($val ?? ''));

            // Name
            if (str_contains($vLower, 'tên hàng') || str_contains($vLower, 'tên sản phẩm') || str_contains($vLower, 'tên quy cách') || str_contains($vLower, 'danh mục'))
                $nameCol = $cIdx;
            elseif (str_contains($vLower, 'tên') && !str_contains($vLower, 'đơn vị') && !str_contains($vLower, 'khoa'))
                if ($nameCol == -1)
                    $nameCol = $cIdx; // weak match

            // Unit
            if (str_contains($vLower, 'đvt') || str_contains($vLower, 'đơn vị'))
                $unitCol = $cIdx;

            // Price
            if (str_contains($vLower, 'đơn giá') || $vLower == 'giá')
                $priceCol = $cIdx;

            // Quantity (Step 2)
            if (str_contains($vLower, 'số lượng') || $vLower == 'sl')
                $qtyCol = $cIdx;

            // Note
            if (str_contains($vLower, 'ghi chú'))
                $noteCol = $cIdx;
        }

        // 2. Validate Content (Scan up to 5 rows)
        // If Name Col contains Units -> It's wrong!
        if ($nameCol != -1) {
            $isInvalidNameCol = false;
            $unitCount = 0;
            $checkCount = 0;
            for ($i = 1; $i <= 5; $i++) {
                $checkRow = $rows[$headerRowIndex + $i] ?? null;
                if (!$checkRow)
                    break;
                $val = trim($checkRow[$nameCol] ?? '');
                if (empty($val))
                    continue;

                $checkCount++;
                if ($this->isCommonUnit($val) || is_numeric($val))
                    $unitCount++;
            }
            if ($checkCount > 0 && ($unitCount / $checkCount) > 0.5) {
                // > 50% of "Name" column values are actually Units -> Invalid!
                $nameCol = -1;
            }
        }

        // 3. Fallback Search if Columns Missing
        if ($nameCol == -1 || $unitCol == -1 || $priceCol == -1) {
            // Scan 10 columns
            for ($c = 1; $c < 10; $c++) {
                $potentialName = 0;
                $potentialUnit = 0;
                $potentialPrice = 0;

                for ($i = 1; $i <= 5; $i++) {
                    $checkRow = $rows[$headerRowIndex + $i] ?? null;
                    if (!$checkRow)
                        break;
                    $val = trim($checkRow[$c] ?? '');
                    if (empty($val))
                        continue;

                    if ($this->isCommonUnit($val))
                        $potentialUnit++;
                    elseif (is_numeric(str_replace([',', '.'], '', $val)) && strlen($val) > 3)
                        $potentialPrice++;
                    elseif (strlen($val) > 3 && !is_numeric($val))
                        $potentialName++;
                }

                if ($nameCol == -1 && $potentialName >= 3)
                    $nameCol = $c;
                if ($unitCol == -1 && $potentialUnit >= 3)
                    $unitCol = $c;
                if ($priceCol == -1 && $potentialPrice >= 3)
                    $priceCol = $c;
            }
        }

        // Final Safety
        if ($nameCol == $unitCol)
            $unitCol = -1; // Can't be same

        return [$nameCol, $unitCol, $priceCol, $qtyCol, $noteCol];
    }

    private function findOrCreateDept($name, $slug, &$deptMap)
    {
        if ($name == 'CĐHA' && $slug == 'CDHA')
            $slug = 'CDHA';

        $dept = \App\Models\Department::where('code', $slug)->orWhere('name', $name)->first();
        if (!$dept) {
            try {
                $dept = \App\Models\Department::create(['code' => $slug, 'name' => $name]);
                $this->ensureDepartmentUser($dept);
            } catch (\Exception $e) {
                return \App\Models\Department::where('code', $slug)->first() ?? \App\Models\Department::where('name', $name)->first();
            }
        }
        $deptMap[$slug] = $dept;
        return $dept;
    }

    private function normalizeProductName($name)
    {
        if (empty($name))
            return '';
        // Strip leading numbering/special chars: "1. Product", "1/ Product", "11 Bọc trắng"
        $name = preg_replace('/^\d+[\.\/\-\s]*/', '', trim($name));
        return trim($name);
    }

    private function cleanString($str)
    {
        if (empty($str))
            return '';
        $str = $this->normalizeProductName($str);

        $unicode = array(
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D' => 'Đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );

        foreach ($unicode as $nonUnicode => $uni) {
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }
        $str = mb_strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9]/', '', $str); // Strict alphanumeric for matching
        return $str;
    }

    private function cleanStringForMatch($str)
    {
        if (empty($str))
            return '';

        // Normalize Unicode (NFC)
        if (class_exists('Normalizer')) {
            $str = \Normalizer::normalize($str, \Normalizer::FORM_C);
        }

        $str = $this->cleanString($str); // Uses existing alphanumeric cleaner
        return strtoupper($str);
    }
}
