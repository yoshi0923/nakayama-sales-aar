@echo off
chcp 65001 > nul
cd /d "%~dp0"

echo ===================================
echo  中山営業AARシステム 起動
echo ===================================
echo.

echo [1/3] 設定キャッシュをクリア中...
php artisan config:clear
if errorlevel 1 (
    echo エラー: config:clear に失敗しました
    pause
    exit /b 1
)

echo [2/3] XAMPPのMySQLが起動していることを確認してください
echo       http://localhost/phpmyadmin で確認できます
echo.

echo [3/3] サーバー起動中...
echo.
echo  Laravel : http://localhost:8001
echo  Vite HMR: 自動起動
echo.
echo  停止するには Ctrl+C を押してください
echo ===================================
echo.

npx concurrently ^
  -c "#93c5fd,#c4b5fd" ^
  "php artisan serve --port=8001" ^
  "npm run dev" ^
  --names="Laravel,Vite " ^
  --kill-others
