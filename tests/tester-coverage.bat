@echo off
%CD%\tester.bat -d extension_dir="./ext" -d zend_extension="php_xdebug.dll" --coverage coverage.html --coverage-src ..\src %*
