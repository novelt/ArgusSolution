REM Novel-T Sarl
REM ------------

ECHO Creating Argus Monitoring task running at Logon
@ECHO OFF

elevate64 schtasks /create /XML "C:\Temp\ScheduledTask\ARGUS Boot ArgusMonitoringScheduler.xml" /TN "ARGUS Boot ArgusMonitoringScheduler"

ECHO Done