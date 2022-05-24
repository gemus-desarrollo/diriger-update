<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

set_time_limit(0);
session_cache_expire(720);

session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";


require_once "config.inc.php";
require_once "class/base.class.php";
require_once "class/connect.class.php";
require_once "class/usuario.class.php";
require_once "class/mail.class.php";
require_once "class/proceso_item.class.php";

require_once "class/plantrab.class.php";
require_once "class/orgtarea.class.php";

require_once "class/badger.class.php";

require_once "class/code.class.php";

$_SESSION['debug']= 'no';
$_SESSION['trace_time']= 'no';
?>

<?php
global $using_remote_functions;
$ajax_win= true;
if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class Tinterface extends Tplantrab {
    public $menu;
    private $obj;
    private $xvalue,
            $_xvalue;

    public function __construct($clink= null) {
        global $badger;

        $this->clink= $clink;
        Tplantrab::__construct($clink);

        $this->id= !empty($_GET['id']) ? $_GET['id'] : $_POST['id'];
        $this->id_code= !empty($_GET['id_code']) ? $_GET['id_code'] : $_POST['id_code'];
        $this->id_plan= $this->id;

        $this->action= $_POST['exect'];
        $this->menu= $_POST['menu'];
        $this->year= $_POST['year'];

        $this->print_reject= !is_null($_POST['print_reject']) ? $_POST['print_reject'] : $_GET['print_reject'];
        $this->tipo_plan= !empty($_POST['tipo_plan']) ? $_POST['tipo_plan'] : $_GET['tipo_plan'];
        $this->clink= $clink;

        if (!is_null($_SESSION['obj_plantrab']))
            $this->obj= unserialize($_SESSION['obj_plantrab']);
        else
            $this->obj= new Tplantrab($this->clink);

        $this->id_proceso= !empty($_POST['id_proceso']) ? $_POST['id_proceso'] : $_GET['id_proceso'];

        $this->obj->if_copy_tusuarios= $badger->if_copy_tusuarios;
        $this->obj->SetIdPlan($this->id);
        $this->obj->SetYear($this->year);
        $this->obj->SetTipoPlan($this->tipo_plan);
        $this->obj->set_id_plan_code($this->id_code);
        $this->obj->set_print_reject($this->print_reject);
        $this->obj->SetLink($this->clink);

        $this->obj_code= new Tcode($this->clink);
    }

    private function mail_alert($observcion= null) {
        global $evaluacion_array;

        $obj_mail= new Tmail();
        $obj_user= new Tusuario($this->clink);

        $user= $obj_user->GetEmail($this->id_usuario);
        $obj_mail->usr_target= $user['nombre'].' ('.$user['cargo'].')';

        $user= $obj_user->GetEmail($_SESSION['id_usuario']);
        $obj_mail->responsable= $user['nombre'].' ('.$user['cargo'].')';

        $value= $evaluacion_array[$this->xvalue];
        $_value= $evaluacion_array[$this->_xvalue];

        $obj_mail->body_plan_evalute($value, $_value, $observcion);

        $obj= new Torgtarea($this->clink);
        $obj->listar_chief($_SESSION['id_usuario']);

        foreach ($obj->array_jefes as $jefe)
            $obj_mail->AddAddress($jefe['mail'], $jefe['nombre'].' ('.$jefe['cargo'].')');

        $obj_mail->Send();
    }

    public function apply() {
        global $badger;
        $this->obj->action= $this->action;

        $if_jefe= $_POST['if_jefe'];

        $this->id_proceso= $_POST['id_proceso'];
        $this->id_proceso_code= $_POST['id_proceso_code'];

        if (empty($this->id_proceso)) {
            $this->id_proceso= $_SESSION['id_entity'];
            $this->id_proceso_code= $_SESSION['id_entity_code'];
        }

        $this->obj->SetIdProceso($this->id_proceso);
        $this->obj->set_id_proceso_code($this->id_proceso_code);

        $toshow= (int)$_POST['empresarial'];

        $this->month= ($toshow == 2) ? null : $_POST['month'];
        $this->obj->SetMonth($this->month);

        if (!empty($this->id)) {
            $this->obj->Set($this->id);
            $_objetivos= $this->obj->GetObjetivo();

            $this->id_code= $this->obj->get_id_code();
            $this->id_plan_code= $this->id_code;
            $this->aprobado= $this->obj->GetAprobado();
            $this->evaluado= $this->obj->GetEvaluado();
        }

        $this->xvalue= $_POST['cumplimiento'];
        $this->obj->SetCumplimiento($this->xvalue);

        $this->id_usuario= !empty($_POST['id_usuario']) ? $_POST['id_usuario'] : null;
        $this->obj->SetIdUsuario($this->id_usuario);
        $this->obj->SetIdResponsable($_SESSION['id_usuario']);

        $evaluacion= trim($_POST['evaluacion']);
        $this->obj->SetEvaluacion($evaluacion);

        $_evaluacion= $_POST['_evaluacion'];

        $auto_evaluacion= trim($_POST['auto_evaluacion']);
        $_auto_evaluacion= $_POST['_auto_evaluacion'];
        $this->obj->SetAutoEvaluacion($auto_evaluacion);

        $this->obj->SetIfEmpresarial($toshow);
        $this->obj->set_cronos(date('Y-m-d H:i:s'));

        $error= null;

        $this->obj->set_use_copy_tusuarios(true);
        $this->obj->if_copy_tusuarios= $badger->if_copy_tusuarios;
        $this->tipo_plan == _PLAN_TIPO_MEETING ? $this->obj->SetIdTipo_reunion(0) : $this->obj->SetIdTipo_reunion(null);
        $objetivo= trim($_POST['objetivos']);

        if ($this->action == 'aprove' || $this->action == 'object') {
            $this->obj->SetObjetivo($objetivo);
        }

        $radio_user= $_POST['_radio_user'];

        if ($this->action == 'eval' || $this->action == 'auto_eval' || $this->action == 'aprove') {
            $this->obj->SetIfEmpresarial(null);
            $this->obj->create_temporary_treg_evento_table= false;

            if ($this->action == 'eval' || $this->action == 'auto_eval')
                ($this->tipo_plan != _PLAN_TIPO_ACTIVIDADES_ANUAL) ? $this->obj->list_reg($toshow) : $this->obj->list_reg_anual();
            if ($this->action == 'aprove') {
                $this->obj->automatic_event_status($toshow);
            }
        }
        
        $this->obj->SetIdProceso($this->id_proceso);
        $this->obj->set_id_proceso_code($this->id_proceso_code);
        
        if ($this->action == 'aprove') {
            $corte= ($toshow == 2) ? 'all_year' : 'all_month';

            if (!empty($this->evaluado) && !$if_jefe) {
                $error= "No se puede aprobar un plan que ya ha sido evaluado. Su intento de re-aprobación no procede, al menos que sea el ";
                $error.= !empty($toshow) ? "ADMINISTRADOR o SUPERUSUARIO del sistema o el Jefe del proceso." : "el jefe del usuario.";
            }

            if (is_null($error)) {
                $this->obj->SetIdResponsable_aprb($_SESSION['id_usuario']);
                $this->obj->updateAprove($corte, true, $radio_user ? true : false);
                $this->debug_time('updateAprove');

                if ($radio_user && is_null($error)) {
                    $this->obj->SetObjetivo($objetivo);
                    $this->obj->update_objetive_to_users();
        }   }   }

        $this->debug_time('automatic_event_status');

        $error= null;

        if ($this->action == 'eval') {
            $observacion= null;

            if (empty($this->aprobado)) {
                 $error= "No se puede evaluar un Plan de Trabajo que aún no ha sido aprobado. ";
                 $error.= "Primero deberá aprobar el Plan.";
            }
            if (strcasecmp($_evaluacion, $evaluacion) == 0 && is_null($error)) {
                $error= "Este Plan de Trabajo ya había sido evaluado, con las mismas observaciones. ";
                $error.= "Su intento de reevaluación no procede.";
            }
            if (is_null($error)) {
                $obj_user= new Tusuario($this->clink);
                $array_user= $obj_user->GetEmail($_SESSION['id_usuario']);
                $nombre= $array_user['nombre'].'('.$array_user['cargo'].')'.' en fecha '.date('d/m/Y H:i');

                 if (!empty($_POST['_observacion'])) 
                     $observacion= trim($_POST['_observacion'])."\n\n$nombre:\n";
                 $this->obj->SetEvaluacion($evaluacion);

                $error= $this->obj->updateEval();
            }

            if (is_null($error)) {
                $email= false;
                if (!empty($_POST['_cumplimiento']) && $_POST['_cumplimiento'] != $_POST['cumplimiento'])
                    $email= true; $this->_xvalue= $_POST['_cumplimiento'];
                if (!empty($_POST['cumplimiento']) && $_POST['cumplimiento'] != $_POST['_value'])
                    $email= true; $this->_xvalue= $_POST['_value'];
                if ($email)
                    $this->mail_alert($observacion);
            }
        }

        if ($this->action == 'auto_eval') {
            $observacion= null;

            if (empty($this->aprobado))
                $error= "No se puede evaluar un Plan de Trabajo que aún no ha sido aprobado. Primero deberá aprobar el Plan.";
            if (strcasecmp($_auto_evaluacion, $auto_evaluacion) == 0 && is_null($error)) {
                $error= "Este Plan de Trabajo ya había sido autoevaluado, con las mismas observaciones. ";
                $error.= "Su intento de reevaluación no procede.";
            }
            if (is_null($error))
                $error= $this->obj->updateAutoEval();
        }

        if ($this->action == 'object') {
            if (strcasecmp($_objetivo, $objetivo) == 0)
                $error= "Este Plan ya tenia las mismas tareas principales asignadas. Su intento de re-asignar no procede.";

            if (is_null($error)) {
                $error= $this->obj->updateObjective();

                if ($radio_user && is_null($error)) {
                    $this->obj->SetObjetivo($objetivo);
                    $this->obj->update_objetive_to_users();
        }   }   }

        ?>
        parent.app_menu_functions= true;
        
        <?php
        if (is_null($error)) {
            $this->obj->redirect= 'ok';

            if ($this->action != 'object') {
        ?>
                self.location.reload();
        <?php  } else { ?>
                CloseWindow('div-ajax-panel');
        <?php
            }

        } else {
            $this->obj->redirect= 'fail';
            $this->obj->error= $error;
            ?>

            alert("<?=$error?>", function(ok) {
                if (ok)
                    CloseWindow('div-ajax-panel');
            });
    <?php
        }
    }
}
?>

    </div>

<?php if (!$ajax_win) { ?>
    </body>
</html>
<?php } else { ?>
</div>
<?php } ?>

<script type="text/javascript">
    _SERVER_DIRIGER= '<?= addslashes(_SERVER_DIRIGER)?>';

    <?php if (!$ajax_win) { ?>
        $(document).ready(function() {
            // setInterval('setChronometer()',1);

            $('#body-log table').mouseover(function() {
                _moveScroll= false;
            });
            $('#body-log table').mouseout(function() {
                _moveScroll= true;
            });
        <?php } ?>

        <?php
        /**
         * configuracion de usuarios y procesos segun las proiedades del usuario
         */

        $_SESSION['in_javascript_block']= true;
        global $config;
        global $badger;

        $interface= new Tinterface($clink);

        $badger= new Tbadger();
        $badger->SetLink($clink);
        $badger->SetYear($interface->GetYear());
        $badger->set_user_date_ref();
        $badger->set_tusuarios();

        $interface->apply();
        ?>
    <?php if (!$ajax_win) { ?>
    });
    <?php } ?>
</script>