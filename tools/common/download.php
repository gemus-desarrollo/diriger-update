<?php
/*
 * use this script to enable your visitors to download
 * your files. 
 */

$send_file= !empty($_GET['send_file']) ? 1 : 0;
$_file= urldecode($_GET['file']);
$filename= !empty($_GET['filename']) ? urldecode($_GET['filename']) : null;

$fileserver_path = dirname($_file);
$req_file 	 = basename($_file);
$whoami		 = basename(__FILE__);	// you are free to rename this file 

if (empty($filename)) 
    $filename= $req_file;

if (empty($req_file)) {
    print "Usage: $whoami?file=&lt;file_to_download&gt;";
    exit;
}

/* no web spamming */
/*
if (!preg_match("/^[a-zA-Z0-9._-~]+$/", $req_file, $matches)) {
echo "r={$req_file}  m={$matches[0]}           ";    
    print "I can't do that. sorry. No valid charecter in filename";
    exit;
}
*/
/* download any file, but not this one */
if ($req_file == $whoami) {
    print "I can't do that. sorry.";
    exit;
}

/* check if file exists */

if (!file_exists("{$fileserver_path}/{$req_file}")) {
    print "File <strong>$filename</strong> doesn't exist.";
    exit;
}

if (empty($send_file)) {
    header("Refresh: 1; url=$whoami?file={$fileserver_path}/{$req_file}&send_file=yes&filename=$filename");
}
else {  
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Length: ' . filesize("$fileserver_path/$req_file"));
    header('Content-Disposition: attachment; filename=' . urlencode($filename));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');  
    
    ob_clean();
    flush();    
    readfile("$fileserver_path/$req_file"); 
    exit;    
}
?>
