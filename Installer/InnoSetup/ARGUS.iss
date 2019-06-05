; SEE THE DOCUMENTATION FOR DETAILS ON CREATING INNO SETUP SCRIPT FILES!

#define MyAppName "ARGUS"
#define MyAppVersion "1.0.0"
#define MyAppPublisher "Novel-T"
; #define MyAppExeName "MyProg.exe"

[Setup]
; NOTE: The value of AppId uniquely identifies this application.
; Do not use the same AppId value in installers for other applications.
; (To generate a new GUID, click Tools | Generate GUID inside the IDE.)
AppId={{424B3B89-D529-4E6E-846D-8D5C0CCBDBE3}
AppName={#MyAppName}
AppVersion={#MyAppVersion}
AppVerName={#MyAppName} {#MyAppVersion}
AppPublisher={#MyAppPublisher}
DefaultDirName=C:\xampp\
SourceDir=C:\xampp\htdocs\
CreateAppDir=yes
OutputBaseFilename={#MyAppName} {#MyAppVersion}
OutPutDir=D:\Dropbox (Novel-T Sarl)\Novel-T Projects\WHO - IHR - Lyon - ARGUS\04 - Sources\041 - SourceCode\0415 - InnoSetup Installation\OutPut\ARGUS V{#MyAppVersion}
Compression=lzma
SolidCompression=yes
AlwaysRestart=no

[Components]
Name: "argus"; Description: "Argus Server Applications"; Types: full custom compact; Flags: fixed
Name: "argus\ArgusConfig"; Description: "Argus Server web application"; Types: full;
Name: "argus\ArgusDashboard" ; Description: "Argus Dashboard web application"; Types: full;
Name: "argus\ArgusAngular" ; Description: "Argus Validation web/mobile application"; Types: full;

[Languages]
Name: "english"; MessagesFile: "compiler:Default.isl"; LicenseFile: "D:\Dropbox (Novel-T Sarl)\Novel-T Projects\WHO - IHR - Lyon - ARGUS\04 - Sources\041 - SourceCode\0415 - InnoSetup Installation\Versions\V{#MyAppVersion}\argus_license.txt"
Name: "french"; MessagesFile: "compiler:Languages\French.isl"; LicenseFile: "D:\Dropbox (Novel-T Sarl)\Novel-T Projects\WHO - IHR - Lyon - ARGUS\04 - Sources\041 - SourceCode\0415 - InnoSetup Installation\Versions\V{#MyAppVersion}\argus_license.txt"

[Dirs]
; Backup folder
Name: "C:\Backup\SES"; Flags: uninsneveruninstall;
; R
Name: "{app}\htdocs\ScriptsR\argus"; Flags: uninsalwaysuninstall;  

[Files]
; NOTE: Don't use "Flags: ignoreversion" on any shared system files
; APPLICATION FILES
Source: "ses\*"; Excludes: "\.git, \.idea, data\output\*, data\input\*, data\processed\*, data\work\*, logs\*.txt, logs\gateways\*.txt"; DestDir: "{app}\htdocs\ses\"; Flags: recursesubdirs createallsubdirs; Components: argus\ArgusConfig
Source: "sesDashboard\*"; Excludes: "\.git, \.idea, \app\cache\dev\*, \app\cache\prod\*, \app\logs\*, \app\work\odk\*, \app\work\reports\Error\*, \app\work\reports\Success\*, \app\uploads\openrosa\*, \web\exports\*"; DestDir: "{app}\htdocs\sesDashboard\"; Flags: recursesubdirs createallsubdirs; Components: argus\ArgusDashboard
Source: "sesDashboardReports\*"; Excludes: "\.git, \.idea, \cache\*, \dashboards\*.json "; DestDir: "{app}\htdocs\sesDashboardReports\"; Flags: recursesubdirs createallsubdirs; Components: argus\ArgusDashboard
Source: "argus\*"; Excludes: ""; DestDir: "{app}\htdocs\argus\"; Flags: recursesubdirs createallsubdirs; Components: argus\ArgusAngular
; R
Source: "D:\Dropbox (Novel-T Sarl)\Novel-T Projects\WHO - IHR - Lyon - ARGUS\04 - Sources\041 - SourceCode\0415 - InnoSetup Installation\Versions\V{#MyAppVersion}\R\*"; DestDir:"{app}\htdocs\ScriptsR\argus\"; Flags: recursesubdirs createallsubdirs; Components: argus

; Copy config file for mysql to be able to init the database
Source: "D:\Dropbox (Novel-T Sarl)\Novel-T Projects\WHO - IHR - Lyon - ARGUS\04 - Sources\041 - SourceCode\0415 - InnoSetup Installation\Versions\V{#MyAppVersion}\ConfigFiles\my.ini"; DestDir:"c:\xampp\mysql\bin"; Flags: uninsneveruninstall; Components: argus

; IMPORTER TASK
Source: "D:\Dropbox (Novel-T Sarl)\Novel-T Projects\WHO - IHR - Lyon - ARGUS\04 - Sources\041 - SourceCode\0415 - InnoSetup Installation\Versions\V{#MyAppVersion}\Tasks\ImporterTask\ArgusImporter_Task.exe"; DestDir: "{app}\custom\Tasks\"; Components: argus
; ARGUS MONITORING TASK
Source: "D:\Dropbox (Novel-T Sarl)\Novel-T Projects\WHO - IHR - Lyon - ARGUS\04 - Sources\041 - SourceCode\0415 - InnoSetup Installation\Versions\V{#MyAppVersion}\Tasks\MonitoringTask\ArgusMonitoring_Task.exe"; DestDir: "{app}\custom\Tasks\"; Components: argus
; RUNNING R SCRIPT TASK
Source: "D:\Dropbox (Novel-T Sarl)\Novel-T Projects\WHO - IHR - Lyon - ARGUS\04 - Sources\041 - SourceCode\0415 - InnoSetup Installation\Versions\V{#MyAppVersion}\Tasks\RTask\ArgusScriptR_Task.exe"; DestDir: "{app}\custom\Tasks\"; Components: argus

; ANDROID APKS 
Source: "D:\Dropbox (Novel-T Sarl)\Novel-T Projects\WHO - IHR - Lyon - ARGUS\04 - Sources\041 - SourceCode\0415 - InnoSetup Installation\Versions\V{#MyAppVersion}\Apks\*"; DestDir: "{app}\custom\Apks\"; Components: argus
; DEPLOYMENT TOOL
;Source: "D:\Dropbox (Novel-T Sarl)\Novel-T Projects\WHO - IHR - Lyon - ARGUS\04 - Sources\041 - SourceCode\0415 - InnoSetup Installation\Versions\V{#MyAppVersion}\AndroidDeploymentTool\*"; DestDir: "{app}\custom\AndroidDeploymentTool\"; Flags: recursesubdirs createallsubdirs; Components: argus
; CONFIGURATION EXAMPLES FILES
Source: "D:\Dropbox (Novel-T Sarl)\Novel-T Projects\WHO - IHR - Lyon - ARGUS\04 - Sources\041 - SourceCode\0415 - InnoSetup Installation\Versions\V{#MyAppVersion}\ArgusConfiguration\*"; DestDir: "{app}\custom\ArgusConfiguration\"; Flags: recursesubdirs createallsubdirs; Components: argus

[UninstallDelete]
; Delete cache folders 
Type: filesandordirs; Name: "{app}\htdocs\sesDashboard\app\cache"
Type: filesandordirs; Name: "{app}\htdocs\sesDashboardReports\cache"
; R
Type: filesandordirs; Name: "{app}\htdocs\ScriptsR"