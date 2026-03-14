@echo off
cd /d %~dp0

chcp 65001

title DO NOT CLOSE — Laravel Queue Worker —

:refresh
cls
echo.
echo  ######################################################
echo  #                                                    #
echo  #                sim.vinaphonedanang.vn              #
echo  #                                                    #
echo  #            Terminal chạy hẹn giờ tắt SMT.          #
echo  #                                                    #
echo  #               !!! KHÔNG ĐƯỢC ĐÓNG !!!              #
echo  #                                                    #
echo  ######################################################
echo.

php artisan queue:work --sleep=3 --tries=3 --timeout=60

echo.
echo  [!] Queue worker stopped. Restarting in 5 seconds...
timeout /t 5 /nobreak
goto refresh