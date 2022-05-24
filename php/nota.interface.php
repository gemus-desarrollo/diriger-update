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
require_once "class/time.class.php";
require_once "class/proceso_item.class.php";

require_once "class/evento.class.php";
require_once "class/tarea.class.php";
require_once "class/nota.class.php";
require_once "class/lista_requisito.class.php";

require_once "class/code.class.php";
?>

<?php
global $using_remote_functions;

$ajax_win= !empty($_GET['ajax_win']) ? $_GET['ajax_win'] : false;
$control_page_origen= !empty($_POST['control_page_origen']) ? $_POST['control_page_origen'] : false;
$id_tarea= !empty($_POST['id_tarea']) ? $_POST['id_tarea'] : null;
$signal= !empty($_POST['signal']) ? $_POST['signal'] : null;

if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class Tinterface extends TplanningInterface {
    protected $id_proceso_item,
              $id_proceso_item_code;

    public function __construct($clink= null) {
        $this->id_tarea = $_GET['id_tarea'];
        $this->clink = $clink;
        TplanningInterface::__construct($clink);
    }

    protected function setProcesos() {
        $error = null;
        $array = array();

        $tobj = new Tproceso_item($this->clink);
        $result = $tobj->listar();

        $tobj->SetYear($this->year);
        $tobj->SetIdNota($this->id_nota);
        $tobj->set_id_nota_code($this->id_nota_code);

        $i= 0;
        while ($row = $this->clink->fetch_array($result)) {
            $value = $_POST['multiselect-prs_' . $row['_id']];
            $_value = $_POST['multiselect-prs_init_' . $row['_id']];

            $tobj->SetIdProceso($row['_id']);
            $tobj->set_id_proceso_code($row['_id_code']);

            if (!empty($value)) {
                if (empty($_value))
                    $error = $tobj->setRiesgo('add');
                $array[] = $row['_id'];
                ++$i;
            } else {
                if (!empty($_value)) {
                    $error = $tobj->setRiesgo('delete');
                    $this->obj_code->reg_delete('tproceso_riesgos', 'id_nota_code', $this->id_code, 'id_proceso_code', $row['_id_code']);
            }   }

            if (!is_null($error))
                break;
        }

        if ($i == 0 && array_search($this->id_proceso, $array) === false) {
            $tobj->SetIdProceso($this->id_proceso);
            $tobj->set_id_proceso_code($this->id_proceso_code);
            $tobj->setRiesgo('add');
        }

        unset($tobj);
    }

    private function _delete_prs() {
        global $Ttipo_proceso_array;

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

        $obj_prs->SetIdNota($this->id_nota);

        foreach ($this->array_procesos as $prs) {
            $obj_prs->SetIdProceso($prs['id']);
            $obj_prs->set_id_proceso_code($prs['id_code']);
            $obj_prs->setRiesgo('delete');

            $observacion= $this->obj->GetDescripcion(). " <br />".$this->obj->GetFechaInicioReal();
            $observacion.= "<br />Proceso: {$Ttipo_proceso_array[$prs['tipo']]}, {$prs['nombre']}";
            $this->obj_code->SetObservacion($observacion);

            $this->obj_code->reg_delete('tproceso_riesgos', 'id_proceso_code', $prs['id_code'], 'id_nota_code', $this->id_nota_code);
        }

        $obj_prs->GetProcesosRiesgo();
        $cant= $obj_prs->GetCantidad();

        if (empty($cant)) {
            $observacion= $this->obj->GetDescripcion(). " <br />".$this->obj->GetFechaInicioReal();
            $error = $this->obj->eliminar();

            if (is_null($error)) {
                $this->obj_code->SetObservacion($observacion);
                $this->obj_code->reg_delete('tnotas', 'id_code', $this->id_code);
        }   }        
    }

    private function _delete() {
        $this->_delete_prs();
        $cant_prs= $this->obj->if_empty_riesgo_nota();
        if (empty($cant_prs))
            $this->obj->eliminar();
    }

    private function set_tareas() {
        $obj_task = new Ttarea($this->clink);
        $obj_task->SetIdUsuario($_SESSION['id_usuario']);

        if ($this->action == 'add' || $this->action == 'update') {
            $result = $obj_task->listar(0);

            while ($row = $this->clink->fetch_array($result)) {
                $chk = 'chk_task_' . $row['_id'];
                if (empty($_POST[$chk]))
                    continue;

                $this->obj->SetIdTarea($row['_id']);
                $this->obj->set_id_tarea_code($row['_id_code']);
                $error = $this->obj->add_tarea();

                if (!is_null($error))
                    break;
            }
        }

        if ($this->action == 'delete') {
            $obj_task->SetIdTarea($this->id_tarea);
            $obj_task->Set();
            $id_code = $obj_task->get_id_code();

            $error = $this->obj->delete_tarea($this->id_tarea);

            if (is_null($error))
                $this->obj_code->reg_delete('triesgo_tareas', 'id_nota_code', $this->id_code, 'id_tarea_code', $id_code);
        }

        unset($obj_task);
        $this->action = 'edit';

        return $error;
    }

    public function register_requisito() {
        $obj= new Tlista_requisito($this->clink);
        $obj->SetIdRequisito($this->id_requisito);
        $obj->Set();

        $obj->SetIdProceso($this->id_proceso);
        $obj->set_id_proceso_code($this->id_proceso_code);

        $obj->SetIdAuditoria($this->id_auditoria);
        $obj->set_id_auditoria_code($this->id_auditoria_code);  

        $obj->SetIdNota($this->id_nota);
        $obj->set_id_nota_code($this->id_nota_code);         

        $rowcmp= $obj->getLista_reg();

        $obj->setEstado(!is_null($rowcmp['estado']) ? $rowcmp['estado'] : _IDENTIFICADO);
        $obj->SetCumplimiento($_POST['cumplimiento']);
        $obj->SetFecha($rowcmp['reg_fecha']);
        $obj->SetObservacion($rowcmp['observacion']);
        $obj->SetIdUsuario($_SESSION['id_usuario']);

        $obj->setLista_reg();
    }

    private function set_estado() {
        $error = null;

        $this->obj->SetIdUsuario($_SESSION['id_usuario']);
        $this->obj->setEstado(_IDENTIFICADO);
        $this->obj->SetObservacion("Registrada el hallazgo");
        $this->obj->SetFecha($this->cronos);

        $tobj = new Tproceso_item($this->clink);
        $result = $tobj->listar();

        $tobj->SetYear($this->year);
        $tobj->SetIdNota($this->id_nota);
        $tobj->set_id_nota_code($this->id_nota_code);

        $array_prs= array();
        while ($row = $this->clink->fetch_array($result)) {
            $array_prs[$row['_id']]= $row['_id'];

            $value = $_POST['multiselect-prs_' . $row['_id']];
            $_value = $_POST['multiselect-prs_init_' . $row['_id']];

            $this->obj->SetIdProceso($row['_id']);
            $this->obj->set_id_proceso_code($row['_id_code']);

            if (!empty($value) && empty($_value))
                $this->obj->set_estado();

            if (!is_null($error))
                break;
        }

        $this->obj->SetIdProceso($this->id_proceso);
        $this->obj->set_id_proceso_code($this->id_proceso_code);        
        
        if (array_search($this->id_proceso, $array_prs) === false) {
            $this->obj->set_estado();
        }

        unset($tobj);
    }

    private function set_estado_cascade() {
        $this->radio_prs= !empty($_POST['_radio_prs']) ? true : false;
        $this->id_proceso_item= !empty($_POST['id_proceso_item']) ? $_POST['id_proceso_item'] : $this->id_proceso;
        $this->id_proceso_item_code= !empty($_POST['id_proceso_item_code']) ? $_POST['id_proceso_item_code'] : $this->id_proceso_code;

        $array= array('id'=> $this->id_proceso_item, 'id_code'=> $this->id_proceso_item_code);

        if ($this->radio_prs) {
            $obj_prs= new Tproceso($this->clink);
            $obj_prs->SetIdProceso($this->id_proceso_item);
            $obj_prs->Set();

            $array_prs= $obj_prs->get_procesos_down($this->id_proceso_item, null, null, true);
        }
        if (count($array_prs) == 0)
            $array_prs[]= $array;

        foreach ($array_prs as $array) {
            $this->obj->SetIdProceso($array['id']);
            $this->obj->set_id_proceso_code($array['id_code']);

            $this->obj->SetObservacion(trim($_POST['descripcion']));
            $this->obj->setEstado($_POST['estado']);
            $this->obj->SetFecha(date2odbc(trim($_POST['fecha'])));

            $this->obj->set_estado();
        }
    }

    public function apply_init() {
        global $Tcumplimiento_array;
        global $ajax_win;
        global $control_page_origen;
        
        $this->obj = new Tnota($this->clink);

        if (!empty($this->id)) {
            $this->obj->SetIdNota($this->id);
            $this->obj->Set();
            $this->id_code = $this->obj->get_id_code();
            $this->id_nota = $this->id;
            $this->id_nota_code = $this->id_code;
        }

        $this->obj->set_cronos($this->cronos);
        $this->obj->action = $this->action;
        $this->obj->SetIdUsuario($_SESSION['id_usuario']);

        if ($this->action == 'add' || $this->action == 'update') {
            $this->id_auditoria = $_POST['id_auditoria'];
            $this->obj->SetIdAuditoria($this->id_auditoria);
            $this->id_auditoria_code = $_POST['id_auditoria_code'];
            $this->obj->set_id_auditoria_code($this->id_auditoria_code);

            $this->obj->SetLugar(trim($_POST['lugar']));
            $this->descripcion= trim($_POST['descripcion']);

            $this->obj->SetTipo($_POST['tipo']);
            $this->obj->SetOrigen($_POST['origen']);

            $this->fecha_inicio= $_POST['fecha_inicio'];
            $this->obj->SetFechaInicioReal(date2odbc($this->fecha_inicio));
            $this->fecha_fin= $_POST['fecha_fin'];
            $this->obj->SetFechaFinPlan(date2odbc($this->fecha_fin));

            $this->obj->SetObservacion(trim($_POST['observacion']));
            $this->obj->SetObservacion_ma(trim($_POST['observacion_ma']));
            $this->obj->SetObservacion_sst(trim($_POST['observacion_sst']));

            $this->obj->SetIfRequisito_leg($_POST['req_leg']);
            $this->obj->SetIfRequisito_proc($_POST['req_proc']);
            $this->obj->SetIfRequisito_reg($_POST['req_reg']);

            $this->obj->SetNorma(fullUpper(trim($_POST['norma'])));
            $this->obj->SetRequisito(trim($_POST['requisito']));

            $this->id_lista= $_POST['id_lista'];
            $this->obj->SetIdLista($this->id_lista);
            $this->id_lista_code= $_POST['id_lista_code'];
            $this->obj->set_id_lista_code($this->id_lista_code);

            $this->id_requisito= $_POST['id_requisito'];
            $this->obj->SetIdRequisito($this->id_requisito);
            $this->id_requisito_code= $_POST['id_requisito_code'];
            if (!empty($this->id_requisito) && empty($this->id_requisito_code))
                $this->id_requisito_code= get_code_from_table ('tlista_requisitos', $this->id_requisito);
            $this->obj->set_id_requisito_code($this->id_requisito_code);

            $this->cumplimiento= $_POST['cumplimiento'];
            $this->obj->SetCumplimiento($this->cumplimiento);

            if (empty($this->id_requisito)) {
                $this->descripcion= trim($_POST['descripcion']);
            } else {
                $this->descripcion= $Tcumplimiento_array[$this->cumplimiento].":".trim($_POST['requisito_text']);
            }
            $this->obj->SetDescripcion($this->descripcion);
        }
            
        if ($this->action == 'add' || $this->action == 'update' || $this->action == 'register') {
            $this->id_proceso = !empty($_POST['proceso']) ? $_POST['proceso'] : $_GET['id_proceso'];

            $this->obj->SetIdProceso($this->id_proceso);
            $this->id_proceso_code = get_code_from_table('tprocesos', $this->id_proceso);
            $this->obj->set_id_proceso_code($this->id_proceso_code);
        }

        if (!$ajax_win && $control_page_origen) {
            require_once('_body.interface.inc');
        }        
    }

    public function apply() {
        global $control_page_origen; 
        global $ajax_win;       
        global $id_tarea;

        if ($this->menu == 'nota') {
            if ($this->action == 'add') {
                $error = $this->obj->add();

                if (is_null($error)) {
                    $this->id = $this->obj->GetIdNota();
                    $this->id_nota = $this->id;

                    $this->id_code = $this->obj->get_id_code();
                    $this->id_nota_code = $this->id_code;
                }
            }

            if ($this->action == 'update')
                $error = $this->obj->update();

            if (is_null($error) && ($this->action == 'add' || $this->action == 'update')) {
                $this->set_estado();
                $this->setProcesos();

                if (!empty($this->id_requisito))
                    $this->register_requisito();
            }
        }

        if ($this->menu == 'nota' || $this->menu == 'tablero') {
            if ($this->action == 'edit' || $this->action == 'list')
                $error = $this->obj->Set();

            if ($this->action == 'delete')
                $this->_delete();
        }

        if ($this->action == 'register' && $this->menu == 'nota_update')
            $this->set_estado_cascade();

        if ($this->menu == 'add_tarea' || $this->menu == 'tarea')
            $error= $this->set_tareas();

        if ($this->menu != 'add_tarea') {
            $action= !$ajax_win && $control_page_origen ? "edit" : $this->action;

            $url_page = "../php/nota.interface.php?id=$this->id&signal=$this->signal&action=$action&menu=$this->menu";
            $url_page .= "&exect=$this->action&id_proceso=$this->id_proceso&year=$this->year&month=$this->month&day=$this->day";
            $url_page .= "&tipo=$this->tipo";

            add_page($url_page, $this->action, 'i');
        }

        if (is_null($error)) {
            if ($this->menu == 'nota' || $this->menu == 'tablero') {                
                unset($_SESSION['obj']);
                $_SESSION['obj'] = serialize($this->obj);
                
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
                    if ($this->action == 'edit' || $this->action == 'add')
                        $this->action = 'update';
                    ?>
                        self.location.href = '../form/fnota.php?action=<?= $this->action ?>&signal=<?= $this->signal#$this->id?>';
                    <?php
                }
            } else {
                $obj_tmp = unserialize($_SESSION['obj']);
                $obj_tmp->action = $this->obj->action;
                $obj_tmp->error = $this->obj->error;
                $obj_tmp->signal = $this->obj->signal;

                unset($_SESSION['obj']);
                $_SESSION['obj'] = serialize($obj_tmp);
                ?>
                    <?php if ($this->menu == 'tarea' || $this->menu == 'add_tarea') { ?>
                        cerrar();
                    <?php } else { ?>
                        self.location.reload();
                    <?php } ?>

                <?php
            }
        } else {
            $this->obj->error = $error;
            $this->obj->signal = $this->signal;
            $_SESSION['obj'] = serialize($this->obj);
            ?>

            <?php if ($this->menu == 'tarea' || $this->menu == 'add_tarea') { ?>
                    cerrar("<?= $error?>");
            <?php } else { ?>
                    self.location.href = '<?php prev_page($error); ?>';
            <?php } ?>

            <?php
        }
    }
}
?>

<?php
$interface = new Tinterface($clink);
$interface->apply_init();
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
        $interface->apply();
        ?>

        <?php if (!$ajax_win) { ?>
        });
        <?php } ?>
    </script>
<?php } ?>