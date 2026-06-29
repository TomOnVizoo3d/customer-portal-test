@echo off
SETLOCAL EnableExtensions  || goto :error
SETLOCAL EnableDelayedExpansion  || goto :error


(for /f "usebackq delims=" %%P in (`dir /s /b "%~dp0\wp-content\*.src.css"`) do (
    set "filePath=%%P" || goto :error
    call :fileName "fileName" "!filePath!" || goto :error
    call :fileName "fileName" "!fileName!" || goto :error
    call :parentDir "parentDir" "!filePath!\.." || goto :error
    call npx postcss "!filePath!" -c ./postcss.config.js -o "!parentDir!!fileName!.css" --map || goto :error
)) || goto :error
exit /B 0

::returns the full path to the parent-directory
::usage parentDir <output variable> <path>
:parentDir
set "%~1=%~dp2"
exit /B %errorlevel%

::returns the full filename without the directory
::usage parentDir <output variable> <path>
:fileName
set "%~1=%~n2"
exit /B %errorlevel%


:error
set "errorcode=%errorlevel%"
echo Couldn't find valid stylesheet. Please make sure to use naming pattern *.src.css
exit /B %errorcode%
