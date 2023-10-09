<?php
$no_info = 1; // no echoing info from patreon.php file
require_once("../patreon.php");

$file = "files/".$_GET['f'];
$fp = fopen($file, 'rb');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=$_GET[f]");
header("Content-Length: " . filesize($file));
fpassthru($fp);
?>