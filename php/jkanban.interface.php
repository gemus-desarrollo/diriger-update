<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2021
 */


session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "config.inc.php";
require_once "class/base.class.php";

require_once "class/time.class.php";
require_once "class/connect.class.php";
require_once "class/usuario.class.php";
require_once "class/proyecto.class.php";
require_once "class/evento.class.php";
require_once "class/regtarea.class.php";
require_once "class/kanban.class.php";

require_once "register.interface.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !empty($_GET['ajax_win']) ? $_GET['ajax_win'] : false;
if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class Tinterface extends TRegister {
    private $id_kanban_column;
    protected $task_numero;
    private $kanban_columns_order;

    private $id_kanban_column_origen,
            $numero_source,
            $fixed_origen;
    private $id_kanban_column_target,
            $numero_target,
            $fixed_target;
        
    protected $value,
              $cumplimiento;

    protected $inicio,
              $fin;
    private $class;

    private $obj_kan;
    private $obj_task;
    protected $obj_reg;

    public function __construct($clink= null) {
        Tbase::__construct($clink); 
        $this->clink = $clink;

        $this->action = !empty($_GET['action']) ? $_GET['action'] : 'list';
        
        $this->year= !empty($_GET['year']) ? (int)$_GET['year'] : (int)$_POST['year'];
        $this->month= !empty($_GET['month']) ? (int)$_GET['month'] : (int)$_POST['month'];
        $this->day= !empty($_GET['day']) ? (int)$_GET['day'] : (int)$_POST['day'];


        $this->id_proyecto = !empty($_GET['id_proyecto']) ? $_GET['id_proyecto'] : $_POST['id_proyecto'];
        if (empty($this->id_proyecto))
            $this->id_proyecto= null;

        $this->id_tarea= !empty($_GET['id_tarea']) ? $_GET['id_tarea'] : $_POST['id_tarea'];
        if (empty($this->id_tarea))
            $this->id_tarea= null;
        
        $this->id_kanban_column= !empty($_GET['id_kanban_column']) ? $_GET['id_kanban_column'] : null;
        $this->id_kanban_column_origen= !empty($_GET['id_kanban_column_origen']) ? $_GET['id_kanban_column_origen'] : $_POST['id_kanban_column_origen'];
        $this->id_kanban_column_target= !empty($_GET['id_kanban_column_target']) ? $_GET['id_kanban_column_target'] : $_POST['id_kanban_column_target'];

        if (empty($this->id_kanban_column_origen))
            $this->id_kanban_column_origen= null;
        if (empty($this->id_kanban_column_target))
            $this->id_kanban_column_target= null;

        $this->task_numero= !empty($_GET['numero']) ? $_GET['numero'] : $_POST['numero'];
        if (empty($this->task_numero))
            $this->task_numero= null;
                  
        $this->kanban_columns_order= !empty($_GET['kanban_columns_order']) ? json_decode(urldecode($_GET['kanban_columns_order'])) : null;
        
        $this->id_tarea_code= !empty($this->id_tarea) ? get_code_from_table("ttareas", $this->id_tarea) : null;
        $this->id_proyecto_code= get_code_from_table("tproyectos", $this->id_proyecto);
    }    

    private function get_columns_status() {
        $obj_kan= new Tkanban($this->clink);
        $obj_kan->SetIdProyecto($this->id_proyecto);
        
        $obj_kan->SetIdKanbanColumn($this->id_kanban_column_origen);
        $obj_kan->Set();
        $this->numero_source= $obj_kan->GetNumero();
        $this->fixed_origen= $obj_kan->get_if_fixed();

        $obj_kan->SetIdKanbanColumn($this->id_kanban_column_target);
        $obj_kan->Set();
        $this->numero_target= $obj_kan->GetNumero();   
        $this->fixed_target= $obj_kan->get_if_fixed(); 
        
        $obj_kan->Set_task();
        $this->chronos_origen= $obj_kan->get_cronos();
    }

    private function _register_tarea() {
        if($this->id_kanban_column_origen == $this->id_kanban_column_target)
            return null;
        if ($this->fixed_origen == $this->fixed_target) 
            return null;

        $this->cumplimiento= null;   
        if($this->fixed_target == _TAREA_TERMINADA) 
            $this->cumplimiento= _COMPLETADO;
        if($this->fixed_target == _TAREA_NO_INICIADA) 
            $this->cumplimiento= _EN_ESPERA;
        if($this->fixed_target != _TAREA_NO_INICIADA && $this->fixed_target != _TAREA_TERMINADA)
            $this->cumplimiento= _EN_PROCESO;

        $this->obj_task->SetFecha(date('Y-m-d'));

        $this->obj_task->SetPlanning(0);
        if (!empty($this->cumplimiento));
            $this->obj_task->SetCumplimiento($this->cumplimiento);
        $this->obj_task->SetIdUsuario($_SESSION['id_usuario']);

        $this->obj_task->add_cump_to_task();
    }

    private function _register_eventos() {
        if($this->id_kanban_column_origen == $this->id_kanban_column_target)
            return null;

        $this->obj_reg= new Tplanning($this->clink);
        $this->obj_reg->SetYear($this->year);
        $this->obj_reg->SetIdTarea($this->id_tarea);
        $this->obj_reg->set_id_tarea_code($this->id_tarea_code);

        $this->obj_reg->SetInicio($this->inicio);
        $this->obj_reg->SetFin($this->fin);
        $this->array_eventos= $this->obj_reg->get_child_events_by_table("ttareas", $this->id_tarea);

        $this->className= "Ttarea";

        if($this->cumplimiento == _EN_ESPERA) 
            $this->value= _NO_INICIADO;
        if($this->cumplimiento == _COMPLETADO) 
            $this->value= _CUMPLIDA;            
        if($this->cumplimiento == _EN_PROCESO) 
            $this->value= _EN_CURSO;

        $this->radio_user= 1;
        $this->radio_prs= 1;
        $this->extend= 'P'; 
        $this->_register();
    }

    private function _order_columns() {
        $this->obj_kan->SetIdProyecto($this->id_proyecto);
        $this->obj_kan->SetIdResponsable($this->id_calendar);

        foreach ($this->kanban_columns_order as $i) {
            $this->obj_kan->SetIdKanbanColumn($this->kanban_columns_order[$i][0]);
            $this->obj_kan->SetNumero($this->kanban_columns_order[$i][1]);
            $this->obj_kan->update(true);
        }
    }

    public function apply() {
        $this->obj_kan= new Tkanban($this->clink);
        $this->obj_kan->SetIdProyecto($this->id_proyecto);
        $this->obj_kan->set_id_proyecto_code($this->id_proyecto_code);
        $this->obj_kan->SetIdTarea($this->id_tarea);
        $this->obj_kan->set_id_tarea_code($this->id_tarea_code);

        $this->obj_task= new Tregtarea($this->clink);;
        $this->obj_task->SetIdProyecto($this->id_proyecto);
        $this->obj_task->set_id_proyecto_code($this->id_proyecto_code);
        $this->obj_task->SetIdTarea($this->id_tarea);
        $this->obj_task->set_id_tarea_code($this->id_tarea_code);

        if(!empty($this->id_kanban_column)) {
            $this->obj_kan->SetIdKanbanColumn($this->id_kanban_column);
            $this->obj_kan->Set();
            $this->numero= $this->obj_kan->GetNumero();
            $this->nombre= $this->obj_kan->GetNombre();
            $if_fixed= $this->obj_kan->get_if_fixed();
        }

        if ($this->action == 'drag_task') {
            $this->obj_task->Set();
            $this->inicio= date('Y', strtotime($this->obj_task->GetFechaInicioPlan()));
            $this->fin= date('Y', strtotime($this->obj_task->GetFechaFinPlan()));

            $this->obj_kan->update_tarea($this->id_kanban_column_origen, $this->id_kanban_column_target, $this->task_numero);

            $this->get_columns_status();
            $this->_register_tarea();
            $this->_register_eventos();
        } 

        if ($this->action == 'drag_column') {
            $this->_order_columns();
        }

        if ($this->action == "add") {
            $this->nombre= urldecode($_GET['nombre']);
            $this->obj_kan->SetNombre($this->nombre);

            $this->descripcion= !empty($_GET['descripcion']) ? urldecode($_GET['descripcion']) : null;
            $this->obj_kan->SetDescripcion($this->descripcion);

            $this->class= !empty($_GET['kanban_column_class']) ? $_GET['kanban_column_class'] : 'default';
            $this->obj_kan->SetColumnClass($this->class);

            $this->obj_kan->add(null);
        }

        if($this->action == "delete_column") {
            if(!$if_fixed) {
                $this->error= $this->obj_kan->delete();
                if ($this->error) {
                    $this->error= "No se ha podido borrar la columna. Por favor, asegurece de que no existan tareas en la columna";
                }
            } else {
                $this->error= "La columna ".strtoupper($this->nombre)." es una de las tres principales o basicas del metodo Kanban. ";
                $this->error.= "No puede ser eliminada.";
            }
        }

        $url_page = "../php/kaban.interface.php?id_proyecto=$this->id_proyecto&id_proceso=$this->id_proceso";     
        add_page($url_page, "add", 'i');

        if (is_null($this->error)) {
            if ($this->action == "add" || $this->action == "delete_column") {
            ?>
                cerrar(1, "");
        <?php    
            }
            
            if ($this->action == "drag_task") {
            ?>
                cerrar(0, "");
            <?php
            }        
        } else {
            ?>
            cerrar(0, "<?=$this->error?>");
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
        $interface = new Tinterface($clink);
        $interface->apply();
        ?>
    <?php if (!$ajax_win) { ?>
    });
    <?php } ?>
</script>

