<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "config.inc.php";

require_once "interface.class.php";

require_once "class/base.class.php";
require_once "class/connect.class.php";
require_once "class/time.class.php";
require_once "class/usuario.class.php";
require_once "class/grupo.class.php";
require_once "class/peso.class.php";

require_once "class/tematica.class.php";
require_once "class/debate.class.php";

require_once "class/badger.class.php";
require_once "class/code.class.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !empty($_GET['ajax_win']) ? true : false;
if (is_null($using_remote_functions) && !$ajax_win) 
    include "_header.interface.inc";
?>

<?php
class Tinterface extends TplanningInterface {
    private $id_asistencia;
    private $id_asistencia_code;

    public function __construct($clink= null) {
        $this->clink= $clink;
        TplanningInterface::__construct($clink);

        $this->id_evento= !empty($_POST['id_evento']) ? $_POST['id_evento'] : $_GET['id_evento'];
    }

    public function apply() {
        $this->obj= new Tdebate($this->clink);
        $this->id= ($this->action == 'update' || $this->action == 'delete') ? $_POST['id_debate'] : null;

        if (!empty($this->id)) {
            $this->obj->Set($this->id);
            $this->id_code= $this->obj->get_id_code();
            $this->id_tematica= $this->obj->GetIdTematica();
            $this->id_tematica_code= $this->obj->get_id_tematica_code();
        }

        if ($this->action == 'add' || $this->action == 'update') {
            $this->obj->SetObservacion(trim($_POST['observacion']));
            $this->obj->SetHora(ampm2odbc($_POST['time']));

            $this->id_asistencia= $_POST['id_asistencia'];
            $this->obj->SetIdAsistencia($this->id_asistencia);
            $this->id_asistencia_code= get_code_from_table('tasistencias', $this->id_asistencia, $this->clink);
            $this->obj->set_id_asistencia_code($this->id_asistencia_code);

            $this->id_tematica= $_POST['id_tematica'];
            $this->obj->SetIdTematica($this->id_tematica);
            $this->id_tematica_code= get_code_from_table('ttematicas', $this->id_tematica, $this->clink);
            $this->obj->set_id_tematica_code($this->id_tematica_code);

            $this->obj->SetIdUsuario($_SESSION['id_usuario']);
            $this->obj->SetIdProceso($this->id_proceso);
            $this->obj->set_id_proceso_code($this->id_proceso_code);
        }

        if ($this->action == 'add')
            $this->error= $this->obj->add();

        if ($this->action == 'update')
            $this->error= $this->obj->update();

        if ($this->action == 'delete') {
            $observacion= "No.".$this->obj->GetNoTematica()."  ".$this->obj->GetTematica();

            $this->error= $this->obj->eliminar();

            if (is_null($this->error)) {
                $this->obj_code->SetObservacion($observacion);
                $this->obj_code->reg_delete('tdebates','id_code',$this->id_code, 'id_tematica', $this->id_tematica_code);
            }
        }

        if (is_null($_SESSION['debug']) || $_SESSION['debug'] == 'no') {
            if (is_null($this->error)) {
                if ($this->action == 'update' || $this->action == 'delete') 
                    $this->action= 'add';
            ?>
                    url= "../form/fdebate.php?ifaccords=0&action=<?=$this->action?>&id_evento=<?=$this->id_evento?>&id_proceso=<?=$this->id_proceso?>";
                    url+= "&signal=<?=$this->signal?>";
                //    url+= "&id_tematica=<?=$this->id_tematica?>";
                    self.location.href= url;
            <?php
            } else {
                if ($this->action == 'update' || $this->action == 'delete') 
                    $this->action= 'add';
                ?>
                    url= "../form/fdebate.php?ifaccords=0&action=<?=$this->action?>&id_evento=<?=$this->id_evento?>&id_proceso=<?=$this->id_proceso?>&error=<?=urlencode($this->error)?>";
                    url+= "&signal=<?=$this->signal?>";
                //    url+= "&id_tematica=<?=$this->id_tematica?>";
                    self.location.href= url;
            <?php
            }
    }	}
}
?>

<?php if (is_null($using_remote_functions)) { ?>
    <?php if (!$ajax_win) { ?>
                </div>
            </body>
        </html>
<?php } else { ?>
    </div>
<?php } ?>

    <script type="text/javascript">
        <?php if (!$ajax_win) { ?>
        $(document).ready(function() {
            setInterval('setChronometer()',1);

            $('#body-log table').mouseover(function() {
                _moveScroll= false;
            });
            $('#body-log table').mouseout(function() {
                _moveScroll= true;
            });
        <?php } ?>

            <?php
            $interface= new Tinterface($clink);
            $interface->apply();
            ?>

        <?php if (!$ajax_win) { ?>
        });
        <?php } ?>
    </script>
<?php } ?>