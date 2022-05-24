<?php
echo "\n";
echo extension_loaded('pgsql') ? 'yes':'no';
$connection_string= "host=192.168.0.10 port=5432 dbname=cmi-transcupet user=postgres password=postgres";
$dblink= pg_connect($connection_string) or die('connection failed');

/*
$host= "192.168.0.10";
$username= "root";
$password= "root";
$database= "cmi-transcupet";
$dblink = new mysqli($host, $username, $password, $database);

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
} else {
	echo "\n\nSe conecta MySQL";
	exit();
}
*/
?>