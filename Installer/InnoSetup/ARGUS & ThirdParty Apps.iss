; Script generated by the Inno Setup Script Wizard.
; SEE THE DOCUMENTATION FOR DETAILS ON CREATING INNO SETUP SCRIPT FILES!

#define MyAppName "ARGUS & Third Party App"
#define MyAppVersion "1.0.0"
#define MyAppPublisher "Novel-T"
; #define MyAppExeName "MyProg.exe"

[Setup]
; NOTE: The value of AppId uniquely identifies this application.
; Do not use the same AppId value in installers for other applications.
; (To generate a new GUID, click Tools | Generate GUID inside the IDE.)
AppId={{B3AE6627-D554-4E06-8FCC-20C3F3E8E386}
AppName={#MyAppName}
AppVersion={#MyAppVersion}
AppVerName={#MyAppName} {#MyAppVersion}
AppPublisher={#MyAppPublisher}
CreateAppDir=no
SourceDir=D:\Dropbox (Novel-T Sarl)\Novel-T Projects\WHO - IHR - Lyon - ARGUS\04 - Sources\041 - SourceCode\0415 - InnoSetup Installation\
OutputBaseFilename={#MyAppName} {#MyAppVersion}
OutPutDir=D:\Dropbox (Novel-T Sarl)\Novel-T Projects\WHO - IHR - Lyon - ARGUS\04 - Sources\041 - SourceCode\0415 - InnoSetup Installation\OutPut\ARGUS V{#MyAppVersion}
Compression=lzma
SolidCompression=yes
AlwaysRestart=yes

[Components]
Name: "argus"; Description: "Argus Server Application"; Types: full custom compact; Flags: fixed
Name: "python"; Description: "Python 2.7.13"; Types: full ;
Name: "visualCx86"; Description :"Visual C++ Redistributable x86"; Types: full ;
Name: "visualCx64"; Description :"Visual C++ Redistributable x64"; Types: full ;
Name: "xampp"; Description: "Xampp 5.6.8"; Types: full;
Name: "R"; Description: "R component"; Types: full;
Name: "R\R"; Description: "R-3.5.1"; Types: full;
Name: "R\Rtools"; Description: "Rtools35"; Types: full;
Name: "R\Pandoc"; Description: "pandoc-2.4-windows-x86_64"; Types: full;
Name: "tools"; Description: "Tools"; Types: full
Name: "tools\NotePad" ; Description: "NotePad++"; Types: full;

[Languages]
Name: "english"; MessagesFile: "compiler:Default.isl"
Name: "french"; MessagesFile: "compiler:Languages\French.isl"

[Files]
; NOTE: Don't use "Flags: ignoreversion" on any shared system files
Source: "ThirdPartyApps\xampp-win32-5.6.8-0-VC11-installer.exe"; DestDir: "{tmp}"; Flags: ignoreversion; Components: xampp
Source: "ThirdPartyApps\python-2.7.13.msi"; DestDir: "{tmp}"; Flags: ignoreversion; Components: python 
Source: "ThirdPartyApps\vcredist_x86.exe"; DestDir: "{tmp}"; Flags: ignoreversion; Components: visualCx86 
Source: "ThirdPartyApps\vcredist_x64.exe"; DestDir: "{tmp}"; Flags: ignoreversion; Components: visualCx64 
Source: "ThirdPartyApps\R-3.5.1-win.exe"; DestDir: "{tmp}"; Flags: ignoreversion; Components: R\R 
Source: "ThirdPartyApps\Rtools35.exe"; DestDir: "{tmp}"; Flags: ignoreversion; Components: R\Rtools 
Source: "ThirdPartyApps\pandoc-2.4-windows-x86_64.msi"; DestDir: "{tmp}"; Flags: ignoreversion; Components: R\Pandoc 

; Tools
Source: "ThirdPartyApps\Tools\npp.6.9.1.Installer.exe"; DestDir: "{tmp}"; Flags: ignoreversion; Components: tools\NotePad

; ARGUS
Source: "Output\ARGUS V{#MyAppVersion}\ARGUS {#MyAppVersion}.exe"; DestDir: "{tmp}"; Flags: ignoreversion; Components: argus 

[Run]
Filename: "{tmp}\xampp-win32-5.6.8-0-VC11-installer.exe"; StatusMsg: "Installing Xampp..."; Components: xampp
Filename: "msiexec.exe"; Parameters: "/i ""{tmp}\python-2.7.13.msi"""; StatusMsg: "Installing Python 2.7.13 ..."; Components: python
Filename: "{tmp}\vcredist_x86.exe"; StatusMsg: "Installing Visual C++ Redistributable ..."; Components: visualCx86
Filename: "{tmp}\vcredist_x64.exe"; StatusMsg: "Installing Visual C++ Redistributable ..."; Components: visualCx64

; R
Filename: "{tmp}\R-3.5.1-win.exe"; StatusMsg: "Installing R 3.5.1 ..."; Components: R\R
Filename: "{tmp}\Rtools35.exe"; StatusMsg: "Installing Rtools 3.5 ..."; Components: R\Rtools
Filename: "msiexec.exe"; Parameters: "/i ""{tmp}\pandoc-2.4-windows-x86_64.msi"""; StatusMsg: "Installing Pandoc 2.4 ..."; Components: R\Pandoc

; Tools
Filename: "{tmp}\npp.6.9.1.Installer.exe"; StatusMsg: "Installing NotePad++..."; Components: tools\NotePad

; Create a new installer specific for All ARGUS files ( Apps, Worker, etc.. )
Filename: "{tmp}\ARGUS {#MyAppVersion}.exe"; StatusMsg: "Installing Argus Server components..."; Components: argus 

[Code]
procedure DeployArgusFiles();
begin
  MsgBox('Now deployment of Argus Files', mbInformation, MB_OK);
end;