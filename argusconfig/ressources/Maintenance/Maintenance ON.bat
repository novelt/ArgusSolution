@ECHO OFF
REM Novel-T Sarl
REM ------------

ECHO Maintenance ON

copy /-Y maintenance.html ..\..\..\maintenance.html
copy /-Y template.htaccess ..\..\..\.htaccess

copy ..\..\..\sesDashboard\web\.htaccess ..\..\..\sesDashboard\web\.htaccess_
del ..\..\..\sesDashboard\web\.htaccess

set /p password= Gateway Password ? 

call BatchSubstitute.bat %password% "maintenance" ..\..\config\globals.php>..\..\config\globals_.php
del ..\..\config\globals.php
copy ..\..\config\globals_.php ..\..\config\globals.php
del ..\..\config\globals_.php


ECHO Done %done%
pause