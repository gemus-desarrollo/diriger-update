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
if (!$clink) 
    require_once _ROOT_DIRIGER_DIR."php/class/connect.class.php";

require_once "class/perspectiva.class.php";
require_once "class/inductor.class.php";
require_once "class/indicador.class.php";
require_once "class/programa.class.php";
require_once "class/unidad.class.php";
require_once "class/tipo_evento.class.php";

require_once "class/code.class.php";
require_once "base.interface.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !empty($_GET['ajax_win']) ? true : false;
if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class TPerspectivaInterface extends TbaseInterface {
    public $menu;

    public function __construct($clink) {
        $this->clink = $clink;
        TbaseInterface::__construct($clink);

        $this->nombre = trim($_POST['nombre']);
        $this->id_proceso = $_POST['proceso'];
        $this->id_proceso_code = $_POST['proceso_code_' . $this->id_proceso] ;

        $this->id_escenario = $_POST['id_escenario_' . $this->id_proceso];
        $this->id_escenario_code = $_POST['id_escenario_code_' . $this->id_proceso];

        $this->numero = $_POST['numero'];
        $this->color = $_POST['color'];
        $this->peso = $_POST['peso'];
    }

    private function setPeso_indicadores() {
        $error = null;
        if ($_POST['t_cant_multiselect-inds'] == 0)
            return null;

        $use_undefined= $_POST['multiselect-inds_use_undefined'];
        $obj = new Tindicador($this->clink);
        $result = $obj->listar();

        $obj->SetIdProceso($this->id_proceso);
        $obj->set_id_proceso_code($this->id_proceso_code);

        $array_ids= null;
        while ($row= $this->clink->fetch_array($result)) {
            if($array_ids[$row['_id']])
                continue;
            $array_ids[$row['_id']]= $row['_id'];
            
            $value = !$use_undefined ? $_POST['multiselect-inds_ind' . $row['_id']] : setNULL_undefined($_POST['multiselect-inds_ind' . $row['_id']]);
            $_value = !$use_undefined ? $_POST['multiselect-inds_init_ind' . $row['_id']] : setNULL_undefined($_POST['multiselect-inds_init_ind' . $row['_id']]);
            $peso = $_POST['multiselect-inds-select_' . $row['_id']];

            $obj->SetYear($this->year);
            $obj->SetIdIndicador($row['_id']);
            $obj->set_id_indicador_code($row['_id_code']);
            $obj->get_criterio();

            $obj->SetIdProceso($this->id_proceso);
            $obj->set_id_proceso_code($this->id_proceso_code);
            $obj->SetIdPerspectiva($this->id_perspectiva);
            $obj->set_id_perspectiva_code($this->id_perspectiva_code);
            $obj->SetYear($this->year);            

            $fin = min($row['_fin'], $this->fin);
            $obj->SetFin($fin);

            if ((!$use_undefined && !empty($value)) || ($use_undefined && !is_null($value))) {
                if ($peso != $_value) 
                    $error= $obj->set_perspectiva($peso);
            } else {
                if ((!$use_undefined && (!empty($_value) && empty($value))) || ($use_undefined && (!is_null($_value) && is_null($value))))
                    $error= $obj->set_perspectiva(null);
            }

            if (!is_null($error))
                break;
        }

        unset($obj);
        return $error;
    }

    private function setPeso_inductores() {
        $error= null;
        $obj = new Tinductor($this->clink);
        $result = $obj->listar();

        $obj->SetIdPerspectiva($this->id);
        $obj->set_id_perspectiva_code($this->id_code);

        while ($row = $this->clink->fetch_array($result)) {
            $id = $row['_id'];

            $obj->SetIdInductor($id);
            $value = $_POST['select_objt'.$id];
            $_value = $_POST['init_objt'.$id];

            if (!empty($value) && empty($_value)) {
                $error= $obj->set_perspectiva(true);
            } else {
                if (empty($value) && !empty($_value))
                    $error= $obj->set_perspectiva(false);
            }

            if (!is_null($error))
                break;
        }

        $this->clink->free_result($result);
        unset($obj);
        return $error;
    }

    public function apply() {
        $this->error = null;
        $obj = new Tperspectiva($this->clink);

        if (!empty($this->id)) {
            $obj->SetIdPerspectiva($this->id);
            $this->id_perspectiva = $this->id;
            $error = $obj->Set();
            $this->id_code = $obj->get_id_code();
            $this->id_perspectiva_code = $this->id_code;
        }

        $obj->SetYear($this->year);
        $obj->action = $this->action;

        if ($this->action == 'add' || $this->action == 'update') {
            $obj->SetNombre($this->nombre);
            $obj->SetIdProceso($this->id_proceso);
            $obj->set_id_proceso_code($this->id_proceso_code);

            $obj->SetDescripcion($this->descripcion);
        }

        if ($this->action == 'add' || $this->action == 'update') {
            $obj->SetNumero($this->numero);
            $obj->SetColor($this->color);
            $obj->SetPeso($this->peso);

            $obj->SetInicio($this->inicio);
            $obj->SetFin($this->fin);
        }

        if ($this->action == 'add') {
            $error = $obj->add();

            if (is_null($error)) {
                $this->id = $obj->GetIdPerspectiva();
                $this->id_perspectiva = $this->id;

                $this->id_code = $obj->get_id_code();
                $this->id_perspectiva_code = $this->id_code;
            }
        }

        if ($this->action == 'update') {
            $error = $obj->update();
        }

        if (is_null($error) && ($this->action == 'add' || $this->action == 'update')) {
            $this->setPeso_indicadores();
            $this->setPeso_inductores();
        }

        if ($this->action == 'delete') {
            $observacion= "No. ".$obj->GetNumero(). " ".$obj->GetInicio(). " - ". $obj->GetFin(). " ".$obj->GetNombre();

            $error = $obj->eliminar($this->_radio_date);

            if (is_null($error) && $this->menu == 'perspectiva') {
                $this->obj_code->SetObservacion($observacion);
                $this->obj_code->reg_delete('tperspectivas', 'id_code', $this->id_code);
            }
        }

        if ($this->action == 'edit' || $this->action == 'list') {
            $error = $obj->Set();
        }

        if (!is_null($this->model))
            if ($this->action != 'edit')
                return $error;
            else
                return $obj;

        $url_page = "../php/perspectiva.interface.php";
        $url_page .= "?id=$this->id&signal=$this->signal&action=$this->action&year=$this->year&month=$this->month&day=$this->day";
        $url_page .= "&id_proceso=$this->id_proceso&id_perspectiva=$this->id_perspectiva";
        $url_page .= "&exect=$this->action&menu=$this->menu";

        add_page($url_page, $this->action, 'i');

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($error)) {
                $_SESSION['obj'] = serialize($obj);

                if (($this->action == 'add' || $this->action == 'update') || $this->action == 'delete') {
                ?>
                    self.location.href = '<?php next_page(); ?>';
                <?php
                }

                if ($this->action == 'edit' || $this->action == 'list') {
                    if ($this->action == 'edit') 
                        $this->action = 'update';
                    ?>
                    self.location = '../form/fperspectiva.php?action=<?= $this->action ?>#<?= $this->id; ?>';
                <?php
                }
            } else {
                $obj->error = $error;
                $_SESSION['obj'] = serialize($obj);
                ?>
                self.location.href = '<?php prev_page($error); ?>';
                <?php
            }
    }   }
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
            $interface = new TPerspectivaInterface($clink);
            $interface->apply();
            ?>

        <?php if (!$ajax_win) { ?>
        });
        <?php } ?>
    </script>
<?php } ?>