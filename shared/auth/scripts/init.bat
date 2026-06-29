::call init <githubuser> <email>
@echo off
::check args
set argCount=0
for %%x in (%*) do (
   set /A argCount+=1
)
if %argCount%==2 (
    echo githubuser: '%~1'
    echo email:      '%~2'
) else (
    echo usage is "%~dpnx0" ^<githubuser^> ^<email^>
    exit 1
)
::run
SETLOCAL ENABLEEXTENSIONS ENABLEDELAYEDEXPANSION || call:error "failed to enable extensions"
::check setup
echo checking setup ...
where /Q git || call:not_found git
::clone
git config --global credential.helper wincred || call:error "credential setup failed"
git clone "https://github.com/%~1/auth.git" || call:error "checkout failed"
::setup git user
echo setting up user ...
SET "root=%CD%" || call:error "failed to save root"
cd /D "%root%\auth" || call:error "setting up git failed"
git config user.name "%~1" || call:error "setting git user name failed"
git config user.email "%~2" || call:error "setting git user email failed"
git gc --auto || call:error "enabling git cg failed"
::setting remote
git remote add upstream https://github.com/vizoogmbh/auth.git || call:error "failed to set upstream"
git fetch upstream || call:error "failed to fetch upstream"
::updating branches
git checkout master || call:error "failed to checkout master branch"
git pull upstream master || call:error "failed to pull upstream master"
git push || call:error "failed to push master branch"
::setting up hooks
rd /Q /S "%root%\auth\.git\hooks" || call:error "failed to remove .git\hooks"
mklink /J "%root%\auth\.git\hooks" "%root%\auth\githooks" || call:error "failed to link .git\hooks with auth\githooks"
exit 0

:not_found
echo failed to find "%~1" - make sure "%~1.exe" is in your PATH
exit 2

:error
echo %~1 - error code was %errorlevel%
exit 3
