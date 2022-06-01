<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "config.inc.php";
require_once "class/unidad.class.php";
if (!$clink)
    require_once _ROOT_DIRIGER_DIR."php/class/connect.class.php";

require_once "class/proceso.class.php";
require_once "class/peso.class.php";
require_once "class/peso_calculo.class.php";
require_once "class/inductor.class.php";
require_once "class/indicador.class.php";
require_once "class/registro.class.php";
require_once "class/calculator.class.php";

require_once "class/proyecto.class.php";
require_once "class/tablero.class.php";

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
class TindicadorInterface extends TbaseInterface {
    public $menu,
            $signal;
    private $ind_definido,
            $id_unidad,
            $id_unidad_code,
            $trend,
            $calculo,
            $cumulative,
            $chk_cumulative;
    private $formulated,
            $formulastr;
    private $_c;
    private $id_proceso_ref,
            $id_proceso_ref_code;

    public function __construct($clink) {
        $this->clink= $clink;
        TbaseInterface::__construct($clink);

        $this->ind_definido= $_POST['ind_definido'];

        $this->id_indicador= $this->id;
        if ($this->action == 'add' && $this->ind_definido == 2) {
            $this->id_indicador= !empty($_POST['indicador']) ? $_POST['indicador'] : $this->id;
        }

        $this->id_proceso_ref= $_POST['id_proceso_ref'];
        $this->id_proceso_ref_code= $_POST['proceso_code_'.$this->id_proceso_ref];

        $this->id_proyecto= $_POST['proyecto'];
        $this->id_proyecto_code= $_POST['id_proyecto_code_'.$this->id_proyecto];

        $this->trend= $_POST['trend'];

        $this->id_unidad= $_POST['unidad'];
        $this->id_unidad_code= $_POST['id_unidad'.$this->id_unidad];

        $this->periodicidad= $_POST['periodicidad'];
        $this->carga= $_POST['carga'];
        $this->cumulative= $_POST['cumulative'];
        $this->chk_cumulative= $_POST['chk_cumulative'];

        $this->calculo= $_POST['calculo'];
        $this->formulated= $_POST['formulated'];
        $this->formulastr= $_POST['formulastr'];

        $this->_c[$this->trend][1]= $_POST['_c'.$this->trend.'1'];
        $this->_c[$this->trend][2]= $_POST['_c'.$this->trend.'2'];
        $this->_c[$this->trend][3]= $_POST['_c'.$this->trend.'3'];
        $this->_c[$this->trend][4]= $_POST['_c'.$this->trend.'4'];
        $this->_c[$this->trend][5]= $_POST['_c'.$this->trend.'5'];
    }

    private function setInductores() {
        $error= NULL;

        $init_inicio= $_POST['init_inicio'];
        $init_fin= $_POST['init_fin'];
        $init_year= $_POST['init_year'];

        $tobj= new Tinductor($this->clink);
        $result= $tobj->listar();

        while ($row= $this->clink->fetch_array($result)) {
            $value= $_POST['select_objt'.$row['_id']];
            $_value= $_POST['init_objt'.$row['_id']];

            $inicio=  max($this->inicio, $row['_inicio']);
            $fin= min($this->fin, $row['_fin']);

            $this->obj->SetIdInductor($row['_id']);
            $this->obj->set_id_inductor_code($row['_id_code']);

            $this->obj->SetInicio($inicio);
            $this->obj->SetFin($fin);
            $this->obj->SetPeso($value);

            if (!empty($value))
                if ($_value != $value || ($_value = $value && ($inicio != $init_inicio || $fin != $init_fin || $this->year != $init_year)))
                    $error= $this->obj->expand_period_ref();
            else
                if (!empty($_value))
                    $error= $this->obj->delete_period_ref();

            if (!is_null($error))
                break;
        }

        unset($tobj);
        return $error;
    }

    public function setProcesos() {
        $error= null;

        $tobj= new Tproceso_item($this->clink);
        $tobj->SetIdEntity(null);
        $tobj->SetYear($this->year);
        $result= $tobj->listar();

        $tobj->SetIdIndicador($this->id_indicador);
        $tobj->set_id_indicador_code($this->id_indicador_code);

        $use_undefined= !empty($_POST['multiselect-prs_use_undefined']) ? 1 : 0;

        /* insertando el resto de los procesos a los que pertenece el indicador */
        while ($row= $this->clink->fetch_array($result)) {
            $value= setNULL_undefined($_POST['multiselect-prs_'.$row['_id']]);
            $_value = setNULL_undefined($_POST['multiselect-prs_init_' . $row['_id']]);
            $peso= $_POST['multiselect-prs-select_'.$row['_id']];

            if ((!is_null($value) && $value == $_value) 
                || (empty($value) && (($use_undefined && is_null($_value)) || (!$use_undefined && empty($_value)))))
                continue;

            $tobj->SetIdProceso($row['_id']);
            $tobj->set_id_proceso_code($row['_id_code']);

            for ($year= $this->inicio; $year <= $this->fin; $year++) {
                $tobj->SetYear($year);

                if (!empty($value) && $_value != $value) {
                    $error= $tobj->setIndicador('add', $peso);
                    $cant= $tobj->GetCantidad();

                    if ($cant == 0)
                        $error= $tobj->setIndicador('update', $peso);
                } 
                
                if (!is_null($_value) && is_null($value)) {
                    $tobj->SetIndicador('delete');
                    $this->obj_code->reg_delete('tproceso_indicadores', 'id_indicador_code', $this->id_code, 'id_proceso_code', $row['_id_code']);
                }
                
                if (!is_null($error))
                    break;                
            }

            if (!is_null($error))
                break;    
        }

        /* insertando primero el proceso que gestiona el indicador */
        for ($year= $this->inicio; $year <= $this->fin; $year++) {
            $tobj->SetYear($year);

            if ($this->action == 'add' || ($this->action == 'update' && (int)$this->id_proceso != (int)$this->id_proceso_ref)) {
                $tobj->SetIdProceso($this->id_proceso);
                $tobj->set_id_proceso_code($this->id_proceso_code);

                $tobj->setIndicador();
            }
        }

        unset($tobj);
        return $error;
    }

    public function setIndicadores() {
        $array_code= array();
        
        if (!$this->formulated || empty($this->calculo))
            return;
        $array_indicadores= array();
        $this->obj->clean_formulate();
        
        preg_match_all('/\_[A-Z]{2}[0-9]{8}/i', $this->calculo, $array_code);
        foreach ($array_code[0] as $code)
            $array_indicadores[]= substr($code, 1);

        foreach ($array_indicadores as $indi)
            $this->obj->addto_formulate($indi);
    }

    public function apply(&$id_remote= null) {
        $error= null;
        $conectado= null;

        $this->obj= new Tindicador($this->clink);
        $this->obj->action= $this->action;
        $_id_proceso= null;
        $_calculo= null;

        if (!empty($this->id_indicador)) {
            $this->obj->SetIdEscenario($this->id_escenario);
            $this->obj->SetIdIndicador($this->id_indicador);
            $this->obj->SetYear($this->year);

            $error= $this->obj->Set();

            $this->id_code= $this->obj->get_id_code();
            $this->id_indicador_code= $this->id_code;
            $this->id_proceso_ref= $this->obj->GetIdProceso();
            $this->id_proceso_code_ref= $this->obj->get_id_proceso_code();
            $_id_perspectiva= $this->obj->GetIdPerspectiva();
            $_calculo= $this->obj->GetFormCalculo();
        }

        if ($this->action == 'add') {
            $this->id_proceso= $_SESSION['id_entity'];
            $this->id_proceso_code= $_SESSION['id_entity_code'];

            $this->id_proceso_ref= $_SESSION['id_entity'];
            $this->id_proceso_code_ref= $_SESSION['id_entity_code'];
        }

        if ($this->action == 'update') {
            $this->id_proceso= $this->id_proceso_ref;
            $this->id_proceso_code= $this->id_proceso_code;
        }
        
        $this->obj->SetIdProceso($this->id_proceso_ref);
        $this->obj->set_id_proceso_code($this->id_proceso_code_ref);

        $this->obj->SetYear($this->year);

        if (($this->action == 'add' || $this->action == 'update') && !empty($this->calculo)) {
            $obj_cal= new Tcalculator($this->clink);
            $error= $obj_cal->error_formulated($this->calculo);

            if ($this->action == 'update' && !$error)
                $error= $obj_cal->validar_bucle($this->id_code, $this->calculo);
        }

        if (($this->action == 'add' || $this->action == 'update') && !$error) {
            $this->obj->SetNumero($this->numero);

            $this->obj->SetNombre(trim($this->nombre));
            $this->obj->SetDescripcion(trim($this->descripcion));
            $this->obj->SetFormCalculo(trim($this->calculo));

            $this->obj->SetIdProyecto($this->id_proyecto);
            $this->obj->set_id_proyecto_code($this->id_proyecto_code);

            $this->obj->SetIdUnidad($this->id_unidad);
            $this->obj->set_id_unidad_code($this->id_unidad_code);
            $this->obj->SetPeriodicidad($this->periodicidad);
            $this->obj->SetCarga($this->carga);
            $this->obj->SetIfCumulative($this->cumulative);
            $this->obj->SetChkCumulative($this->chk_cumulative);
            $this->obj->SetIfFormulated($this->formulated);

            $this->obj->SetPeso($this->peso);
            $this->obj->SetInicio($this->inicio);
            $this->obj->SetFin($this->fin);

            if (($_id_perspectiva != $this->id_perspectiva && $this->action == 'update') || $this->action == 'add')
                $this->obj->SetIdPerspectiva($this->id_perspectiva);
            $this->obj->set_id_perspectiva_code($this->id_perspectiva_code);

            $this->obj->SetTrend($this->trend);
            $this->obj->set_orange($this->_c[$this->trend][1]);
            $this->obj->set_yellow($this->_c[$this->trend][2]);

            if ($this->trend == 1) {
                $this->obj->set_green($this->_c[$this->trend][3]);
                $this->obj->set_aqua($this->_c[$this->trend][4]);
                $this->obj->set_blue($this->_c[$this->trend][5]);
            }
            if ($this->trend == 2) {
                $this->obj->set_orange(200 - $this->_c[$this->trend][1]);
                $this->obj->set_yellow(200 - $this->_c[$this->trend][2]);
                $this->obj->set_green(200 - $this->_c[$this->trend][3]);
                $this->obj->set_aqua(200 - $this->_c[$this->trend][4]);
                $this->obj->set_blue(200 - $this->_c[$this->trend][5]);
            }
            if ($this->trend == 3) {
                $this->obj->set_orange_cot($this->_c[$this->trend][4]);
                $this->obj->set_yellow_cot($this->_c[$this->trend][3]);
            }
        }

        if (($this->action == 'add' && !$this->ind_definido) && !$error) {
            if (is_null($error))
                $error= $this->obj->add();

            if (is_null($error)) {
                $this->id= $this->obj->GetIdIndicador();
                $this->id_indicador= $this->id;

                $this->id_code= $this->obj->get_id_code();
                $this->id_indicador_code= $this->id_code;
            }
        }

        if ($this->action == 'update' && is_null($error)) {
            if (!$this->ind_definido)
                $error= $this->obj->update();
            else
                $error= $this->obj->update_inicio_fin();

            if (!$this->ind_definido && (!empty($_id_proceso) && $_id_proceso != $this->id_proceso))
                $this->obj->update_proceso_ref($_id_proceso, $this->id_proceso, $this->year);

            if (!is_null($_calculo) && strcasecmp($_calculo, $this->calculo) != 0) {
                $obj_peso= new Tpeso_calculo($this->clink);
                $obj_peso->SetIdEscenario($this->id_escenario);
                $obj_peso->SetYear($this->year);
                $obj_peso->set_cronos($this->cronos);

                $obj_peso->set_matrix();
                $obj_peso->SetYearMonth($this->year, $this->month);

                $obj_peso->set_calcular_indicador($this->id_indicador, null);
            }
        }

        if (($this->action == 'add' || $this->action == 'update') && !$this->ind_definido && is_null($error)) {
            $obj_registro= new Tregistro($this->clink);
            $obj_registro->SetYear($this->year);

            $obj_registro->SetIdIndicador($this->id_indicador);
            $obj_registro->set_id_code($this->id_code);

            $obj_registro->SetCarga($this->carga);
            $obj_registro->SetPeriodicidad($this->periodicidad);
            $obj_registro->SetInicio($this->inicio);
            $obj_registro->SetFin($this->fin);

            $obj_registro->SetTrend($this->trend);
            $obj_registro->SetIfFormulated($this->formulated);
            $obj_registro->SetIfCumulative($this->cumulative);

            $error= $obj_registro->insert_registro($this->action);
        }

        if (($this->action == 'add' || $this->action == 'update') && is_null($error)) {
            $this->setIndicadores();
            $error= $this->setInductores();

            $this->obj->SetInicio($this->inicio);
            $this->obj->SetFin($this->fin);
            $this->obj->SetTrend($this->trend);
            $this->obj->SetPeso($this->peso);

            if (is_null($error) && ($this->action == 'update' && $this->id_proceso != $this->id_proceso_ref)) {
                $error= $this->obj->delete_criterio($this->id_proceso_ref);
            }
            if (is_null($error))
                $error= $this->obj->expand_criterio_in_period();
            if (is_null($error))
                $this->setProcesos();
        }

        if (($this->action == 'add') && is_null($error)) {
            $this->obj_tablero= new Ttablero($this->clink);
            $id_integral= $this->obj_tablero->GetIdIntegral();

            $this->obj_tablero->SetIdIndicador($this->id_indicador);
            $this->obj_tablero->set_id_indicador_code($this->id_code);
            $this->obj_tablero->SetIdTablero($id_integral);
            $this->obj_tablero->setIndicador();

            unset($this->obj_tablero);
        }

        if ($this->action == 'delete') {
            $observacion= "No. ".$this->obj->GetNumero(). " ".$this->obj->GetInicio(). " - ". $this->obj->GetFin(). " ".$this->obj->GetNombre();

            $error= $this->obj->eliminar($this->_radio_date);

            if (is_null($error) && $this->_radio_date == 2) {
                $this->obj_code->SetObservacion($observacion);
                $this->obj_code->reg_delete('tindicadores', 'id_code', $this->id_code);
            }
        }

        if ($this->action == 'edit' || $this->action == 'list') {
            $error= $this->obj->Set();

            $this->id_proceso_ref= $this->obj->GetIdProceso();
            $this->id_proceso_ref_code= $this->obj->get_id_proceso_ref_code();

            $obj_prs= new Tproceso($this->clink);
            $obj_prs->Set($this->id_proceso_ref);
            $conectado= $obj_prs->GetConectado();
            $conectado= $conectado != _LAN && $this->id_proceso_ref != $_SESSION['local_proceso_id'] ? _NO_LOCAL : _LOCAL;
        }

        if (!is_null($this->model)) {
            $id_remote= $this->id_indicador;
            if ($this->action != 'edit') {
                return $error;
            }  else  {
                return $this->obj;
            }
        }

    	$this->id_perspectiva= $this->obj->GetIdPerspectiva();
        $this->id_inductor= $this->obj->GetIdInductor();

        $url_page= "../php/indicador.interface.php";
        $url_page.= "?id=$this->id_indicador&signal=$this->signal&year=$this->year&month=$this->month&day=$this->day";
        $url_page.= "&id_proceso=$this->id_proceso&id_perspectiva=$this->id_perspectiva";
        $url_page.= "&id_inductor=$this->id_inductor&id_indicador=$this->id_indicador&menu=$this->menu";
        $action= "&action=$this->action&exect=$this->action";

        add_page($url_page.$action,$this->action,'i');

        unset($_SESSION['obj']);

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($error)) {
                $_SESSION['obj']= serialize($this->obj);

                if (($this->action == 'add' || $this->action == 'update') || $this->action == 'delete') {
            ?>
                    self.location.href='<?php next_page();?>';
            <?php
                }

                if ($this->action == 'edit' || $this->action == 'list') {
                    if ($this->action == 'edit') $this->action= "update";
                    $update= $conectado == _NO_LOCAL ? "_update" : null;
                ?>
                    self.location='../form/findicador<?=$update?>.php?action=<?= $this->action?>';
            <?php } }  else {
                    $this->obj->error= $error;
                    $_SESSION['obj']= serialize($this->obj);
               ?>
                    self.location.href='<?php prev_page($error);?>';
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
            $interface= new TindicadorInterface($clink);
            $interface->apply();
            ?>

        <?php if (!$ajax_win) { ?>
        });
        <?php } ?>
    </script>
<?php } ?>