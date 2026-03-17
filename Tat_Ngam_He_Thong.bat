@echo off
title TAT HE THONG VAT TU TREN MAY CHU
color 0c

echo =======================================================
echo          DANG TAT HE THONG QUAN LY VAT TU ngam...        
echo =======================================================
echo.
echo 1. Dang dong May chu Laravel chay ngam...
taskkill /F /IM php.exe >nul 2>&1
taskkill /F /IM mysqld.exe >nul 2>&1

echo 2. Dang dong ket noi Cloudflare 4G...
taskkill /F /IM cloudflared.exe >nul 2>&1

echo 3. Dang xoa Link cu...
del cloudflare_log.txt >nul 2>&1
del Link_Truy_Cap.txt >nul 2>&1

echo.
echo Da tat an toan toan bo he thong! 
echo Bay gio duong link 4G khong the vao duoc nua.
timeout /t 3 >nul
exit

