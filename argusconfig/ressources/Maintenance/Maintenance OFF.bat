@ECHO OFF
REM Novel-T Sarl
REM ------------

ECHO Maintenance OFF

del ..\..\..\.htaccess

copy ..\..\..\sesDashboard\web\.htaccess_ ..\..\..\sesDashboard\web\.htaccess
del ..\..\..\sesDashboard\web\.htaccess_

set /p password= Gateway Password ? 

call BatchSubstitute.bat "maintenance" %password% ..\..\config\globals.php>..\..\config\globals_.php
del ..\..\config\globals.php
copy ..\..\config\globals_.php ..\..\config\globals.php
del ..\..\config\globals_.php

ECHO Done %done%
pause