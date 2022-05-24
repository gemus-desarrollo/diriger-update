<?php
$x= !empty($_GET['x']) ? $_GET['x'] : 0;
echo number_format($x,1);
?>