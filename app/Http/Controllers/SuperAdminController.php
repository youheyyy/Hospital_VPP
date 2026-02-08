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
            session()->flash('success', 'Hệ thống đã tự động cập nhật dữ liệu mới nhất (Realtime)');

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
            // Departments: [normalized_code => dept_object]
            $existingDepts = \App\Models\Department::all();
            $deptMap = [];
            foreach ($existingDepts as $d) {
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

            // --- STEP 1: DETECT & PARSE "TỔNG HỢP" SHEET (Pivot Mode) ---
            $masterSheet = $spreadsheet->getSheetByName('TỔNG HỢP') ?? $spreadsheet->getSheetByName('Tong hop');

            if ($masterSheet) {
                $rows = $masterSheet->toArray();
                $currentCategoryId = null;

                // DETECT PIVOT HEADERS (Scan rows 3-5 for Department Names)
                $headerRowIndex = -1;
                for ($r = 2; $r <= 5; $r++) {
                    if (!isset($rows[$r]))
                        continue;
                    $row = $rows[$r];
                    // Check if > 3 columns look like Departments
                    $deptCount = 0;
                    for ($c = 3; $c < count($row); $c++) {
                        $cell = trim($row[$c] ?? '');
                        if (strlen($cell) > 2 && !str_contains($cell, 'Tổng') && !is_numeric($cell)) {
                            $deptCount++;
                        }
                    }
                    if ($deptCount > 2) {
                        $headerRowIndex = $r;
                        $pivotMode = true;
                        break;
                    }
                }

                if ($pivotMode) {
                    // Process Headers to create/map Departments
                    $headerRow = $rows[$headerRowIndex];
                    for ($col = 3; $col < count($headerRow); $col++) {
                        $deptName = trim($headerRow[$col] ?? '');
                        if (empty($deptName) || str_contains($deptName, 'Tổng') || str_contains($deptName, 'Ghi chú'))
                            continue;

                        $deptSlug = strtoupper(\Illuminate\Support\Str::slug($deptName));

                        if (isset($deptMap[$deptSlug])) {
                            $dept = $deptMap[$deptSlug];
                        } else {
                            $dept = \App\Models\Department::create(['code' => $deptSlug, 'name' => $deptName]);
                            $this->ensureDepartmentUser($dept);
                            $deptMap[$deptSlug] = $dept; // Update map
                        }

                        $pivotDeptIds[$col] = $dept->id;

                        // Clear old data for matched departments
                        \App\Models\MonthlyOrder::where('department_id', $dept->id)
                            ->where('month', $targetMonth)
                            ->delete();
                    }
                    $processedDepartments = count($pivotDeptIds);

                    // Process Data Rows
                    for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                        $row = $rows[$i];
                        $colB = trim($row[1] ?? ''); // Name (often in B for Single)
                        $colC = trim($row[2] ?? ''); // Name (often in C for Pivot) or Unit

                        // Heuristic: If pivot mode, Name is usually C, Unit D. 
                        // But sometimes B is name if A is empty.
                        $name = !empty($colC) ? $colC : $colB;
                        $unit = trim($row[3] ?? '');

                        if (empty($name) || strlen($name) < 2)
                            continue;

                        // Filter junk
                        if (str_contains($name, 'CỘNG HÒA') || str_contains($name, 'BẢNG') || str_contains($name, 'Tên hàng'))
                            continue;

                        // Identify Product
                        $normName = $this->cleanString($name);
                        $product = $productMap[$normName] ?? null;
                        $productId = $product ? $product->id : null;

                        // Create if new
                        if (!$product) {
                            // Detect Category (No Unit, Name only)
                            if (empty($unit)) {
                                // Category Logic
                                $normCatName = $this->cleanString($name);
                                if (isset($catMap[$normCatName])) {
                                    $currentCategoryId = $catMap[$normCatName];
                                } else {
                                    $cat = \App\Models\Category::create(['name' => $name, 'is_active' => true]);
                                    $catMap[$normCatName] = $cat->id;
                                    $currentCategoryId = $cat->id;
                                }
                                continue;
                            }
                            // Create Product
                            else {
                                if (!$currentCategoryId) {
                                    $defaultCatName = $this->cleanString('Văn phòng phẩm');
                                    $currentCategoryId = $catMap[$defaultCatName] ?? \App\Models\Category::firstOrCreate(['name' => 'Văn phòng phẩm'])->id;
                                    $catMap[$defaultCatName] = $currentCategoryId;
                                }

                                $product = \App\Models\Product::create([
                                    'name' => $name,
                                    'unit' => $unit,
                                    'category_id' => $currentCategoryId,
                                    'is_active' => true
                                ]);
                                $productMap[$normName] = $product;
                                $productId = $product->id;
                            }
                        }

                        // Add Orders for this row
                        if ($productId) {
                            foreach ($pivotDeptIds as $colIndex => $deptId) {
                                $qty = trim($row[$colIndex] ?? '');
                                $qty = (float) str_replace([',', '.'], '', $qty);
                                if ($qty > 0) {
                                    $ordersToInsert[] = [
                                        'department_id' => $deptId,
                                        'product_id' => $productId,
                                        'month' => $targetMonth,
                                        'quantity' => $qty,
                                        'notes' => '',
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ];
                                    $processedOrders++;
                                }
                            }
                        }
                    }
                }
            }

            // --- STEP 2: FALLBACK TO MULTI-SHEET (If no Pivot Mode detected) ---
            if (!$pivotMode) {
                $sheetNames = $spreadsheet->getSheetNames();
                $ignoredSheets = ['BẢNG TỔNG', 'TỔNG HỢP', 'Highlights', 'Sheet1', 'Tong hop', 'Ghi chu'];

                foreach ($sheetNames as $sheetName) {
                    if (in_array($sheetName, $ignoredSheets))
                        continue;

                    // Parse Department from Sheet Name
                    $deptSlug = strtoupper(\Illuminate\Support\Str::slug($sheetName));
                    if (isset($deptMap[$deptSlug])) {
                        $dept = $deptMap[$deptSlug];
                    } else {
                        $dept = \App\Models\Department::create(['code' => $deptSlug, 'name' => $sheetName]);
                        $this->ensureDepartmentUser($dept);
                        $deptMap[$deptSlug] = $dept;
                    }

                    // Reset data for this dept
                    \App\Models\MonthlyOrder::where('department_id', $dept->id)
                        ->where('month', $targetMonth)
                        ->delete();

                    $processedDepartments++;

                    $sheet = $spreadsheet->getSheetByName($sheetName);
                    $rows = $sheet->toArray();

                    // Buffer for summation: [prod_id => ['quantity' => float, 'notes' => string]]
                    $deptOrdersBuffer = [];
                    // Default fallback category if sheet starts without one
                    $defaultCatName = $this->cleanString('Văn phòng phẩm');
                    $currentCategoryId = $catMap[$defaultCatName] ?? \App\Models\Category::firstOrCreate(['name' => 'Văn phòng phẩm'])->id;
                    $catMap[$defaultCatName] = $currentCategoryId;

                    foreach ($rows as $index => $row) {
                        if ($index < 5)
                            continue; // Skip header mostly

                        // Indexes:
                        // 0: STT, 1: Name (B), 2: Unit (C), 3: Qty (D), 4: Price (E), 6: Note (G)
                        $name = trim($row[1] ?? ''); // usually Col B
                        $unit = trim($row[2] ?? '');
                        $qtyStr = trim($row[3] ?? '');
                        $priceStr = trim($row[4] ?? '');
                        $note = trim($row[6] ?? '');

                        if (empty($name))
                            continue;

                        // --- CATEGORY DETECTION ---
                        if (empty($unit) && empty($qtyStr) && empty($priceStr)) {
                            if (str_contains($name, 'CỘNG') || str_contains($name, 'Tổng'))
                                continue;
                            $normCatName = $this->cleanString($name);
                            if (isset($catMap[$normCatName])) {
                                $currentCategoryId = $catMap[$normCatName];
                            } else {
                                $cat = \App\Models\Category::create(['name' => $name, 'is_active' => true]);
                                $catMap[$normCatName] = $cat->id;
                                $currentCategoryId = $cat->id;
                            }
                            continue;
                        }

                        // --- PRODUCT PROCESSING ---
                        $qty = (float) str_replace([',', '.'], '', $qtyStr);
                        if ($qty <= 0)
                            continue;
                        if (str_contains($name, 'Tên hàng') || str_contains($name, 'ĐVT') || str_contains($name, 'Cộng') || str_contains($name, 'Tổng'))
                            continue;

                        $normName = $this->cleanString($name);
                        $product = $productMap[$normName] ?? null;
                        $productId = $product ? $product->id : null;

                        // Create if missing
                        if (!$product) {
                            $newProd = \App\Models\Product::create([
                                'name' => $name,
                                'unit' => $unit,
                                'category_id' => $currentCategoryId,
                                'is_active' => true
                            ]);
                            $productMap[$normName] = $newProd; // Cache full object
                            $product = $newProd;
                            $productId = $newProd->id;
                        }

                        // UPDATE PRODUCT PRICE (In-Memory Check to avoid DB writes)
                        if (!empty($priceStr)) {
                            $price = (float) str_replace([',', '.'], '', $priceStr);
                            if ($price > 0) {
                                // Only update if price changed
                                if ((float) $product->price != $price) {
                                    $product->price = $price;
                                    // Update unit if missing
                                    if (empty($product->unit) && !empty($unit)) {
                                        $product->unit = $unit;
                                    }
                                    $product->save(); // Save to DB
                                }
                            }
                        }

                        // BUFFER QUANTITY (Summation)
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
            }

            // --- STEP 3: BATCH INSERT ---
            if (count($ordersToInsert) > 0) {
                // Chunk to verify memory limits
                $chunks = array_chunk($ordersToInsert, 1000);
                foreach ($chunks as $chunk) {
                    \App\Models\MonthlyOrder::insert($chunk);
                }
            }

            $mode = $pivotMode ? 'Pivot (Siêu tốc)' : 'Đơn lẻ (Nhiều sheet)';
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
}
