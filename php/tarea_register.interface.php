<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
include_once "setup.ini.php";
require_once "class/config.class.php";

require_once "config.inc.php";

require_once "interface.class.php";

require_once "class/base.class.php";
require_once "class/connect.class.php";
require_once "class/time.class.php";
require_once "class/usuario.class.php";
require_once "class/grupo.class.php";
require_once "class/peso.class.php";

require_once "class/evento.class.php";
require_once "class/regtarea.class.php";
require_once "class/kanban.class.php";

require_once "class/mail.class.php";
require_once "class/code.class.php";

require_once "register.interface.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !empty($_GET['ajax_win']) ? true : false;
if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class Tinterface extends TRegister {
    private $reg_fecha;

    private $year_init,
            $year_fin;

    protected $radio_date;

    public function __construct($clink= null) {
        $this->clink = $clink;
        TRegister::__construct($clink);

        $this->id_tarea = !empty($_POST['id_tarea']) ? $_POST['id_tarea'] : $_GET['id_tarea'];
        $this->id_evento = !empty($_POST['id_evento']) ? $_POST['id_evento'] : $_GET['id_evento'];

        $this->className= "Ttarea";
    }

    private function _set_evento_user($id_evento, $year, $value, $id_calendar) {
        $obj_reg= new Tregister_planning($this->clink);

        $obj_reg->SetIdUsuario($id_calendar);
        $obj_reg->SetYear($year);
        $obj_reg->SetIdEvento($id_evento);
        $obj_reg->SetIdTarea($this->id_tarea);

        $row = $obj_reg->get_last_reg();

        if (empty($row))
            return null;
        if ($row['cumplimiento'] == _CUMPLIDA || $row['cumplimiento'] == _SUSPENDIDO 
            || $row['cumplimiento'] == _POSPUESTO || $row['cumplimiento'] == _DELEGADO)
            return null;

        $obj_reg->SetCumplimiento($value);
        $obj_reg->SetRechazado(null);
        $obj_reg->compute = 1;
        $obj_reg->toshow = boolean($row['_toshow']);
        $obj_reg->set_user_check(boolean($row['_user_check']));
        $obj_reg->SetObservacion($this->observacion);

        $obj_reg->SetIdResponsable($_SESSION['id_usuario']);

        $error= $obj_reg->add_cump();
        return $error;
    }

    private function _set_evento_prs($id_evento, $year, $value, $id_proceso) {
        $obj_reg= new Tregister_planning($this->clink);

        $obj_reg->SetIdProceso($id_proceso);

        $obj_reg->SetYear($year);
        $obj_reg->SetIdEvento($id_evento);
        $obj_reg->SetIdTarea($this->id_tarea);
        $row = $obj_reg->get_reg_proceso();

        if (empty($row))
            return null;
        if($row['cumplimiento'] == _CUMPLIDA || $row['cumplimiento'] == _SUSPENDIDO 
            || $row['cumplimiento'] == _POSPUESTO || $row['cumplimiento'] == _DELEGADO)
            return null;

        $obj_reg->SetCumplimiento($value);
        $obj_reg->SetRechazado(null);
        $obj_reg->compute = 1;
        $obj_reg->toshow = boolean($row['_toshow']);
        $obj_reg->SetObservacion($this->observacion);

        $obj_reg->SetIdUsuario($_SESSION['id_usuario']);

        $error= $obj_reg->add_cump_proceso();
        return $error;
    }

    private function set_evento() {
        $this->cumplimiento = !empty($_POST['cumplimiento']) ? $_POST['cumplimiento'] : $_GET['cumplimiento'];

        reset($this->array_eventos);
        foreach ($this->array_eventos as $event) {
            $value= $this->cumplimiento;
            $year= date('Y', strtotime($event['fecha_inicio']));

            if(strtotime($event['fecha_inicio']) < strtotime($this->reg_fecha))
                continue;
            if((strtotime($event['fecha_inicio']) > strtotime($this->reg_fecha)) && !$this->radio_date)
                continue;

            $this->init_register_usuarios();
            $this->init_register_procesos();

            $error= $this->_set_evento_user($event['id'], $year, $value, $this->id_responsable);

            if($this->radio_user) {
                foreach ($this->array_reg_usuarios as $id_user => $user) {
                    if($this->id_responsable == $id_user)
                        continue;
                    $error= $this->_set_evento_user($event['id'], $year, $value, $id_user);
                    if (!empty($error))
                        return $error;
            }   }

            if($this->radio_prs) {
                foreach ($this->array_reg_procesos as $id_prs => $prs) {
                    $error= $this->_set_evento_prs($event['id'], $year, $value, $id_prs);
                    if (!empty($error))
                        return $error;
            }   }
        }
        return null;
    }

    private function set_tarea() {
        $value = !empty($_POST['real']) ? $_POST['real'] : null;
        $fecha_final = null;
        $this->obj->SetFecha($this->reg_fecha);

        if (!empty($_POST['ended'])) {
            $fecha_final = trim($_POST['fecha_final']);
            $this->obj->SetFechaFinReal(date2odbc($fecha_final));
            $this->cumplimiento = ($value >= 100) ? _COMPLETADO : _CANCELADO;
        }

        $this->obj->SetPlanning(0);
        if (!empty($value));
            $this->obj->SetCumplimiento($value);
        $this->obj->SetIdUsuario($_SESSION['id_usuario']);

        $this->obj->SetIdKanbanColumn($this->id_kanaban_column);
        $this->obj->set_id_kanbanColumn_code($this->id_kanaban_column_code);
        
        $this->obj->add_cump_to_task();

        $this->obj->set_dependecies_status();
    }

    // dependencias entre las tareas
    private function set_depend() {
        $cant_source_rows= $_POST['cant_source_rows'];

        for ($i= 1; $i <= $cant_source_rows; $i++) {
            $_id= $_POST['init_source_'.$i];
            $id= $_POST['source_'.$i];
            $id_code= get_code_from_table('ttareas', $id);
            $tipo= $_POST['tipo_depend_'.$i];

            if (empty($id)) {
                if (empty($_id))
                    continue;
                else
                    $this->obj->setDependencies($_id, $id_code, $tipo, 'delete');
            } else {
                if (empty($_id))
                    $this->obj->setDependencies($id, $id_code, $tipo, 'add');
                else
                    $this->obj->setDependencies($id, $id_code, $tipo, 'update', $_id);
            }
        }
    }

    private function set_grupo() {
        $id_tarea_grupo= !empty($_POST['id_grupo']) ? $_POST['id_grupo'] : null;
        $this->obj->SetIdTarea_grupo($id_tarea_grupo);
        $id_tarea_grupo_code= !empty($id_tarea_grupo) ? get_code_from_table("ttareas", $id_tarea_grupo) : null;
        $this->obj->Set_id_tarea_grupo_code($id_tarea_grupo_code);

        $this->error= $this->obj->update();
    }

    // Identificacion de los hitos en la planificacion de la tarea
    private function set_hits() {
        $this->obj->SetPlanning(1);

        $cant= $_POST['cant_hits'];
        for ($i= 1; $i <= $cant; ++$i) {
            $id_code= $_POST['init_hit_'.$i];
            $fecha= $_POST['fecha_'.$i];

            if (empty($fecha) || strlen($fecha) == 0) {
                if (!empty($id_code)) {
                    $this->obj->del_reg($id_code);
                    $this->obj_code->reg_delete('treg_tarea', 'id_code', $id_code, 'planning', 1);
                }
                continue;
            }

            $this->obj->SetFecha(date2odbc($fecha));
            $this->obj->SetCumplimiento($_POST['real_'.$i]);
            $this->obj->SetObservacion(trim($_POST['observacion_'.$i]));

            $error= $this->obj->add_cump_to_task($id_code);
            if (!is_null($error))
                break;
        }

        $this->error= $error;
    }

    private function get_kanban() {
        $obj_kan= new Tkanban($this->clink);
        
        if (!empty($this->id_proyecto)) {
            $obj_kan->SetIdResponsable(null);
            $obj_kan->SetIdProyecto($this->id_proyecto);
            $obj_kan->set_id_proyecto_code($this->id_proyecto_code);
        } else {
            $obj_kan->SetIdResponsable($this->id_responsable);
            $obj_kan->SetIdProyecto(null);
            $obj_kan->set_id_proyecto_code(null);            
        }

        $kanban_column= $obj_kan->listar(false, true);
        $this->id_kanaban_column= $kanban_column['id'];
        $this->id_kanaban_column_code= $kanban_column['id_code'];
    }

    public function apply() {
        $this->obj = new Tregtarea($this->clink);
        $this->obj->Set($this->id_tarea);
        
        $this->id_tarea= $this->obj->GetId();
        $this->id_tarea_code= $this->obj->get_id_code();

        $this->id_responsable= $this->obj->GetIdResponsable();
        $this->id_proyecto= $this->obj->GetIdProyecto();
        $this->id_proyecto_code= $this->get_id_proyecto_code();

        $fecha_inicio_plan= $this->obj->GetFechaInicioPlan();
        $fecha_fin_plan= $this->obj->GetFechaFinPlan();

        $this->year_init= date('Y', strtotime($fecha_inicio_plan));
        $this->year_fin= date('Y', strtotime($fecha_fin_plan));

        $this->get_kanban();

        $this->obj_event= new Tevento($this->clink);
        $this->obj_event->SetIdTarea($this->id_tarea);
        $this->obj_event->get_eventos_by_tarea($this->id_tarea, array($this->year_init, $this->year_fin));
        $this->array_eventos= $this->obj_event->array_eventos;

        if(!empty($this->id_evento)) {
            $this->obj_event->SetIdEvento($this->id_evento);
            $this->obj_event->Set();
            $this->reg_fecha= $this->obj_event->GetFechaInicioPlan();
        }

        $this->id_calendar= !empty($_POST['id_calendar']) ? $_POST['id_calendar'] : null;
        $this->observacion= !empty($_POST['observacion']) ? trim($_POST['observacion']) : null;

        $this->obj->SetIdUsuario($this->id_calendar);

        $this->radio_prs = 1;
        $this->radio_date = !empty($this->id_calendar) ? $_POST['_radio_date'] : 0;
        $this->radio_user = !empty($this->id_calendar) ? $_POST['_radio_user'] : 1;
   
        $this->obj->action = $this->action;

        if ($this->menu == 'regtarea') {
            $this->set_evento();
            $this->set_tarea();
        }
 
        if ($this->menu == 'tarea_hito') {
            $this->set_hits();
        }

        if ($this->menu == 'tarea_depend') {
            $this->set_depend();
        }

        if ($this->menu == 'fdelete') {
            $ref_code = true;
            $id = null;
            $id_code = null;

            $date = $this->year . '-' . str_pad($this->month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($this->day, 2, '0', STR_PAD_LEFT);

            if ($this->radio_date) {
                $id = $this->obj->get_id_evento_ref();
                $id_code = $this->obj->get_id_evento_ref_code();
            }

            if (empty($id)) {
                $id = $this->id;
                $id_code = $this->id_code;
                $ref_code = false;
            }

            $id_calendar = empty($this->radio_user) ? $this->id_calendar : null;
            $this->obj->delete_reg($date, $id, $id_calendar, $this->radio_date, $ref_code);
        }

        if (is_null($this->error)) {
            $this->redirect = 'ok';
            ?>

            <script language='javascript' type="text/javascript" charset="utf-8">
            <?php if ($this->menu == 'tarea_hito' || $this->menu == 'tarea_depend') { ?>
                closep();
            <?php } else { ?>
                cerrar();
            <?php } ?>
            </script>

        <?php } else { ?>

            <script language='javascript' type="text/javascript" charset="utf-8">
                alert("<?=$this->error?>", function(ok) {
                    <?php if ($this->menu == 'tarea_hito' || $this->menu == 'tarea_depend') { ?>
                        closep();
                    <?php } else { ?>
                        cerrar();
                    <?php } ?>
                });
            </script>

            <?php
        }
    }

}

$interface = new Tinterface($clink);
$interface->apply();
?>