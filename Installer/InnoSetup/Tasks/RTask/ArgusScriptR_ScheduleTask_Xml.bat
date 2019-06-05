REM Novel-T Sarl
REM ------------

ECHO Creating Argus ScriptR Daily tasks running every 15 minutes
@ECHO OFF
SET /P Login=Login :
SET /P Password=Password :

elevate64 schtasks /create /XML "C:\Temp\ScheduledTask\ARGUS ScriptR.xml" /TN "ARGUS RScripts" /RU "%Login%" /RP "%Password%" 

ECHO Done