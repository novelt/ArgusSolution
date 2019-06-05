# Core settings - you will need to set these 
$mysql_server = "localhost"
$mysql_user = "root" 
$mysql_password = "" 
$backupstorefolder= "C:\Backup\MySqlBackup\" 
$dbName = "avadar"

# Days in the past to keep. Must be negative
$daysBackupToKeep = -30

$pathtomysqldump = "C:\xampp\mysql\bin\mysqldump.exe"

# Minimum size to consider backup valid 
$bkpminsize=0.2

# Determine Today Date Day (monday, tuesday etc)
$timestamp = Get-Date -format yyyyMMdd-HHmmss

$bkpsuffix = "_" + $dbName + ".sql"

# Set backup filename and check if exists, if so delete existing
$backupfilename = $timestamp + $bkpsuffix
$backuppathandfile = $backupstorefolder + "" + $backupfilename

write-host "Backing up database: " $dbName " to " $backuppathandfile

 If (test-path($backuppathandfile)) {
    write-host "Backup file '" $backuppathandfile "' already exists. Existing file will be deleted"
    Remove-Item $backuppathandfile
 }
 
try 
{
 # Invoke backup Command. /c forces the system to wait to do the backup

    If ($mysql_password.Length -eq 0){
		$out = cmd /c " `"$pathtomysqldump`" -h $mysql_server -u $mysql_user $dbname --result-file=$backuppathandfile " 2>&1
	}
	Else{
		$out = cmd /c " `"$pathtomysqldump`" -h $mysql_server -u $mysql_user -p$mysql_password $dbname --result-file=$backuppathandfile " 2>&1
	}

}
catch
{
    write-host "Powershell error when creating backup:" $_.Exception.Message

    Exit
}


# Check if error string in output
if( ($out -ne $null) -and ($out.Length -gt 0) ) {

	if( $out -Match "error" ) 	{

        write-host "Mysql error when creating backup:" $out

        If (test-path($backuppathandfile)) {

            write-host "Delete file just created."

            Remove-Item $backuppathandfile
        }

        Exit
	}
}


if (test-path($backuppathandfile)) {

   if((Get-Item $backuppathandfile).length/1MB -lt $bkpminsize) {

       write-host "File is too small. Remove file just created"
       
       Remove-Item $backuppathandfile     
   }
}

# Filter to list backup files
$filter = "*" + $bkpsuffix

$date = Get-Date

$bkpfiles = Get-ChildItem $backupstorefolder  | Where-Object {$_.Attributes -ne "Directory"} | Sort-Object LastWriteTime -Descending

foreach ($file in $bkpfiles) {

    $file_date = get-date $file.LastWriteTime


    if ($file_date -le $date.AddDays($daysBackupToKeep)) {

        Write-Host "Remove old file " $file.Name

        Remove-Item $file.FullName
    }
}

 







