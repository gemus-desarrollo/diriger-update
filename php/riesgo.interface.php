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
require_once "class/peso.class.php";
require_once "class/proceso_item.class.php";

require_once "class/tarea.class.php";
require_once "class/riesgo.class.php";
require_once "class/time.class.php";
require_once "class/schedule.class.php";

require_once "class/code.class.php";
?>

<?php
global $using_remote_functions;

$ajax_win= !empty($_GET['ajax_win']) ? $_GET['ajax_win'] : false;
$control_page_origen= !empty($_POST['control_page_origen']) ? $_POST['control_page_origen'] : false;
$id_tarea= !empty($_POST['id_tarea']) ? $_POST['id_tarea'] : null;
$signal= !empty($_POST['signal']) ? $_POST['signal'] : null;

if (is_null($using_remote_functions) && !$ajax_win)
    require_once "_header.interface.inc";
?>

<?php
class Tinterface extends TplanningInterface {
    protected $radio_prs;
    protected $radio_date;

    private $frecuencia;
    private $impacto;
    private $deteccion;

    private $_frecuencia;
    private $_impacto;
    private $_deteccion;

    private $to_year;
    private $copy_all;
    private $nums_id_show;
    private $array_id_show;
    private $array_procesos_entity;

    public function __construct($clink= null) {
        $this->id_tarea= $_GET['id_tarea'];
        $this->clink= $clink;
        TplanningInterface::__construct($clink);

        $this->radio_prs= $_POST['_radio_prs'];
        $this->radio_date= $_POST['_radio_date'];
        $this->radio_user= $_POST['_radio_user'];

        $this->to_year= $_POST['to_year'];
        $this->copy_all= $_POST['copy_all'];
        $this->nums_id_show= $_POST['nums_id_show'];
        $this->array_id_show= $_POST['array_id_show'];

        $this->array_id_show= !empty($this->nums_id_show) ? explode(',', $this->array_id_show) : null;
    }

    private function init_entity() {
        $this->array_usuarios_entity= array();
        $this->array_procesos_entity= array();

        $obj_prs= new Tproceso($this->clink);
        $obj_prs->SetYear($this->year);
        $obj_prs->listar_procesos_entity();
        $this->array_procesos_entity= $obj_prs->array_procesos_entity;
    }
    
    protected function set_proceso_from_array() {
        $error= null;
        $this->setProcesos(false);

        $tobj= new Tproceso_item($this->clink);
        $tobj->SetYear($this->year);
        $tobj->SetIdRiesgo($this->id);
        $tobj->set_id_riesgo_code($this->id_code);

        reset($this->accept_process_list);
        foreach ($this->accept_process_list as $array) {
            $tobj->SetIdProceso($array['id']);
            $tobj->set_id_proceso_code($array['id_code']);

            if (empty($array['flag'])) {
                $error= $tobj->setRiesgo('add');
            }
            if (!is_null($error)) {
                break;
            }
        }

        reset($this->denied_process_list);
        foreach ($this->denied_process_list as $array) {
            if ($array['id'] == $this->id_proceso) {
                continue;
            }

            $tobj->SetIdProceso($array['id']);
            $tobj->set_id_proceso_code($array['id_code']);

            $error= $tobj->SetRiesgo('delete');
            if (!is_null($error)) {
                break;
            }

            $this->obj_code->reg_delete('tproceso_riesgos', 'id_riesgo_code', $this->id_code, 'id_proceso_code', $array['id_code']);
        }

        if (!array_key_exists($tobj->id_proceso, (array)$this->accept_process_list)) {
            $tobj->SetIdProceso($this->id_proceso);
            $tobj->set_id_proceso_code($this->id_proceso_code);
            $tobj->setRiesgo();
        }

        unset($tobj);
    }

    protected function set_estado() {
        reset($this->accept_process_list);
        reset($this->denied_process_list);

        $this->obj->SetIdUsuario($_SESSION['id_usuario']);
        $date= date('Y', strtotime($this->cronos)) > $this->year ? $this->year."-12-31" : $this->cronos;
        $this->obj->SetFecha($date);
        $this->obj->setEstado(_IDENTIFICADO);

        foreach ($this->accept_process_list as $array) {
            $this->obj->SetIdProceso($array['id']);
            $this->obj->set_id_proceso_code($array['id_code']);
            $this->obj->set_estado();
        }
        $this->obj->SetIdProceso($this->id_proceso);
        $this->obj->set_id_proceso_code($this->id_proceso_code);
    }

    protected function set_peso() {
        $tobj= new Tinductor($this->clink);
        $result= $tobj->listar();

        $obj_peso= new Tpeso($this->clink);

        $obj_peso->SetIdRiesgo($this->id);
        $obj_peso->set_id_riesgo_code($this->id_code);

        $obj_peso->set_cronos($this->cronos);

        while ($row= $this->clink->fetch_array($result)) {
            $value= $_POST['select_objt'.$row['_id']];
            $_value= $_POST['init_objt'.$row['_id']];

            if ($value > 0) {
                if (!empty($_value)) {
                    if ($_value != $value) {
                        $obj_peso->update_riesgo_ref_inductor($row['_id'], $row['_id_code'], $value, 'update');
                    }
                } else {
                    $obj_peso->update_riesgo_ref_inductor($row['_id'], $row['_id_code'], $value, 'insert');
                }
            } elseif (!empty($_POST['init_objt'.$row['_id']])) {
                $obj_peso->delete_evento_ref_inductor($row['_id']);
            }
        }

        unset($obj_peso);
        unset($tobj);
    }

    private function _delete() {
        $radio_prs= !is_null($_POST['_radio_prs']) ? $_POST['_radio_prs'] : 0;

        $obj_prs= new Tproceso_item($this->clink);
        $obj_prs->SetIdProceso($this->id_proceso);

        if ($radio_prs == 2) {
            $this->array_procesos= $obj_prs->get_procesos_down($this->id_proceso, null, null, true);
        } else {
            $obj_prs->Set($this->id_proceso);
            $array= array('id'=>$this->id_proceso, 'id_code'=>$obj_prs->get_id_code(), 'nombre'=>$obj_prs->GetNombre(),
                    'tipo'=>$obj_prs->GetTipo(), 'lugar'=>$obj_prs->GetLugar(), 'descripcion'=>$obj_prs->GetDescripcion(),
                    'id_responsable'=>$obj_prs->GetIdResponsable(), 'conectado'=>$obj_prs->GetConectado(), 'codigo'=>$obj_prs->GetCodigo(),
                    'id_proceso'=>$obj_prs->GetIdProceso_sup());

            $this->array_procesos[$this->id_proceso]= $array;
        }

        $obj_prs->SetIdRiesgo($this->id_riesgo);

        foreach ($this->array_procesos as $prs) {
            $obj_prs->SetIdProceso($prs['id']);
            $obj_prs->set_id_proceso_code($prs['id_code']);
            $obj_prs->setRiesgo('delete');

            $observacion= $this->obj->GetDescripcion(). " <br />".$this->obj->GetFechaInicioReal();
            $observacion.= "<br />Proceso: {$Ttipo_proceso_array[$prs['tipo']]}, {$prs['nombre']}";
            $this->obj_code->SetObservacion($observacion);

            $this->obj_code->reg_delete('tproceso_riesgos', 'id_proceso_code', $prs['id_code'], 'id_riesgo_code', $this->id_riesgo_code);
        }

        $obj_prs->GetProcesosRiesgo();
        $cant= $obj_prs->GetCantidad();

        if (empty($cant)) {
            $observacion= $this->obj->GetNombre(). " <br />".$this->obj->GetFechaInicioPlan(). " - ". $this->obj->GetFechaFinPlan();
            $error = $this->obj->eliminar();

            if (is_null($error)) {
                $this->obj_code->SetObservacion($observacion);
                $this->obj_code->reg_delete('triesgos', 'id_code', $this->id_code);
            }
        }
    }

    public function apply_init() {
        global $ajax_win;
        global $signal;
        global $control_page_origen;

        $this->obj= new Triesgo($this->clink);

        if ($this->action != 'add' && !empty($this->id)) {
            $this->obj->SetIdRiesgo($this->id);
            $this->obj->Set();
            $this->id_code= $this->obj->get_id_code();

            $this->id_riesgo= $this->id;
            $this->id_riesgo_code= $this->id_code;

            $this->_frecuencia= $this->obj->getFrecuencia();
            $this->_impacto= $this->obj->getImpacto();
            $this->_deteccion= $this->obj->getDeteccion();
        }

        if ($this->action !== 'copy') {
            $this->obj->set_cronos($this->cronos);
            $this->obj->action= $this->action;

            $this->obj->SetIdUsuario($_SESSION['id_usuario']);
            $this->obj->SetYear($this->year);
            $this->obj->SetMonth($this->month);
        }

        if ($this->action == 'add' || $this->action == 'update') {
            $this->obj->SetNombre(trim($_POST['nombre']), false);
            $this->obj->SetDescripcion(trim($_POST['descripcion']));

            $this->obj->setFrecuencia_memo(trim($_POST['frecuencia_memo']));
            $this->obj->setImpacto_memo(trim($_POST['impacto_memo']));
            $this->obj->setDeteccion_memo(trim($_POST['deteccion_memo']));

            $this->obj->SetIfEstrategico($_POST['ifestrategico']);
            $this->obj->SetIfExterno($_POST['origen']);
            $this->obj->SetIfSST($_POST['sst']);
            $this->obj->SetIfMedioAmbiental($_POST['ma']);
            $this->obj->SetIfEconomico($_POST['econ']);
            $this->obj->SetIfRegulatorio($_POST['reg']);
            $this->obj->SetIfInformatico($_POST['info']);
            $this->obj->SetIfCalidad($_POST['calidad']);
            $this->obj->SetValue(trim($_POST['valor']));

            $this->obj->SetLugar(trim($_POST['lugar']));
            
            $this->fecha_inicio= $_POST['fecha_inicio'];
            $this->obj->SetFechaInicioPlan($this->fecha_inicio);
            $this->fecha_fin= $_POST['fecha_fin'];
            $this->obj->SetFechaFinPlan($this->fecha_fin);

            $this->obj->SetIdResponsable($_SESSION['id_usuario']);
        } else {
            $this->init_entity();
            $this->radio_prs= $_POST['_radio_prs'];
            $this->radio_date= $_POST['_radio_date'];

            $this->id_responsable = $_SESSION['id_usuario'];
            $this->id_proceso = $_POST['id_proceso'];
            $this->id_proceso_code = $_POST['id_proceso_code'];
            $this->tipo = $_POST['tipo'];
        }

        if ($this->action == 'add' || $this->action == 'update' || $this->action == 'register') {
            $this->id_proceso= !empty($_POST['proceso']) ? $_POST['proceso'] : $_GET['id_proceso'];

            $this->frecuencia= $_POST['frecuencia'];
            $this->impacto= $_POST['impacto'];
            $this->deteccion= $_POST['deteccion'];
            $this->obj->setFrecuencia($this->frecuencia);
            $this->obj->setImpacto($this->impacto);
            $this->obj->setDeteccion($this->deteccion);

            $this->obj->SetIdProceso($this->id_proceso);
            $this->id_proceso_code= get_code_from_table('tprocesos', $this->id_proceso);
            $this->obj->set_id_proceso_code($this->id_proceso_code);
        }

        if ($this->action != 'copy') {
            $this->obj->SetIdRiesgo($this->id);
        }

        if (!$ajax_win && $control_page_origen) {
            require_once('_body.interface.inc');
        }
    }

    public function apply() {
        global $control_page_origen;
        global $ajax_win;
        global $id_tarea;

        if ($this->menu == 'riesgo' || $this->menu =='tablero') {
            if ($this->action == 'add') {
                $error= $this->obj->add();

                if (is_null($error)) {
                    $this->id= $this->obj->GetIdRiesgo();
                    $this->id_code= $this->obj->get_id_code();

                    $this->id_riesgo= $this->id;
                    $this->id_riesgo_code= $this->id_code;
                }
            }
            if ($this->action == 'update') {
                $error= $this->obj->update();

                if (is_null($error)) {
                    if (($this->frecuencia != $this->_frecuencia) || ($this->impacto != $this->_impacto) || ($this->deteccion != $this->_deteccion)) {
                        $this->obj->riesgo_reg_clean();
                    }
                }
            }

            if ($this->action == 'add' || $this->action == 'update') {
                $this->set_proceso_from_array();
                $this->set_estado();
                $this->set_peso();
            }

            if ($this->action == 'delete') {
                $this->_delete();
            }

            if ($this->action == 'edit' || $this->action == 'list') {
                $error= $this->obj->Set();
            }
        }

        if ($this->menu == 'fcopy') {
            if ($this->action == 'repro') {
                $if_synchronized= $this->if_synchronize($this->obj->GetIdProceso()) ? true : false ;
                $array= $this->obj->if_exists_copyto($this->to_year);
                $if_entity= array_key_exists($this->obj->GetIdProceso(), $this->array_procesos_entity) ? true : false;
                
                if (!$if_synchronized && $if_entity) {
                    $error= $this->obj->this_copy($this->id_proceso, $this->id_proceso_code, $this->tipo, $this->radio_prs, $this->to_year, $array);
                }
            }

            if ($this->action == 'copy') {
                foreach ($this->array_id_show as $id) {
                    if (isset($this->obj)) {
                        unset($this->obj);
                    }
                    $this->obj= new Triesgo($this->clink);
                    $this->obj->SetIdRiesgo($id);
                    $this->obj->Set();

                    $if_synchronized= $this->if_synchronize($this->obj->GetIdProceso()) ? true : false ;
                    $array= $this->obj->if_exists_copyto($this->to_year);
                    $if_entity= array_key_exists($this->obj->GetIdProceso(), $this->array_procesos_entity) ? true : false;
                    
                    if ($if_synchronized || !$if_entity) {
                        continue;
                    }

                    $this->obj->set_cronos($this->cronos);
                    $this->obj->action= $this->action;
                    $this->obj->SetIdUsuario($_SESSION['id_usuario']);
                    $this->obj->SetYear($this->year);
                    $this->obj->SetMonth($this->month);

                    $this->obj->this_copy($this->id_proceso, $this->id_proceso_code, $this->tipo, $this->radio_prs, $this->to_year, $array);
                }
            }
        }

        if ($this->action == 'register' && $this->menu == 'riesgo_update') {
            $this->obj->SetObservacion(trim($_POST['descripcion']));
            $this->obj->setEstado($_POST['estado']);
            $this->obj->SetFecha(date2odbc(trim($_POST['fecha'])));
            $this->obj->set_estado();
        }

        if ($this->menu == 'add_tarea' || $this->menu == 'tarea') {
            $obj_task= new Ttarea($this->clink);
            $obj_task->SetIdUsuario($_SESSION['id_usuario']);

            if ($this->action == 'add' || $this->action == 'update') {
                $result= $obj_task->listar(false, false);

                while ($row= $this->clink->fetch_array($result)) {
                    $chk= 'chk_task_'.$row['_id'];
                    if (empty($_POST[$chk])) {
                        continue;
                    }

                    $this->obj->SetIdTarea($row['_id']);
                    $this->obj->set_id_tarea_code($row['_id_code']);
                    $error= $this->obj->add_tarea();

                    if (!is_null($error)) {
                        break;
                    }
                }
            }

            if ($this->action == 'delete') {
                $obj_task->SetIdTarea($this->id_tarea);
                $obj_task->Set();
                $id_code= $obj_task->get_id_code();

                $error= $this->obj->delete_tarea($this->id_tarea);

                if (is_null($error)) {
                    $this->obj_code->reg_delete('triesgo_tareas', 'id_riesgo_code', $this->id_code, 'id_tarea_code', $id_code);
                }
            }

            unset($obj_task);
            $this->action= 'edit';
        }

        if ($this->menu != 'add_tarea') {
            $action= !$ajax_win && $control_page_origen ? "edit" : $this->action;

            $url_page= "../php/riesgo.interface.php?id=$this->id&signal=$this->signal&action=$action&menu=$this->menu";
            $url_page.= "&exect=$this->action&id_proceso=$this->id_proceso&year=$this->year&month=$this->month&day=$this->day";

            add_page($url_page, $this->action, 'i');
        }

        unset($_SESSION['obj']);

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($error)) {
                $_SESSION['obj']= serialize($this->obj);

                if ($this->menu == 'riesgo' || $this->menu =='tablero') {
                    if (($this->action == 'add' || $this->action == 'update') && !empty($control_page_origen)) {
                        if ($control_page_origen == 'add') {
                            ?>
                            add_tarea();
                        <?php
                        }
                        if ($control_page_origen == 'edit') {
                            ?>    
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
                        if ($this->action == 'edit' || $this->action == 'add') {
                            $this->action = 'update';
                        } ?>
                            self.location.href = '../form/friesgo.php?action=<?= $this->action ?>&signal=<?= $this->signal#$this->id?>';
                        <?php
                    }
                } else {
                    $this->obj->error= $error;
                    $this->obj->signal= $this->signal;
                    $_SESSION['obj']= serialize($this->obj); ?>
                    self.location.href='<?php prev_page($error); ?>';
                <?php
                }
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