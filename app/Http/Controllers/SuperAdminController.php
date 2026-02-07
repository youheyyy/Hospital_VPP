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
        if (file_exists($backupPath . '/' . $filenameV1)) {
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
                $dump->start($backupPath . '/' . $filenameV1);
            } catch (\Exception $e) {
                // Log error but continue causing page load logic
                \Illuminate\Support\Facades\Log::error('Auto refresh V1 failed: ' . $e->getMessage());
            }
        }

        if (file_exists($backupPath . '/' . $filenameV1) && !file_exists($backupPath . '/' . $filenameV2)) {
            copy($backupPath . '/' . $filenameV1, $backupPath . '/' . $filenameV2);
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
            'month' => 'required|date_format:Y-m', // "2026-02"
        ]);

        try {
            $file = $request->file('excel_file');
            $monthInput = $request->input('month');
            // Convert "2026-02" to "02/2026" for database storage if that's the format
            // Check monthly_orders table migration: $table->string('month', 7); // Format: MM/YYYY
            $monthParts = explode('-', $monthInput);
            $targetMonth = "{$monthParts[1]}/{$monthParts[0]}";

            // Load Excel file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());

            // --- STEP 1: PARSE "TỔNG HỢP" SHEET (Master Data) ---
            $masterSheet = $spreadsheet->getSheetByName('TỔNG HỢP');
            if ($masterSheet) {
                $rows = $masterSheet->toArray();
                $currentCategoryId = null;
                // row 0 is header usually? Let's iterate.
                // Based on User feedback, we scan for categories and products.
                // Assuming Row 9 is header, data starts Row 10 (index 9). But let's scan all.

                foreach ($rows as $index => $row) {
                    if ($index < 5)
                        continue; // Skip top header

                    $colA = trim($row[0] ?? ''); // STT
                    $colB = trim($row[1] ?? ''); // Tên hàng / Danh mục
                    $colC = trim($row[2] ?? ''); // ĐVT
                    $colE = trim($row[4] ?? ''); // Đơn giá (check index carefully: A=0, B=1, C=2, D=3, E=4)

                    // Identify Category: usually no STT, no Unit, and has Name
                    // Or "VĂN PHÒNG PHẨM..." style.
                    // Simple heuristic: If STT is empty AND Name is not empty AND Unit is empty -> Category
                    // OR if Name is UPPERCASE and long?

                    // Better approach from user image: Category "VĂN PHÒNG PHẨM..." is in Blue, no STT.

                    if (empty($colA) && !empty($colB) && empty($colC)) {
                        // Likely a Category
                        $category = \App\Models\Category::updateOrCreate(
                            ['name' => $colB],
                            ['is_active' => true]
                        );
                        $currentCategoryId = $category->id;
                    }
                    // Identify Product: Has STT (numeric)
                    elseif (is_numeric($colA) && !empty($colB)) {
                        // It's a product
                        $price = (float) str_replace([',', '.'], '', $colE); // Remove commas/dots

                        \App\Models\Product::updateOrCreate(
                            ['name' => $colB],
                            [
                                'unit' => $colC,
                                'price' => $price,
                                'category_id' => $currentCategoryId, // Assign to current category
                                'is_active' => true
                            ]
                        );
                    }
                }
            }

            // --- STEP 2: PARSE DEPARTMENT SHEETS ---
            $sheetNames = $spreadsheet->getSheetNames();
            $ignoredSheets = ['BẢNG TỔNG', 'TỔNG HỢP', 'Highlights', 'Sheet1', 'Tong hop', 'Ghi chu'];

            $processedDepartments = 0;
            $processedOrders = 0;

            foreach ($sheetNames as $sheetName) {
                if (in_array($sheetName, $ignoredSheets))
                    continue;

                // 1. Identify/Create Department
                $departmentName = trim($sheetName);
                if (empty($departmentName))
                    continue;

                $department = \App\Models\Department::firstOrCreate(
                    ['name' => $departmentName],
                    ['code' => strtoupper(\Illuminate\Support\Str::slug($departmentName))]
                );

                // 2. Clean Old Data for this Month + Dept
                \App\Models\MonthlyOrder::where('department_id', $department->id)
                    ->where('month', $targetMonth)
                    ->delete();

                // 3. Parse Sheet
                $sheet = $spreadsheet->getSheetByName($sheetName);
                $rows = $sheet->toArray();

                foreach ($rows as $index => $row) {
                    if ($index < 8)
                        continue; // Skip headers (Row 9 is header, data starts Row 10 -> index 9)

                    $colA = trim($row[0] ?? ''); // STT
                    $colB = trim($row[1] ?? ''); // Tên hàng (Product Name)
                    $colD = trim($row[3] ?? ''); // Số lượng (A=0, B=1, C=2, D=3)

                    // Only process rows with numeric STT (valid products)
                    if (is_numeric($colA) && !empty($colB)) {
                        $product = \App\Models\Product::where('name', $colB)->first();

                        if ($product) {
                            $quantity = (float) str_replace([',', '.'], '', $colD);

                            if ($quantity > 0) {
                                \App\Models\MonthlyOrder::create([
                                    'department_id' => $department->id,
                                    'product_id' => $product->id,
                                    'month' => $targetMonth,
                                    'quantity' => $quantity,
                                    'notes' => '' // Can parse note from Col F if needed
                                ]);
                                $processedOrders++;
                            }
                        }
                    }
                }
                $processedDepartments++;
            }

            $this->logActivity('Import Excel Nâng cao', "Đã import dữ liệu tháng {$targetMonth} cho {$processedDepartments} khoa.");
            return redirect()->route('superadmin.data-management')->with('success', "Import thành công! Đã xử lý {$processedDepartments} khoa và {$processedOrders} đơn hàng cho tháng {$targetMonth}.");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Advanced Import failed: ' . $e->getMessage());
            return redirect()->route('superadmin.data-management')->with('error', 'Lỗi Import Nâng cao: ' . $e->getMessage());
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
