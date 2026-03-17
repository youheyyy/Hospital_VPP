@echo off
title BAO MAT TREN MAY CHU: DANG MO WEB (KHONG MO XAMPP)
color 0a

echo =======================================================
echo      DANG KHI DONG HE THONG CHAY NGAM (KHONG XAMPP)...        
echo =======================================================
echo.

echo 1. Dang xoa cac phien lam viec cu de tranh loi...
taskkill /F /IM php.exe >nul 2>&1
taskkill /F /IM cloudflared.exe >nul 2>&1
del cloudflare_log.txt >nul 2>&1
del Link_Truy_Cap.txt >nul 2>&1

echo.
echo 2. Dang bat May chu Laravel vao che do an (chay ngam)...
powershell -WindowStyle Hidden -Command "Start-Process cmd -ArgumentList '/c php artisan serve' -WindowStyle Hidden"

echo.
echo 3. Dang ket noi voi Cloudflare tao Link 4G (chay ngam)...
powershell -WindowStyle Hidden -Command "Start-Process cmd -ArgumentList '/c cloudflared tunnel --url http://127.0.0.1:8000 2> cloudflare_log.txt' -WindowStyle Hidden"

echo.
echo 4. Dang cho he thong tao duong dan tu dong (khoang 7->10 giay)...
timeout /t 10 /nobreak >nul

echo.
echo 5. Dang trich xuat Link gui cho ban qua Notepad...
powershell -Command "$content = Get-Content 'cloudflare_log.txt' -ErrorAction SilentlyContinue | Out-String; if ($content -match '(https://[a-zA-Z0-9-]+\.trycloudflare\.com)') { $url = $matches[1]; '===================================================' | Out-File -Encoding ASCII 'Link_Truy_Cap.txt'; ' DAY LA DUONG DAN 4G CUA BAN CHO CA NGAY HOM NAY' | Out-File -Encoding ASCII -Append 'Link_Truy_Cap.txt'; '===================================================' | Out-File -Encoding ASCII -Append 'Link_Truy_Cap.txt'; '' | Out-File -Encoding ASCII -Append 'Link_Truy_Cap.txt'; $url | Out-File -Encoding ASCII -Append 'Link_Truy_Cap.txt'; '' | Out-File -Encoding ASCII -Append 'Link_Truy_Cap.txt'; C:\xampp\php\php.exe update_rebrandly.php "$url"; 'Luu y: Chieu/Toi khi ve, ban nho CHAY FILE [Tat_Ngam_Khong_XAMPP.bat] nhe!' | Out-File -Encoding ASCII -Append 'Link_Truy_Cap.txt'; Start-Process notepad 'Link_Truy_Cap.txt' } else { 'Mang hoi cham. Ban cho chut hoac mo file cloudflare_log.txt de xem nhe!' | Out-File -Encoding ASCII 'Link_Truy_Cap.txt'; Start-Process notepad 'Link_Truy_Cap.txt' }"

echo.
echo Hoan tat! Web da chay an. Notepad chua Link cua ban se mo ra.
echo Ban co the dong cua so den nay.
timeout /t 3 >nul
exit


