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
     * Remove accents and spaces from string
     */
    private function cleanString($str)
    {
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
        $str = str_replace(' ', '', $str); // Remove spaces
        return $str;
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
     * Import Advanced (Multi-sheet)
     */
    public function importAdvanced(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
            'month' => 'required|date_format:Y-m',
        ]);

        try {
            set_time_limit(0);
            ini_set('memory_limit', '1024M'); // Increase memory for large batch processing

            $file = $request->file('excel_file');
            $monthInput = $request->input('month');
            $monthParts = explode('-', $monthInput);
            $targetMonth = "{$monthParts[1]}/{$monthParts[0]}";

            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getPathname());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getPathname());

            // --- PRE-LOAD DATA INTO HASH MAPS (Optimization) ---
            // Departments: [normalized_key => dept_object]
            $existingDepts = \App\Models\Department::all();
            $deptMap = [];
            foreach ($existingDepts as $d) {
                // Map by Code slug
                $deptMap[strtoupper(\Illuminate\Support\Str::slug($d->code))] = $d;
                // Map by Name slug (as backup)
                $deptMap[strtoupper(\Illuminate\Support\Str::slug($d->name))] = $d;
            }

            // Products: [normalized_name => product_object] - LOADING FULL OBJECTS TO AVOID "Find()"
            $existingProducts = \App\Models\Product::all();
            $productMap = [];
            foreach ($existingProducts as $p) {
                $uniqueKey = $this->cleanString($p->name);
                $productMap[$uniqueKey] = $p;
            }

            // Categories: [normalized_name => id]
            $existingCats = \App\Models\Category::all();
            $catMap = [];
            foreach ($existingCats as $c) {
                $catMap[$this->cleanString($c->name)] = $c->id;
            }

            $ordersToInsert = [];
            $processedDepartments = 0;
            $processedOrders = 0;
            $pivotMode = false;
            $pivotDeptIds = []; // [colIndex => departmentId]

            // --- STEP 1: PARSE MASTER CATALOG FROM "TỔNG HỢP" (Catalog Only) ---
            $masterSheet = $spreadsheet->getSheetByName('TỔNG HỢP') ?? $spreadsheet->getSheetByName('Tong hop');
            if ($masterSheet) {
                $rows = $masterSheet->toArray();
                $currentCategoryId = null;

                // 1.1: PRIME CATALOG (Products, Categories, Prices)
                // Search for the header row containing "Tên" and "ĐVT"
                $masterHeaderIdx = -1;
                foreach ($rows as $idx => $row) {
                    $rStr = mb_strtolower(implode(' ', array_filter($row)));
                    if (str_contains($rStr, 'tên') && (str_contains($rStr, 'đvt') || str_contains($rStr, 'đơn vị'))) {
                        $masterHeaderIdx = $idx;
                        break;
                    }
                }

                if ($masterHeaderIdx != -1) {
                    $masterHeaderRow = $rows[$masterHeaderIdx];
                    list($nameCol, $unitCol, $priceCol, $qtyCol, $noteCol) = $this->detectColumnsStrict($rows, $masterHeaderIdx);

                    // Fallback Defaults
                    if ($nameCol == -1)
                        $nameCol = 1;
                    if ($unitCol == -1)
                        $unitCol = 2;
                    if ($priceCol == -1)
                        $priceCol = 4;

                    // FALLBACK PRICE COLUMN DETECTION: Scan first 5 rows
                    if ($priceCol == -1 || $priceCol == 4) { // Re-check if strict failed or defaulted
                        for ($scanRow = $masterHeaderIdx + 1; $scanRow < min($masterHeaderIdx + 6, count($rows)); $scanRow++) {
                            // Check potential columns (typically 3, 4, 5, 6)
                            for ($pCol = 3; $pCol <= 10; $pCol++) {
                                $val = $this->parsePrice($rows[$scanRow][$pCol] ?? '');
                                if ($val > 1000) { // Prices usually > 1000 VND
                                    $priceCol = $pCol;
                                    break 2;
                                }
                            }
                        }
                    }

                    Log::info("Import Debug: Master Header Found at Row $masterHeaderIdx");
                    Log::info("Import Debug: Columns Detected - Name: $nameCol, Unit: $unitCol, Price: $priceCol");

                    for ($i = $masterHeaderIdx + 1; $i < count($rows); $i++) {
                        $row = $rows[$i];
                        $name = trim($row[$nameCol] ?? '');
                        $unit = trim($row[$unitCol] ?? '');
                        $priceStr = trim($row[$priceCol] ?? '');

                        if ($i < $masterHeaderIdx + 6) {
                            Log::info("Import Debug Row $i: Name='$name', PriceStr='$priceStr', ParsedPrice=" . $this->parsePrice($priceStr));
                        }

                        if (empty($name) || strlen($name) < 2 || $this->isJunkRow($name))
                            continue;
                        if (str_contains($name, 'CỘNG') || str_contains($name, 'Tổng'))
                            continue;

                        // CATEGORY DETECTION (Row with name but no unit/price)
                        // Note: Some catalogs have categories with just a name row.
                        if (empty($unit) && (empty($priceStr) || $priceStr == '0')) {
                            // We treat ALL group headers in Master Sheet as Categories (Suppliers, Types, etc.)
                            // Removed isDeptName check because it was filtering out Suppliers (Cửa hàng, Công ty...)

                            $normCatName = $this->cleanString($name);
                            $cat = \App\Models\Category::updateOrCreate(['name' => $name], ['is_active' => true]);
                            $catMap[$normCatName] = $cat->id;
                            $currentCategoryId = $cat->id;
                            continue;
                        }

                        // PRODUCT UPDATE/CREATE (Master Source)
                        $price = $this->parsePrice($priceStr);
                        $normName = $this->cleanString($name);

                        // If no category yet, create a default one
                        if (!$currentCategoryId) {
                            $defCat = \App\Models\Category::firstOrCreate(['name' => 'Chung']);
                            $currentCategoryId = $defCat->id;
                        }

                        $updateData = [
                            'unit' => $unit,
                            'category_id' => $currentCategoryId,
                            'is_active' => true
                        ];
                        // Only update price if we actually have a non-zero price in Master
                        if ($price > 0) {
                            $updateData['price'] = $price;
                        }

                        $product = \App\Models\Product::updateOrCreate(
                            ['name' => $name],
                            $updateData
                        );
                        $productMap[$normName] = $product;
                    }
                }
                // PIVOT MODE REMOVED per user request.
                // We will now strictly use individual sheets for orders.
            }

            // --- STEP 2: MULTI-SHEET PROCESSING (Orders Source) ---
            $sheetNames = $spreadsheet->getSheetNames();
            $ignoredSheets = ['BẢNG TỔNG', 'TỔNG HỢP', 'Highlights', 'Sheet1', 'Tong hop', 'Ghi chu'];

            foreach ($sheetNames as $sheetName) {
                if (in_array($sheetName, $ignoredSheets))
                    continue;

                // --- FILTER JUNK SHEETS (Sheet2, Sheet3, etc.) ---
                if (preg_match('/^Sheet\d+$/i', $sheetName) || preg_match('/^Column\d+$/i', $sheetName))
                    continue;

                // Use Sheet Name AS Dept Name
                $deptName = $sheetName;
                $deptSlug = strtoupper(\Illuminate\Support\Str::slug($deptName));

                // Normalize specific common abbreviations
                if ($deptName == 'CĐHA' && $deptSlug == 'CDHA')
                    $deptSlug = 'CDHA';

                if (isset($deptMap[$deptSlug])) {
                    $dept = $deptMap[$deptSlug];
                } else {
                    // Create new Department from Sheet Name
                    // Check by Code OR Name to prevent "Duplicate entry" error
                    $dept = \App\Models\Department::where('code', $deptSlug)
                        ->orWhere('name', $deptName)
                        ->first();

                    if (!$dept) {
                        try {
                            $dept = \App\Models\Department::create(['code' => $deptSlug, 'name' => $deptName]);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Duplicate handling
                            $dept = \App\Models\Department::where('code', $deptSlug)->first();
                        }
                    }
                    $this->ensureDepartmentUser($dept);
                    $deptMap[$deptSlug] = $dept;
                }

                // Reset data for this dept for this month
                \App\Models\MonthlyOrder::where('department_id', $dept->id)
                    ->where('month', $targetMonth)
                    ->delete();

                $processedDepartments++;

                $sheet = $spreadsheet->getSheetByName($sheetName);
                $rows = $sheet->toArray();

                // DYNAMIC HEADER PARSING (Step 2)
                $headerRowIndex = 4; // Default
                // Scan first 20 rows for Header
                for ($r = 0; $r < 20; $r++) {
                    $rowStr = mb_strtolower(implode(' ', array_map(fn($x) => (string) $x, $rows[$r] ?? [])));
                    if (str_contains($rowStr, 'tên hàng') || str_contains($rowStr, 'tên quy cách') || str_contains($rowStr, 'tên')) {
                        $headerRowIndex = $r;
                        break;
                    }
                }

                list($nameCol, $unitCol, $priceCol, $qtyCol, $noteCol) = $this->detectColumnsStrict($rows, $headerRowIndex);

                // Fallbacks if detection failed
                if ($nameCol == -1)
                    $nameCol = 1;
                if ($unitCol == -1)
                    $unitCol = 2;
                if ($qtyCol == -1)
                    $qtyCol = 3; // Start assuming col 3
                if ($priceCol == -1)
                    $priceCol = 4;

                // Refined Quantity Column Search if default failed or seems wrong
                if ($rows[$headerRowIndex][$qtyCol] ?? '' == '') {
                    // Try to find a column with "Số lượng" explicitly
                    foreach ($rows[$headerRowIndex] as $ix => $val) {
                        if (str_contains(mb_strtolower($val), 'số lượng')) {
                            $qtyCol = $ix;
                            break;
                        }
                    }
                }

                // Buffer for summation: [prod_id => ['quantity' => float, 'notes' => string]]
                $deptOrdersBuffer = [];

                foreach ($rows as $index => $row) {
                    if ($index <= $headerRowIndex)
                        continue;

                    // Indexes
                    $name = trim($row[$nameCol] ?? '');
                    $qtyStr = trim($row[$qtyCol] ?? '');
                    // We don't really trust Price/Unit in these sheets as much as Master, but we use them to find product

                    if (empty($name))
                        continue;

                    // SKIP JUNK
                    if (str_contains($name, 'CỘNG') || str_contains($name, 'Tổng') || $this->isJunkRow($name))
                        continue;
                    if ($this->isDeptName($name, $deptMap))
                        continue; // Skip header section in middle of sheet

                    // --- QUANTITY CHECK ---
                    $qty = $this->parsePrice($qtyStr);
                    if ($qty <= 0)
                        continue;

                    // Find Product
                    $normName = $this->cleanString($name);
                    $product = $productMap[$normName] ?? null;

                    // If missing in Master, we can optionally create it, 
                    // but it's better to rely on Master. User said "chính xác tất cả như excel".
                    // So if it's in the Excel sheet and has quantity, we SHOULD import it.
                    if (!$product) {
                        // Infer details from this row
                        $unit = trim($row[$unitCol] ?? '');
                        $price = $this->parsePrice(trim($row[$priceCol] ?? '0'));
                        // Try to match a default category or fallback
                        $defCat = \App\Models\Category::firstOrCreate(['name' => 'Khác']);

                        $product = \App\Models\Product::create([
                            'name' => $name,
                            'unit' => $unit,
                            'price' => $price,
                            'category_id' => $defCat->id,
                            'is_active' => true
                        ]);
                        $productMap[$normName] = $product;
                    }

                    // BUFFER QUANTITY
                    $productId = $product->id;
                    $note = trim($row[$noteCol] ?? '');

                    if (isset($deptOrdersBuffer[$productId])) {
                        $deptOrdersBuffer[$productId]['quantity'] += $qty;
                        if (!empty($note)) {
                            $deptOrdersBuffer[$productId]['notes'] .= '; ' . $note;
                        }
                    } else {
                        $deptOrdersBuffer[$productId] = [
                            'quantity' => $qty,
                            'notes' => $note
                        ];
                    }
                }

                // Convert Buffer to Insert Array
                foreach ($deptOrdersBuffer as $pId => $data) {
                    $ordersToInsert[] = [
                        'department_id' => $dept->id,
                        'product_id' => $pId,
                        'month' => $targetMonth,
                        'quantity' => $data['quantity'],
                        'notes' => trim($data['notes'], '; '),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $processedOrders++;
                }
            }

            // --- STEP 3: BATCH INSERT ---
            if (count($ordersToInsert) > 0) {
                // Chunk to verify memory limits
                $chunks = array_chunk($ordersToInsert, 1000);
                foreach ($chunks as $chunk) {
                    \App\Models\MonthlyOrder::insert($chunk);
                }
            }

            $mode = 'Đa Sheet (Chính xác)';
            $this->logActivity('Import HighPerf', "Import {$processedOrders} dòng cho {$processedDepartments} khoa. Mode: {$mode}");

            return redirect()->route('superadmin.data-management')
                ->with('success', "Đã import xong! ({$mode}): {$processedDepartments} khoa, {$processedOrders} sản phẩm.");

        } catch (\Exception $e) {
            Log::error('Advanced Import failed: ' . $e->getMessage());
            return redirect()->route('superadmin.data-management')->with('error', 'Lỗi Import: ' . $e->getMessage());
        }
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
        $clean = preg_replace('/^([0-9]+|[IVXLC]+|[A-Z])[\.\-\:\)]\s*/i', '', trim($name));

        // 2. Normalize
        $norm = strtoupper(\Illuminate\Support\Str::slug($clean));
        if (isset($deptMap[$norm]))
            return true;

        // 3. Check keywords
        $lower = mb_strtolower($clean);
        // 3. Check keywords
        $lower = mb_strtolower($clean);
        $keywords = [
            'khoa',
            'phòng',
            'ban',
            'đơn vị',
            'phòng khám',
            'nhà thuốc',
            'chẩn đoán',
            'xét nghiệm',
            'trung tâm',
            'bộ phận',
            'tổ',
            'đội'
        ];

        foreach ($keywords as $kw) {
            // Check if starts with OR contains (for things like "II. CHẨN ĐOÁN HÌNH ẢNH")
            if (str_contains($lower, $kw))
                return true;
        }

        return false;
    }

    private function parsePrice($val)
    {
        if (is_numeric($val))
            return (float) $val;
        if (empty($val))
            return 0;

        // Clean currency symbols, spaces, and potential invisible artifacts
        $val = str_replace(['đ', 'VND', ' ', "\xc2\xa0", "\xa0"], '', trim($val));

        // Remove thousand separators if they are dots (VN standard) and commas are decimals
        // or if they are just dots/commas consistently

        // Example: 12,000 or 12.000 or 1.234,56
        if (str_contains($val, ',') && str_contains($val, '.')) {
            // Mixed: assume dot = thousand, comma = decimal
            $val = str_replace('.', '', $val);
            $val = str_replace(',', '.', $val);
        } elseif (str_contains($val, ',')) {
            // Only comma: Check if it's likely a decimal (2-3 digits after) or thousand
            if (preg_match('/,\d{2,3}$/', $val)) {
                // If the number is small (e.g. 12,00) or looks like decimal, convert to dot
                $val = str_replace(',', '.', $val);
            } else {
                // Otherwise thousand
                $val = str_replace(',', '', $val);
            }
        } elseif (str_contains($val, '.')) {
            // Only dot: VN standard for 12.000 is thousand
            // Unless it looks like 12.0 (but prices usually don't have that in these files)
            if (preg_match('/\.\d{3}$/', $val) || preg_match('/\.\d{3}\./', $val)) {
                $val = str_replace('.', '', $val);
            }
            // However, for single dots, it's ambiguous. But most hospital prices are > 1000.
            // If we remove dot and it becomes a huge number that makes sense for VND, fine.
            // Let's assume dot is thousand if it's followed by 3 digits.
        }

        // Final fallback: remove anything not numeric or dot
        $val = preg_replace('/[^0-9\.]/', '', $val);

        return (float) $val;
    }

    private function isJunkRow($name)
    {
        if (empty($name))
            return true;

        // Check if it's just a common Unit Name (e.g. "Cái", "Hộp")
        if ($this->isCommonUnit($name))
            return true;

        $h = mb_strtolower(trim($name));

        // Document headers
        if (str_starts_with($h, 'ngày') || str_contains($h, 'mẫu số'))
            return true;
        // removing 'cty cp' generally, only block specific hospital header
        if (str_contains($h, 'bệnh viện đa khoa') || str_contains($h, 'hỗ trợ dịch vụ'))
            return true;
        if (str_contains($h, 'phiếu xuất') || str_contains($h, 'biên bản'))
            return true;
        if (str_contains($h, 'bảng kê') || str_contains($h, 'chủ tài khoản'))
            return true;
        if (str_contains($h, 'tổng cộng phiếu') || str_contains($h, 'ghi chú'))
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

    private function bufferOrder(&$orders, $deptId, $prodId, $month, $qty)
    {
        $key = "{$deptId}_{$prodId}";
        if (isset($orders[$key])) {
            $orders[$key]['quantity'] += $qty;
        } else {
            $orders[$key] = [
                'department_id' => $deptId,
                'product_id' => $prodId,
                'month' => $month,
                'quantity' => $qty,
                'notes' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }
}
