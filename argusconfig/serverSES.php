<?php

require_once(__DIR__."/config/globals.php");

//Reduce errors
error_reporting(~E_WARNING);
//global $config ;

//Create a UDP socket
if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    die("Couldn't create socket: [$errorcode] $errormsg \n");
}

echo "Socket created \n";

// Bind the source address
if( !socket_bind($sock, "0.0.0.0" , 9999) )
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    die("Could not bind socket : [$errorcode] $errormsg \n");
}

echo "Socket bind OK \n";

$host= gethostname();
$ip = gethostbyname($host);


echo $ip."\n" ;
//echo $config["SesServer_address"];
//echo $port."\n" ;


//Do some communication, this loop can handle multiple clients
while(1)
{
    global $config ;
    echo "Waiting for request ... \n";

    //Receive some data
    $r = socket_recvfrom($sock, $buf, 512, 0, $remote_ip, $remote_port);
    echo "$remote_ip : $remote_port --" . $buf. "--\n";

	if($buf == "DISCOVER_SES_SERVER_REQUEST") {

	echo "Reply sent\n";;

	   //Send back the data to the client
		socket_sendto($sock, $config["ArgusGateway_address"], 100 , 0 , $remote_ip , $remote_port);
	}
}

socket_close($sock);
