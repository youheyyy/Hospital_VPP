<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TimeService
{
    /**
     * Lấy thời gian hiện tại chuẩn xác (Ưu tiên từ Network)
     */
    public static function now()
    {
        $cachedNetworkTime = Cache::get('network_time_now');
        $cachedAt = Cache::get('network_time_cached_at');
        $systemNow = now();

        if ($cachedNetworkTime && $cachedAt) {
            // Kiểm tra xem đồng hồ hệ thống có bị nhảy (drift) quá lớn không (> 5 phút)
            // Nếu có, xóa cache và lấy lại từ đầu
            $drift = abs($systemNow->diffInSeconds(Carbon::parse($cachedAt)));
            
            if ($drift < 300) { // Trong khoảng 5 phút thì tin tưởng cache
                $diffSeconds = $systemNow->diffInSeconds(Carbon::parse($cachedAt), false);
                return Carbon::parse($cachedNetworkTime)->addSeconds($diffSeconds);
            }
            
            Cache::forget('network_time_now');
            Cache::forget('network_time_cached_at');
        }

        try {
            // Thử gọi WorldTimeAPI (Cần internet)
            $response = Http::timeout(3)->get('http://worldtimeapi.org/api/timezone/Asia/Ho_Chi_Minh');
            
            if ($response->successful()) {
                $data = $response->json();
                $networkNow = Carbon::parse($data['datetime']);
                
                // Lưu vào cache ngắn (30 giây) để giảm tải API nhưng vẫn cập nhật nhanh
                Cache::put('network_time_now', $networkNow->toIso8601String(), 30);
                Cache::put('network_time_cached_at', $systemNow->toIso8601String(), 30);
                
                return $networkNow;
            }
        } catch (\Exception $e) {
            Log::warning("TimeService: Không thể lấy Network Time: " . $e->getMessage());
        }

        // Fallback về giờ hệ thống nếu thất bại
        return $systemNow;
    }

    /**
     * Kiểm tra xem đã quá hạn chót cho một tháng cụ thể chưa
     */
    public static function isPastDeadline($monthString, $day = 25)
    {
        try {
            if (empty($monthString)) return true;

            $now = self::now();
            
            // Đảm bảo định dạng m/Y
            if (strpos($monthString, '/') === false) {
                 return true; // Khóa nếu dữ liệu không hợp lệ
            }

            $orderMonth = Carbon::createFromFormat('m/Y', $monthString)->startOfMonth();
            $deadline = $orderMonth->copy()->day($day)->endOfDay();
            
            // Nếu tháng yêu cầu là tháng cũ (quá khứ), luôn khóa
            if ($orderMonth->lt($now->copy()->startOfMonth())) {
                return true;
            }

            return $now->gt($deadline);
        } catch (\Exception $e) {
            Log::error("TimeService Error: " . $e->getMessage());
            return true; // Fallback: Khóa nếu có lỗi để đảm bảo an toàn
        }
    }

    /**
     * Lấy tháng của chu kỳ hiện tại (Tự động chuyển sang tháng sau từ ngày 26)
     */
    public static function getCurrentCycleMonth()
    {
        $now = self::now();
        
        // Nếu ngày hiện tại từ 26 trở đi, mặc định là chu kỳ của tháng sau
        if ($now->day >= 26) {
            return $now->copy()->addMonth()->format('m/Y');
        }
        
        return $now->format('m/Y');
    }

    /**
     * Lấy tháng sớm nhất có dữ liệu của khoa
     */
    public static function getEarliestMonth($departmentId = null)
    {
        $query = \App\Models\MonthlyOrder::query();
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        $months = $query->distinct()->pluck('month');
        
        if ($months->isEmpty()) {
            return self::now()->format('m/Y');
        }
        
        try {
            return $months->map(function($m) {
                try {
                    return Carbon::createFromFormat('m/Y', $m)->startOfMonth();
                } catch (\Exception $e) {
                    return null;
                }
            })->filter()
            ->min()
            ->format('m/Y');
        } catch (\Exception $e) {
            return self::now()->format('m/Y');
        }
    }
}
