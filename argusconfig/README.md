# Argus Config

Argus configuration application, used to manage received and sent SMS.

1. Synching SMS : Android ANDROIDID=1, VERSION=1.1.0
2. Weekly Report : RT DIS=AFP,WK=43,YR=2017,P60D=0,M60D=0,UKN=0
3. Monthly Report : 
4. Alert : ALERT EVENEMENT=Suspicion de choléra,DATE=14/07/2018,LIEU=village de Boudié,ANDROIDID=273

## Bulk SMS Tool

The Bulk SMS tool lets you test the sending and reception of a large amount of SMS.
* All config and useful files are in the [test](test) folder
* You will find logs in the [logs](test/gateway/logs) folder

### Configuration

1. Create a new specific database for your tests
2. Run script [gateway_test](test/gateway/gateway_test.sql) on your created database
3. Open configuration file [globals.php](test/globals.php) and update the settings to target your created database
  ```
  $config["mysql_server"]      // Server IP
  $config["mysql_db"]          // Database name
  $config["mysql_user"]        // Mysql user
  $config["mysql_password"]    // Mysql password

  $config["ArgusSMS_secret"]		// Gateway password (0011 by default)
  $config["ArgusSMS_pendingSms"]	// Number of SMS pulled by the gateway (10)
  ```
4. Uncomment the following line in the [.htaccess](test/gateway/.htaccess) file
  ```
  # Allow from env=gatewaytest
  ```
5. Configure the 2 gateways used for this test
    * Disable Argus Gateway on the phone
    * Open "Server URL" setting and put __http://\<XX.XX.XX.XX:XX\>/\<DirectoryName\>/test/gateway/gateway.php__
    * Open "Your phone Number" setting and put the __international phone number__ of the SIM card in the gateway
    * Open "Password" setting and put the __password defined in the [globals.php](test/globals.php) file__
    * From the Log View of Argus Gateway, open the menu and click "Test Connection"
6. Use the [SMSGenerator.xlsm](test/gateway/SMSGenerator.xlsm) to create mass SMS
    * Run the INSERT queries generated on your database
7. You can use a query like the following to check if all messages have been received :
  ```
  SELECT * from ses_gateway_queue as s LEFT JOIN ses_incoming_sms r ON s.message = r.message  COLLATE utf8_unicode_ci;
  ```

## How to generate an SQL install script
Open a command window and go into the repository folder.
Run the following command:
```
mysqldump avadar -u [root_username] -p --add-drop-database --add-drop-table -d > ".\ressources\Database\Schema\01_Install\01_Structure.sql"
```

This will generate a file to create tables. The file also contains views definitions because we can not explude them using mysqldump.
But this is not a problem, views will be re-created by the scripts in the folder 03_Views.

Open the file using Notepadd++, and do the following replaces in __Regular expression mode__
  * __replace__: AUTO_INCREMENT\=\d* __with__: AUTO_INCREMENT=1
  * __replace__ : \r\n.* PARTITION BY .*\r\n __with__: ;\r\n
  * __replace__: .\*PARTITION p_.*\\r\n __with__: [nothing]
  
