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

require_once "class/tmp_tables_planning.class.php";
require_once "class/register_planning.class.php";
require_once "class/evento.class.php";
require_once "class/asistencia.class.php";

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
    private $obj_event;

    public function __construct($clink= null) {
        $this->clink= $clink;
        TplanningInterface::__construct($clink);

        $this->id_evento= !empty($_POST['id_evento']) ? $_POST['id_evento'] : $_GET['id_evento'];
    }

    private function setUsers() {
        $cant= $_POST['t_cant_tab_user'];
        if (empty($cant)) return;

        $ausente= null;
        $invitado= null;
        $id_usuario= null;

        for ($i= 1; $i <= $cant; $i++) {
            $id_usuario= $_POST['tab_user_'.$i];
            if ($id_usuario == 0 && !empty($_POST['id_assist_'.$i])) continue;

            $this->obj->SetIdUsuario($id_usuario);
            $this->obj->SetNumero(null);
            $this->obj->SetCargo(null);
            $this->obj->SetEntidad(null);

            $ausente= $_POST['ausente_'.$i] ? 1 : 0;
            $this->obj->SetIfAusente($ausente);

            $invitado= $_POST['invitado_'.$i] ? 1 : 0;
            $this->obj->SetIfInvitado($invitado);

            $this->obj->SetIdEvento($this->id_evento);
            $this->obj->set_id_evento_code($this->id_evento_code);
            $this->obj->SetIdProceso($this->id_proceso);
            $this->obj->set_id_proceso_code($this->id_proceso_code);

            if (!empty($_POST['id_assist_'.$i])) $this->obj->update($_POST['id_assist_'.$i]);
            else $this->obj->add();

            $this->obj_event->SetIdUsuario($id_usuario);

            if ($ausente) {
                $this->obj_event->SetCumplimiento(_INCUMPLIDO);
                $this->obj_event->SetObservacion("AUSENTE a la reunión");
            } else {
                $this->obj_event->SetCumplimiento(_COMPLETADO);
                $this->obj_event->SetObservacion("ASISTIO a la reunión");
            }

            $obj_register= new Tregister_planning($this->clink);
            $this->obj_event->copy_in_object($obj_register);
            $obj_register->add_cump();
        }
    }

    private function setGuest() {
        $cant= $_POST['cant_guest'];
        if (empty($cant)) return;

        for ($i= 1; $i <= $cant; $i++) {
            if ($_POST['tab_guest_'.$i] == 1 && !empty($_POST['id_assist_guest_'.$i])) continue;

            if (empty($_POST['id_assist_guest_'.$i]) || (!empty($_POST['id_assist_guest_'.$i]) && $_POST['tab_guest_'.$i])) {
                if (empty($_POST['nombre_'.$i])) continue;
                if (empty($_POST['cargo_'.$i])) continue;
                if (empty($_POST['entidad_'.$i])) continue;

                $this->obj->SetIdUsuario(null);
                $this->obj->SetNombre($_POST['nombre_'.$i], false);
                $this->obj->SetCargo($_POST['cargo_'.$i]);
                $this->obj->SetEntidad($_POST['entidad_'.$i]);
                $this->obj->SetIfAusente(0);
                $this->obj->SetIfInvitado(1);
                $this->obj->SetIdEvento($this->id_evento);
                $this->obj->set_id_evento_code($this->id_evento_code);
                $this->obj->SetIdProceso($this->id_proceso);
                $this->obj->set_id_proceso_code($this->id_proceso_code);
            }

            if (!empty($_POST['id_assist_guest_'.$i]) && $_POST['tab_guest_'.$i] == 2) {
                $this->obj->update($_POST['id_assist_guest_'.$i]);
            }
            if (empty($_POST['id_assist_guest_'.$i])) {
                $this->obj->add();
            }
            if (!empty($_POST['id_assist_guest_'.$i]) && $_POST['tab_guest_'.$i] == 0) {
                $this->obj->eliminar($_POST['id_assist_guest_'.$i]);
            }
        }
    }

    public function apply() {
        $this->obj= new Tasistencia($this->clink);

        $this->id_evento= $_POST['id_evento'];
        $this->id_evento_code= get_code_from_table('teventos',$this->id_evento);

        $this->obj_event= new Tevento($this->clink);
        $this->obj_event->SetIdEvento($this->id_evento);
        $this->obj_event->Set();

        $this->setUsers();
        $this->setGuest();

        if (is_null($this->error)) {
        ?>
                url= "../form/fassist.php?&action=<?=$this->action?>&id_evento=<?=$this->id_evento?>&id_proceso=<?=$this->id_proceso?>";
                self.location.href= url;
        <?php
        } else {
            ?>
            url= "../form/fassist.php?action=<?=$this->action?>&id_evento=<?=$this->id_evento?>&id_proceso=<?=$this->id_proceso?>&error=<?=urlencode($this->error)?>";
            self.location.href= url;
        <?php
        }
    }
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