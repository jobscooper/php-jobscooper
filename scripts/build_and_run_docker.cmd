@Echo off
IF %~1.==. SET branch=use-propel-orm
IF NOT %~1.==. SET branch=%1



REM "***************************************************************"
REM ""
REM "Getting a unique datestamp to use for the folders & filenames
REM ""
REM "***************************************************************"

:: Check WMIC is available
WMIC.EXE Alias /? >NUL 2>&1 || GOTO s_error

:: Use WMIC to retrieve date and time
FOR /F "skip=1 tokens=1-6" %%G IN ('WMIC Path Win32_LocalTime Get Day^,Hour^,Minute^,Month^,Second^,Year /Format:table') DO (
   IF "%%~L"=="" goto s_done
      Set _yyyy=%%L
      Set _mm=00%%J
      Set _dd=00%%G
      Set _hour=00%%H
      SET _minute=00%%I
)
:s_done

:: Pad digits with leading zeros
      Set _mm=%_mm:~-2%
      Set _dd=%_dd:~-2%
      Set _hour=%_hour:~-2%
      Set _minute=%_minute:~-2%

:: Display the date/time in ISO 8601 format:
Set _isodate=%_yyyy%-%_mm%-%_dd% %_hour%:%_minute%
Set _filedate=%_yyyy%%_mm%%_dd%-%_hour%%_minute%




REM "***************************************************************"
REM ""
REM "Setting variable parameters to use during this script
REM ""
REM "***************************************************************"

SET jobsname=jobs-%branch%
SET imagetag=selner/js4-%branch%
echo Branch is %branch%.
echo Container name is %jobsname%.
echo Image tag is %imagetag%.
SET TMPDIR=%TEMP%\%JOBSNAME%-%_filedate%
SET REPOZIP=%TEMP%\repo-%BRANCH%-%_filedate%.zip
MKDIR %TMPDIR%


ECHO "***************************************************************"
ECHO ""
ECHO "Removing any previous Docker container %jobsname%"
ECHO ""
ECHO "***************************************************************"
docker rm -f %jobsname%
REM docker rmi %imagetag%


ECHO "***************************************************************"
ECHO ""
ECHO "Downloading repo source for jobs_scooper with branch %BRANCH%"
ECHO ""
ECHO "***************************************************************"
curl -v --url "https://github.com/selner/job_scooper_v4/archive/%BRANCH%.zip" --ssl --insecure --location --output %REPOZIP%

ECHO "***************************************************************"
ECHO ""
ECHO Extracting repo source file %REPOZIP% to %TMPDIR%
ECHO ""
ECHO "***************************************************************"
ECHO Add-Type -AssemblyName System.IO.Compression.FileSystem   > unziprepo.ps1
ECHO [IO.Compression.ZipFile]::ExtractToDirectory("%REPOZIP%", "%TMPDIR%") >> unziprepo.ps1
PowerShell.exe -NoProfile -ExecutionPolicy Bypass -Command "& ./unziprepo.ps1"
REN %TMPDIR%\job_scooper_v4-%BRANCH% source

ECHO "***************************************************************"
ECHO ""
ECHO "Copying custom userfiles to %TMPDIR%..."
ECHO ""
ECHO "***************************************************************"
IF EXIST userfiles (
	IF NOT EXIST %TMPDIR%\source\userfiles (
		MKDIR %TMPDIR%\source\userfiles
	)
	XCOPY userfiles %TMPDIR%\source\userfiles /E /I /H /Y
)

ECHO "***************************************************************"
ECHO ""
echo Building docker image from %TMPDIR%\source directory
ECHO ""
ECHO "***************************************************************"
PUSHD %TMPDIR%\source

docker build -t %imagetag% . 

ECHO "***************************************************************"
ECHO ""
echo Starting docker $jobsname%
ECHO ""
ECHO "***************************************************************"
docker run --volume C:\var\local\jobs_scooper:/var/local/jobs_scooper --volume C:\var\local\jobs_scooper:/private/var/local/jobs_scooper --volume %JOBSCOOPER_PROPEL_INI%:/private/var/local/jobs_scooper/configs/propel.ini  -e "NLTK_DATA=/private/var/local/jobs_scooper/nltk_data" -e "JOBSCOOPER_OUTPUT=/private/var/local/jobs_scooper/output" --hostname %COMPUTERNAME%_docker --name %jobsname% -d %imagetag%

POPD
docker logs -f %jobsname%

:s_error


