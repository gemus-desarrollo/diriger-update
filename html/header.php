<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */
?>

    <div id="navbar-secondary">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div>   

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">
                        <li>
                            <label class="badge badge-danger">
                            <?= textparse($_SESSION['empresa'])?>
                            </label>
                        </li>
                    </ul>

                    <div class="navd-end">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a href="#" onclick="open_help_window('../help/02_usuarios.htm#02_4.3')">
                                    <i class="fa fa-question"></i>Ayuda
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" onclick="self.location.href='<?=_SERVER_DIRIGER?>php/exit.php?action=exit';">
                                    <i class="fa fa-power-off"></i>
                                </a>
                            </li>                            
                        </ul>
                    </div>                    
                </div>        
            </div>
        </nav>    
    </div>

    <script type="text/javascript" src="js/menu.js?version="></script>

    <script type="text/javascript">
        // Build an array initializer
        $(document).ready(function() {
            var fechacompleta = new Date();
            fechacompleta.setFullYear(<?=date('Y')?>);
            <?php $m = (int)date('n') - 1; $d = date('j'); ?>
            fechacompleta.setMonth(<?="$m, $d"?>);
            <?php $hh = date('G'); $i = (int)date('i'); $s = (int)date('s'); ?>
            fechacompleta.setHours(<?="$hh, $i, $s"?>);

            muestraReloj('<?=date('r')?>');
        });
    </script>


