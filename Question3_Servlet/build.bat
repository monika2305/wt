@echo off
REM ==============================================================
REM  build.bat - compiles the Java source files in src\ into
REM  WebContent\WEB-INF\classes, using the Servlet API jar that
REM  comes with Tomcat.
REM
REM  Usage: double-click, or run "build.bat" in this folder.
REM  Requires: JDK 11 or newer (javac on the PATH). Tested with JDK 24.
REM ==============================================================
setlocal

REM Tomcat location - change this if Tomcat is installed elsewhere
if "%CATALINA_HOME%"=="" set "CATALINA_HOME=C:\xampp\tomcat"
set "SERVLET_API=%CATALINA_HOME%\lib\servlet-api.jar"

REM Always work relative to the folder this script is in
cd /d "%~dp0"

where javac >nul 2>nul
if errorlevel 1 (
    echo ERROR: javac was not found. Install a JDK and add its bin folder to PATH.
    exit /b 1
)

if not exist "%SERVLET_API%" (
    echo ERROR: %SERVLET_API% not found. Set CATALINA_HOME to your Tomcat folder.
    exit /b 1
)

if not exist "WebContent\WEB-INF\lib\mysql-connector-j-8.4.0.jar" (
    echo WARNING: MySQL Connector/J jar is missing from WebContent\WEB-INF\lib.
    echo          The project will compile but cannot connect to MySQL. See README.md.
)

echo Cleaning old classes...
if exist "WebContent\WEB-INF\classes" rmdir /s /q "WebContent\WEB-INF\classes"
mkdir "WebContent\WEB-INF\classes"

echo Compiling Java sources...
REM --release 8 : produce class files that run on Java 8 and newer
REM -Xlint:-options : hide the "release 8 is obsolete" warning on new JDKs
javac --release 8 -Xlint:-options -encoding UTF-8 ^
    -cp "%SERVLET_API%" ^
    -d "WebContent\WEB-INF\classes" ^
    src\com\studentportal\model\*.java ^
    src\com\studentportal\util\*.java ^
    src\com\studentportal\dao\*.java ^
    src\com\studentportal\filter\*.java ^
    src\com\studentportal\servlet\*.java

if errorlevel 1 (
    echo.
    echo BUILD FAILED - see the errors above.
    exit /b 1
)

echo.
echo BUILD SUCCESSFUL - classes are in WebContent\WEB-INF\classes
endlocal
