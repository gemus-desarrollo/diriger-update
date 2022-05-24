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
require_once "class/riesgo.class.php";
require_once "class/plan_ci.class.php";

require_once "class/badger.class.php";

require_once "class/code.class.php";

$_SESSION['trace_time']= 'no';
?>

<?php
$ajax_win= true;
include "_header.interface.inc";
?>

<?php
class Tplan_interface extends Tbase {
    private $obj;
    private $tipo_plan;


    public function Tplan_interface($clink) {
        $this->clink= $clink;
        $this->id_usuario= $_SESSION['id_usuario'];

        $this->year= !empty($_GET['year']) ? $_GET['year'] : $_POST['_year'];
        $this->month= !empty($_GET['month']) ? $_GET['month'] : $_POST['_month'];
       // $this->day= !empty($_GET['day']) ? $_GET['day'] : $_POST['day'];

        $this->action= !empty($_GET['action']) ? $_GET['action'] : $_POST['exect'];
        $this->menu= !empty($_GET['menu']) ? $_GET['menu'] : $_POST['menu'];
        $this->signal= !empty($_GET['signal']) ? $_GET['signal'] : $_POST['signal'];

        $this->id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_POST['id_proceso'];

        $this->tipo_plan= !empty($_GET['tipo_plan']) ? $_GET['tipo_plan'] : $_POST['tipo_plan'];
        $this->id= !empty($_GET['id']) ? $_GET['id'] : $_POST['id'];
        $this->id_code= !empty($_GET['id_code']) ? $_GET['id_code'] : $_POST['id_code'];

        $this->obj= new Tplan_ci($this->clink);
        $this->obj->set_cronos(date('Y-m-d H:i:s'));
        $this->obj->SetYear($this->year);
        $this->obj->SetMonth($this->month);
        $this->obj->SetTipoPlan($this->tipo_plan);
    }

    public function apply() {
        $this->obj->action= $this->action;
        $this->obj->SetYear($this->year);

        if (!empty($this->id)) {
            $this->obj->SetIdPlan($this->id);
            $this->obj->Set();

            $this->id_plan= $this->id;
            $this->id_code= $this->obj->get_id_code();
            $this->id_plan_code= $this->id_code;
            $this->aprobado= $this->obj->GetAprobado();
            $this->evaluado= $this->obj->GetEvaluado();

            $_objetivo= $this->obj->GetObjetivo();
        }

        if ($this->tipo == _PLAN_TIPO_AUDITORIA || $this->tipo == _PLAN_TIPO_SUPERVICION) {
            $this->obj->SetIfAuditoria(true);
            $this->obj->create_tmp_teventos_from_("tnotas");
        }

        if ($this->tipo == _PLAN_TIPO_PREVENCION) {
            $this->obj->SetIfAuditoria(false);
            $this->obj->create_tmp_teventos_from_("triesgos");
        }

        $if_jefe= $_POST['if_jefe'];

        $this->obj->SetIdResponsable($_SESSION['id_usuario']);

        $objetivo= trim($_POST['objetivos']);
        $_objetivo= trim($_POST['_objetivos']);

        $evaluacion= trim($_POST['evaluacion']);
        $this->obj->SetEvaluacion($evaluacion);

        $_evaluacion= $_POST['_evaluacion'];

        if ($this->action == 'aprove' || $this->action == 'object') {
            $this->obj->SetObjetivo($objetivo);
        }

        $error= null;
        $radio_user= $_POST['_radio_user'];

        if ($this->action == 'aprove') {
           $this->obj->SetObjetivo($objetivo);

            if (!empty($this->evaluado) && !$if_jefe) {
                $error= "No se puede aprobar un plan que ya ha sido evaluado. Su intento de re-aprobación no procede, al menos que seas un ";
                $error.= "ADMINISTRADOR o SUPERUSUARIO del sistema.";
            }

            if (is_null($error))
                $error= $this->obj->updateAprove();

            if (is_null($error) && $radio_user)
                $this->obj->aprove_task_to_users();
        }

        if ($this->action == 'eval') {
            $this->obj->SetEvaluacion($observacion);
            $this->obj->SetCumplimiento($_POST['cumplimiento']);

            if (empty($this->aprobado)) {
                 $error= "No se puede evaluar un Plan que aún no ha sido aprobado. Primero deberá aprobar el Plan.";
            }
            if (strcasecmp($_objetivo, $objetivo) == 0 && is_null($error)) {
                $error= "Este Plan de Trabajo ya había sido evaluado, con las mismas observaciones. Su intento de reevaluación no procede.";
            }

            if (is_null($error))
                $error= $this->obj->updateEval();
        }

        if (is_null($error)) {
      ?>
            cerrar();
    <?php
        } else {

            $this->obj->redirect= 'fail';
            $this->obj->error= $error;
        ?>
            cerrar("<?=$error?>");
    <?php
        }
    }
}
?>

        <div class="form-group row">
            <div id="progressbar-0" class="progress-block">
                <div id="progressbar-0-alert" class="alert alert-success">
                    Comenzando
                </div>
                <div id="progressbar-0-" class="progress progress-striped active">
                    <div id="progressbar-0-bar" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <span class="sr-only"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div id="progressbar-1" class="progress-block">
                <div id="progressbar-1-alert" class="alert alert-success">
                    Comenzando
                </div>
                <div id="progressbar-1-" class="progress progress-striped active">
                    <div id="progressbar-1-bar" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <span class="sr-only"></span>
                    </div>
                </div>
            </div>
        </div>

    </div>

<?php if (!$ajax_win) { ?>
    </body>
</html>
<?php } ?>

<script type="text/javascript">
    _SERVER_DIRIGER= '<?= addslashes(_SERVER_DIRIGER)?>';

    $(document).ready(function() {
        // setInterval('setChronometer()',1);

        $('#body-log table').mouseover(function() {
            _moveScroll= false;
        });
        $('#body-log table').mouseout(function() {
            _moveScroll= true;
        });


        <?php
        /**
         * configuracion de usuarios y procesos segun las proiedades del usuario
         */

        global $config;
        global $badger;

        $interface= new Tplan_interface($clink);

        $badger= new Tbadger();
        $badger->SetLink($clink);
        $badger->SetYear($interface->GetYear());
        $badger->set_user_date_ref();
        $badger->set_tusuarios();

        $interface->apply();
        ?>
    });
</script>

