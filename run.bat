@echo off
echo =======================================================
echo          DANG KHOI DONG HE THONG QUAN LY VAT TU        
echo =======================================================
echo.
echo 1. Dang bat May chu noi bo Laravel...
start "Laravel Server" cmd /k "php artisan serve"
echo.
echo 2. Dang ket noi voi Cloudflare tao duong link 4G...
start "Cloudflare Tunnel" cmd /k "cloudflared tunnel --url http://127.0.0.1:8000"
echo.
echo Da ra lenh xong! Ban co the tat cua so nho nay (2 cua so den kia van phai de mo nhe).
pause
