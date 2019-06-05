# ARGUS Dashboard Application

## Composer Installation
AS Xampp is using php 32 bits, composer doesn't work anymore. To be able to run composer, follow those steps :
* Download a version of php 5.6.8 64 bits [here](https://windows.php.net/downloads/releases/archives/php-5.6.8-Win32-VC11-x64.zip)
* Unzip the content into a new php folder
* modify php.ini to : 
    * update the memory limit : `memory_limit = -1`
    * activate php extensions : 
        * `extension=php_pdo_sqlite.dll`
        * `extension=php_mbstring.dll`

* open a command prompt in the project folder
* run `"<Php 64 bits folder>\php.exe" "<Composer install folder>\bin\composer.phar" install`
    * Example : `"c:\xampp\php 5.6.8\php.exe" "c:\ProgramData\ComposerSetup\bin\composer.phar" install`
