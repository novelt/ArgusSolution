REM Novel-T Sarl
REM ------------

ECHO Creating Argus Importer Daily tasks running every 1 minute
@ECHO OFF
SET /P Login=Login :
SET /P Password=Password :

elevate64 schtasks /create /XML "C:\Temp\ScheduledTask\ARGUS Importer.xml" /TN "ARGUS Importer" /RU "%Login%" /RP "%Password%" 

ECHO Done