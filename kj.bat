chcp 65001
@echo off
setlocal enabledelayedexpansion
for /f "delims=  tokens=1" %%i in ('netstat -aon ^| findstr "8086"') do (
set a=%%i
goto js
)
:js
taskkill /f /pid "!a:~71,5!"
E:\phpStudy\PHPTutorial\php\php-5.4.45-nts\php.exe server.php

