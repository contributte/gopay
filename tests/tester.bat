@echo off
%CD%\..\vendor\bin\tester.bat %CD%\Gopay -s -j 40 -log %CD%\gopay.log %*
rmdir %CD%\tmp /Q /S
