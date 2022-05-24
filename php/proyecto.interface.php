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
require_once "class/connect.class.php";
require_once "class/usuario.class.php";
require_once "class/grupo.class.php";
require_once "class/proyecto.class.php";
require_once "class/tarea.class.php";
require_once "class/proceso.class.php";
require_once "class/proceso_item.class.php";

require_once "class/kanban.class.php";

require_once "class/code.class.php";
?>

<?php
$ajax_win= !empty($_GET['ajax_win']) ? $_GET['ajax_win'] : false;
$control_page_origen= !empty($_POST['control_page_origen']) ? $_POST['control_page_origen'] : false;
$id_tarea= !empty($_POST['id_tarea']) ? $_POST['id_tarea'] : null;
$signal= !empty($_POST['signal']) ? $_POST['signal'] : null;

if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class Tinterface extends TplanningInterface {
    protected  $inicio;
    protected  $fin;


    public function __construct($clink= null) {
        $this->id_tarea= $_GET['id_tarea'];
        $this->clink= $clink;
        TplanningInterface::__construct($clink);
    }

    protected function set_proceso_from_array() {
        $error= null;

        $tobj= new Tproceso_item($this->clink);
        $tobj->set_cronos($this->cronos);

        $tobj->SetIdProyecto($this->id_proyecto);
        $tobj->set_id_proyecto_code($this->id_proyecto_code);

        $tobj->SetIdPrograma($this->id_programa);
        $tobj->set_id_programa_code($this->id_programa_code);

        $inicio= !empty($this->inicio) ? $this->inicio : $this->year;
        $fin= !empty($this->fin) ? $this->fin : $this->year;

        for ($year= $inicio; $year <= $fin; ++$year) {
            $tobj->SetYear($year);

            reset($this->accept_process_list);
            foreach ($this->accept_process_list as $array) {
                $tobj->SetIdProceso($array['id']);
                $tobj->set_id_proceso_code($array['id_code']);

                if (empty($array['flag']))
                    $error= $tobj->setProyecto($this->action);
                if (!is_null($error))
                    break;
            }

            reset($this->denied_process_list);
            foreach ($this->denied_process_list as $array) {
                if ($array['id'] == $this->id_proceso)
                    continue;

                $tobj->SetIdProceso($array['id']);
                $tobj->set_id_proceso_code($array['id_code']);

                $error= $tobj->SetProyecto('delete');
                if (!is_null($error))
                    break;

                $this->obj_code->reg_delete('tproceso_proyectos', 'id_proyecto_code', $this->id_code, 'id_proceso_code', $array['id_code']);
            }

            if (!array_key_exists($tobj->id_proceso, (array)$this->accept_process_list)) {
                $tobj->SetIdProceso($this->id_proceso);
                $tobj->set_id_proceso_code($this->id_proceso_code);
                $tobj->setProyecto();
            }
        }

        unset($tobj);
    }

    private function set_tareas() {
        $error= null;
        $obj_task= new Ttarea($this->clink);
        $obj_task->SetIdUsuario($_SESSION['id_usuario']);

        if ($this->action == 'add' || $this->action == 'update') {
            $result= $obj_task->listar(false, false);

            while ($row= $this->clink->fetch_array($result)) {
                $chk= 'chk_task_'.$row['_id'];

                if (empty($_POST[$chk]))
                    continue;

                $this->obj->SetIdTarea($row['_id']);
                $this->obj->set_id_tarea_code($row['_id_code']);
                $error= $this->obj->add_tarea();

                if (!is_null($error))
                    break;
            }
        }

        if ($this->action == 'delete') {
            $obj_task->SetIdTarea($this->id_tarea);
            $obj_task->Set();
            $id_code= $obj_task->get_id_code();

            $error= $this->obj->delete_tarea($this->id_tarea);
            $observacion= "Proyecto: Código: ".$this->obj->GetCodigo(). " ".$this->obj->GetFechaInicioPlan(). " - ". $this->obj->GetFechaFinPlan(). " ".$this->obj->GetNombre();
            $observacion.= "<br />Tarea: ".$obj_task->GetNombre(). " Inicio: ".$obj_task->GetFechaInicioPlan(). " Fin: ".$obj_task->GetFechaFinPlan();

            if (is_null($error)) {
                $this->obj_code->SetObservacion($observacion);
                $this->obj_code->reg_delete('triesgo_tareas', 'id_riesgo_code', $this->id_code, 'id_tarea_code', $id_code);
            }
        }

        unset($obj_task);
        $this->action= 'edit';

        return $error;
    }

    private function register_proyecto() {
        $this->observacion= $this->obj->GetDescripcion();

        $obj_tarea= new Ttarea($this->clink);
        $obj_tarea->SetIdProyecto($this->id);

        $observacion= $this->observacion."<br/><br/>CANCELADO<br/>".trim($_POST['descripcion']);
        $this->obj->SetDescripcion($observacion);

        $fecha_final= trim($_POST['fecha_final']);
        $this->obj->SetFechaInicioReal($fecha_final);

        $obj_tarea->set_fin_by_proyecto();

        unset($obj_tarea);
    }

    private function set_kanban() {
        $obj_kan= new Tkanban($this->clink);
        $obj_kan->SetIdProyecto($this->id_proyecto);
        $obj_kan->set_id_proyecto_code($this->id_proyecto_code);
        $obj_kan->SetIdResponsable(null);
        
        $obj_kan->set_new_proyecto();
    }

    private function delete() {
        $error= null;
        $ifDelete_task= !empty($_GET['ifDelete_task']) ? $_GET['ifDelete_task'] : 0;
        $observacion= "Código: ".$this->obj->GetCodigo(). " ".$this->obj->GetFechaInicioPlan();
        $observacion.= " - ". $this->obj->GetFechaFinPlan(). " ".$this->obj->GetNombre();

        $error= $this->obj->eliminar();
        if (is_null($error)) {
            $this->obj_code->SetObservacion($observacion);
            $this->obj_code->reg_delete('tproyectos', 'id_code', $this->id_code);
        }

        return $error;
    }

    public function apply_init() {
        global $ajax_win;
        global $control_page_origen;

        $this->obj= new Tproyecto($this->clink);

        if ($this->action != 'add' && !empty($this->id)) {
            $this->id_proyecto= $this->id;
            $this->obj->SetIdProyecto($this->id);
            $this->obj->Set();
            $this->id_code= $this->obj->get_id_code();
            $this->id_proyecto_code= $this->id_code;
        }

        $this->obj->set_cronos($this->cronos);
        $this->obj->action= $this->action;

        if ($this->menu == 'proyecto') {
            if ($this->action == 'add' || $this->action == 'update') {
                $this->id_responsable= $_POST['responsable'];
                $this->obj->SetIdResponsable($this->id_responsable);

                $this->obj->SetIdUsuario($_SESSION['id_usuario']);

                $this->fecha_inicio= date2odbc($_POST['fecha_inicio']);
                $this->fecha_fin= date2odbc($_POST['fecha_fin']);
                $this->obj->SetFechaInicioPlan($this->fecha_inicio);
                $this->obj->SetFechaFinPlan($this->fecha_fin);

                $this->inicio= date('Y', strtotime($this->fecha_inicio));
                $this->fin= date('Y', strtotime($this->fecha_fin));
                $this->year= date('Y');

                $this->obj->SetCodigo(trim($_POST['codigo']));
                $this->obj->SetNombre(trim($_POST['nombre']), false);
                $this->obj->SetDescripcion(trim($_POST['descripcion']));

                $this->id_programa= $_POST['programa'];
                $this->id_programa_code= $_POST['programa_code_'.$this->id_programa];
                $this->obj->SetIdPrograma($this->id_programa);
                $this->obj->set_id_programa_code($this->id_programa_code);
            }
            
            if ($this->action == 'add') {
                $this->id_proceso= $_SESSION['id_entity'];
                $this->id_proceso_code= $_SESSION['id_entity_code'];
                $this->obj->SetIdProceso($this->id_proceso);
                $this->obj->set_id_proceso_code($this->id_proceso_code);
            }
        }
        
        if (!$ajax_win && $control_page_origen) {
            require_once('_body.interface.inc');
        }           
    } 

    public function apply() {
        global $control_page_origen; 
        global $ajax_win;       
        global $id_tarea;

        if ($this->menu == 'proyecto') {
            if ($this->action == 'add') {
                $error= $this->obj->add();

                if (is_null($error)) {
                    $this->id= $this->obj->GetId();
                    $this->id_proyecto= $this->id;
                    $this->id_code= $this->obj->get_id_code();
                    $this->id_proyecto_code= $this->id_code;
                }
            }

            if ($this->action == 'add' && is_null($this->error)) {
                $this->set_kanban();
            }

            if ($this->action == 'update') {
                $error= $this->obj->update();
            }

            if (($this->action == 'add' || $this->action == 'update') && is_null($error))  {
                $this->set_reg_table('tusuario_proyectos');
                $this->setUsuarios();
                $this->setGrupos();
                $this->setProcesos(false);
                $this->set_proceso_from_array();
            }

            if ($this->action == 'delete') {
                $this->delete();
            }
        }


        if ($this->menu == 'add_tarea') {
            $error= $this->set_tareas();
        }

        if ($this->action == 'edit' || $this->action == 'list') {
            $error = $this->obj->Set();
        }

        if ($this->action == 'add' && $this->menu == 'regproyecto') {
            $this->register_proyecto();
        }


        if ($this->menu == 'gantt') {

        }

        if ($this->menu != 'add_tarea') {
            $action= !$ajax_win && $control_page_origen ? "edit" : $this->action;

            $url_page= "../php/proyecto.interface.php?id=$this->id&signal=$this->signal&action=$action&menu=$this->menu";
            $url_page.= "&exect=$this->action&id_proceso=$this->id_proceso&year=$this->year&month=$this->month&day=$this->day";
            $url_page.= "&id_programa=$this->id_programa";

            add_page($url_page, $this->action, 'i');
        }

        unset($_SESSION['obj']);
        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($error)) {
                $_SESSION['obj']= serialize($this->obj);

                if ($this->menu == 'proyecto' || $this->menu == 'tablero') {
                    if (($this->action == 'add' || $this->action == 'update') && !empty($control_page_origen)) {
                        if ($control_page_origen == 'add') {
                        ?>  
                            $('#id_proyecto').val(<?=$this->id_proyecto?>);
                            add_tarea();
                        <?php
                        }
                        if ($control_page_origen == 'edit') {
                        ?>  
                            $('#id_proyecto').val(<?=$this->id_proyecto?>);
                            editar_tarea(<?=$id_tarea?>);
                    <?php        
                        }
                    }
    
                    if ((($this->action == 'add' || $this->action == 'update') && empty($control_page_origen)) || $this->action == 'delete') {
                        ?>
                        self.location.href = '<?php next_page(); ?>';
                        <?php
                    }

                    if (($this->action == 'edit' || ($this->action == 'add' && empty($control_page_origen))) 
                                                                                || $this->action == 'list') {
                        if ($this->action == 'edit' || $this->action == 'add')
                            $this->action = 'update';
                        ?>
                        self.location.href = '../form/fproyecto.php?action=<?= $this->action ?>&signal=<?= $this->signal#$this->id?>';
                        <?php
                        }
                }  else {
                ?>
                    <?php if ($this->menu == 'tarea' || $this->menu == 'add_tarea') { ?>
                        cerrar();
                    <?php } else { ?>
                        self.location.reload();
                    <?php } ?>

                <?php
                }
            } else {
                $this->obj->error= $error;
                $this->obj->signal= $this->signal;
                $_SESSION['obj']= serialize($this->obj);
            ?>
                self.location.href='<?php prev_page($error);?>';

            <?php
            }
        }
    }
}

?>

<?php
$interface = new Tinterface($clink);
$interface->apply_init();
?>

        </div>

<?php if (!$ajax_win) { ?>
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
        $interface->apply();
        ?>
    <?php if (!$ajax_win) { ?> 
    }); 
    <?php } ?>
</script>
