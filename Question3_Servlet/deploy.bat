@echo off
REM ==============================================================
REM  deploy.bat - builds the project and copies WebContent into
REM  Tomcat's webapps folder as the "StudentPortal" application.
REM
REM  After deploying, open:  http://localhost:8080/StudentPortal/
REM ==============================================================
setlocal

if "%CATALINA_HOME%"=="" set "CATALINA_HOME=C:\xampp\tomcat"
set "APP_NAME=StudentPortal"
set "TARGET=%CATALINA_HOME%\webapps\%APP_NAME%"

cd /d "%~dp0"

REM 1. Compile first
call "%~dp0build.bat"
if errorlevel 1 (
    echo DEPLOY CANCELLED because the build failed.
    exit /b 1
)

REM 2. Copy (mirror) WebContent -> webapps\StudentPortal
echo.
echo Copying files to %TARGET% ...
robocopy "WebContent" "%TARGET%" /MIR /NFL /NDL /NJH /NJS /NP
REM robocopy exit codes 0-7 mean success, 8 or higher mean failure
if errorlevel 8 (
    echo DEPLOY FAILED - could not copy files.
    exit /b 1
)

REM 3. Update web.xml's timestamp so a RUNNING Tomcat notices the change
REM    and reloads the application automatically.
copy /b "%TARGET%\WEB-INF\web.xml" +,, "%TARGET%\WEB-INF\web.xml" >nul

echo.
echo DEPLOYED to %TARGET%
echo If Tomcat is running it will reload the app in a few seconds;
echo otherwise start Tomcat from the XAMPP Control Panel.
echo Then open: http://localhost:8080/%APP_NAME%/
endlocal
