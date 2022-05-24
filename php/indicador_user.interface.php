<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "config.inc.php";
require_once "class/base.class.php";
require_once "class/connect.class.php";
require_once "class/indicador.class.php";
?>

<?php include "_header.interface.inc"; ?>

<?php
class Tindicador_usr extends Tindicador {

    public function  __construct($clink) {
         $this->clink= $clink;
         Tbase::__construct($clink);

        $this->action= $_POST['exect'];
        $this->id_proceso= $_GET['id_proceso'];
        $this->year= $_GET['year'];
    }

    public function execute() {
         $icount= !empty($_POST['count']) ? $_POST['count'] : 0;
         $sql= null;

         for ($k= 1; $k <= $icount; ++$k) {
           $this->id_indicador= $_POST["id_".$k];
           $this->id_user_real= !empty($_POST["usuario_real_".$k]) ? (int)$_POST["usuario_real_".$k] : 0;
           $this->id_user_plan= !empty($_POST["usuario_plan_".$k]) ? (int)$_POST["usuario_plan_".$k] : 0;

           $sql.= $this->update_access(true);
         }

        $this->do_multi_sql_show_error('update_access', $sql);

        $url_page= "../form/findicador_usuarios.php";
        $url_page.= "?id=$this->id&year=$this->year&month=$this->month&day=$this->day";
        $url_page.= "&id_proceso=$this->id_proceso&id_perspectiva=$this->id_perspectiva";
        $url_page.= "&action=$this->action&exect=$this->action";
        ?>

        confirm("Desea salir a la pagina inicial de Diriger?", function(ok) {
            if (ok) {
                self.location.href='../html/background.php?csfr_token=<?=$_SESSION['csfr_token']?>&';
            } else {
                self.location.href='../form/findicador_usuarios.php?action=add';
            }
        });
    <?php
    }
}
?>
        </div>
    </body>
</html>

<script type="text/javascript">
    $(document).ready(function() {
        setInterval('setChronometer()',1);

        $('#body-log table').mouseover(function() {
            _moveScroll= false;
        });
        $('#body-log table').mouseout(function() {
            _moveScroll= true;
        });
        <?php
        $obj= new Tindicador_usr($clink);
        $obj->execute();
        ?>
    });
</script>
