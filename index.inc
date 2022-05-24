<?php
$_SESSION['virtualhost_base_dir']= "../";

switch ($id) {
    case 1:
        $_SESSION['virtualhost_base_dir']= "../empresa/";
        break;
    case 26:
        $_SESSION['virtualhost_base_dir']= "../ueb1/";
        break;
    case 27:
        $_SESSION['virtualhost_base_dir']= "../ueb2/";
        break;
    case 28:
        $_SESSION['virtualhost_base_dir']= "../ueb3/";
        break;        
}

?>