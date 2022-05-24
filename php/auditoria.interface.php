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
require_once "class/proceso.class.php";
require_once "class/nota.class.php";
require_once "class/auditoria.class.php";
require_once "class/tipo_evento.class.php";
require_once "class/lista.class.php";

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

    public function __construct($clink= null) {
        $this->clink= $clink;
        TplanningInterface::__construct($clink);
    }

    public function apply() {
        global $Ttipo_nota_origen_array;
        global $Ttipo_nota_array;

        $this->obj= new Tauditoria($this->clink);
        $error= null;
        $fecha_origen= null;
        $error_duplicate= null;
        
        if (!empty($this->id)) {
            $this->obj->SetIdAuditoria($this->id);
            $this->obj->Set();

            $this->kronos= $this->obj->get_kronos();
            $this->obj->set_id_responsable_2(null);

            $this->id_code= $this->obj->get_id_code();
            $this->id_auditoria= $this->id;
            $this->id_auditoria_code= $this->id_code;

            $this->_id_auditoria= $this->id;
            $this->_id_auditoria_code= $this->id_code;

            $this->_id_responsable= $this->obj->GetIdResponsable();

            $this->_periodicidad= $this->obj->GetPeriodicidad();
            $this->_periodic= $this->GetIfPeriodic();

            $fecha_origen= $this->obj->GetFechaInicioPlan();

            $this->tipo= $this->obj->GetTipo();
            $this->origen= $this->obj->GetOrigen();
        }

        $this->obj->set_cronos($this->cronos);
        $this->obj->action= $this->action;
        $this->obj->SetIdUsuario($_SESSION['id_usuario']);

        $this->className= 'Tauditoria';
        $this->obj->className= 'Tauditoria';

        if (!empty($_POST['toshow2']))
            $this->toshow= _EVENTO_ANUAL;
        else if (!empty($_POST['toshow1']))
            $this->toshow= _EVENTO_MENSUAL;
        else
            $this->toshow= _EVENTO_INDIVIDUAL;

        $this->obj->set_toshow_plan($this->toshow);
        $this->user_check= $_POST['user_check'];
        $this->obj->set_user_check_plan($this->user_check);

        $this->empresarial= !empty($_POST['tipo_actividad1']) ? $_POST['tipo_actividad1'] : $this->toshow;
        $this->obj->SetIfEmpresarial($this->empresarial);

        $this->id_tipo_evento= !empty($_POST['tipo_actividad3']) ? $_POST['tipo_actividad3'] : $_POST['tipo_actividad2'];
        $this->id_tipo_evento= !empty($this->id_tipo_evento) ? $this->id_tipo_evento : null;
        $this->obj->SetIdTipo_evento($this->id_tipo_evento);

        $this->id_tipo_evento_code= !empty($this->id_tipo_evento) ? get_code_from_table('ttipo_eventos', $this->id_tipo_evento) : null;
        $this->obj->set_id_tipo_evento_code($this->id_tipo_evento_code);

        if (empty($_POST['responsable']))
            $this->id_responsable= $_POST['id_usuario'];
        if ($_POST['responsable'] == 2)
            $this->obj->SetFuncionario($_POST['funcionario']);
        if ($_POST['responsable'] == 2 && $this->acc == 3)
            $this->id_responsable = $_POST['usuario'];
        if ($_POST['responsable'] == 1 || ($_POST['responsable'] == 2 && $this->acc != 3))
            $this->id_responsable = $_POST['subordinado'];
        if ($_POST['responsable'] == 3)
            $this->id_responsable= $_POST['usuario'];
        if (empty($this->id_responsable))
            $this->id_responsable= $_POST['id_usuario'];

        $this->obj->SetIdResponsable($this->id_responsable);

        if (($this->action == 'update' && !empty($this->_id_responsable)) && $this->_id_responsable != $this->id_responsable) {
            $this->obj->set_id_responsable_2($this->_id_responsable);
            $this->obj->set_responsable_2_reg_date($this->cronos);
        }

        $this->obj->SetNumero($_POST['numero']);
        if (!empty($_POST['numero_plus']))
            $this->obj->SetNumero_plus(trim($_POST['numero_plus']));

        $this->id_usuario= $_POST['id_usuario'];
        $this->obj->SetIdUsuario($this->id_usuario);

        /**
         * configuracion de usuarios y procesos segun las propiedades del usuario
         */
        global $config;
        global $badger;

        $badger= new Tbadger();
        $badger->SetLink($this->clink);
        $badger->SetYear($this->year);
        $config->freeassign= ($config->freeassign || ($_SESSION['planaudit'] || $_SESSION['nivel'] >= _SUPERUSUARIO)) ? true : false;
        $badger->set_user_date_ref($this->fecha_inicio);
        $badger->set_planaudit();

        $this->obj->SetIdProceso($this->id_proceso);
        $this->obj->set_id_proceso_code($this->id_proceso_code);

        if ($this->action == 'add' || $this->action == 'update') {
            $this->origen= $_POST['origen'];
            $this->obj->SetOrigen($this->origen);

            $this->id_tipo_auditoria = empty($_POST['tipo_auditoria']) ? null : $_POST['tipo_auditoria'];
            $this->obj->SetIdTipo_auditoria($this->id_tipo_auditoria);
            $this->id_tipo_auditoria_code= !empty($this->id_tipo_auditoria) ? $_POST['id_tipo_auditoria'.$this->id_tipo_auditoria] : null;
            $this->obj->set_id_tipo_auditoria_code($this->id_tipo_auditoria_code);

            $this->nombre= $this->GetNombre_auditoria();
            $this->obj->SetNombre($this->nombre);
            $this->obj->SetLugar(trim($_POST['lugar']));
            $this->obj->SetObjetivo(trim($_POST['descripcion']));

            $this->obj->SetOrganismo(trim($_POST['organismo']));
            $this->obj->SetJefe_equipo(trim($_POST['jefe_equipo']));

            $this->set_date_time_scheduler();
            $this->year= date('Y', strtotime($this->fecha_inicio));
            $this->obj->SetYear($this->year);
        }

        if ($this->action == 'add') {
            $this->error = $this->obj->add(null, null, $error_duplicate);

            if (is_null($this->error)) {
                $this->changed = false;

                $this->id = $this->obj->GetIdAuditoria();
                $this->id_auditoria = $this->id;
                $this->id_code = $this->obj->get_id_code();
                $this->id_auditoria_code = $this->id_code;
                $this->_id_auditoria = $this->id_auditoria;
                $this->_id_auditoria_code = $this->id_auditoria_code;
                $this->_id= $this->id;
                $this->_id_code= $this->id_code;

                $this->indice= $this->obj->indice;
                $this->indice_plus= $this->obj->indice_plus;                
                
                $this->setting('NUEVO', null, true);

            } else {
                $this->id= null;
                if ($error_duplicate) {
                    $this->id= $this->obj->find_auditoria_duplicated();
                    if ($this->id) {
                        $this->action= 'edit';
                        $this->error.= " Ahora puede modificar esta actividad ya registrada en el sistema.";
                    }
            }   }
        }

        $_radio_date= $_POST['_radio_date'];

        if ($this->action == 'update') {
            $array= null;
            $fecha_origen= ($_radio_date == 2) ? strtotime($fecha_origen) <= strtotime($this->fecha_inicio) ? $fecha_origen : $this->fecha_inicio : null;

            $this->obj->get_child_auditoria($this->id_auditoria, null, $fecha_origen);
            $this->obj->get_child_events_by_auditoria($this->id_auditoria, null, $fecha_origen);

            if (!$this->periodic && $this->_periodic) {
                $array= $this->obj->update_exclusive($this->id, $this->fecha_inicio);
                $this->updated= !is_null($array) ? true : false;

                if ($this->changed) {
                    $this->obj_doc= new Tdocumento($this->clink);
                    $this->obj_doc->SetIdAuditoria($this->id);
                    $this->array_documentos = $this->obj_doc->get_documentos(false);
                }
                if ($this->updated)
                    $this->if_child_auditoria(null, $array[0]);
            }

            if (!$this->updated && (empty($_radio_date) || $_radio_date == 2)) {
                $error= $this->obj->update();
            }    
            if (is_null($error)) {
                $this->indice= $this->obj->indice;
                $this->indice_plus= $this->obj->indice_plus;                
                
                if ($this->updated) {
                    $this->id_auditoria= $array[0];
                    $this->id_auditoria_code= $array[1];

                    $this->_id_auditoria= $this->id_auditoria;
                    $this->_id_auditoria_code= $this->id_auditoria_code;

                    $this->obj->SetIdAuditoria($this->id_auditoria);
                    $this->obj->set_id_auditoria_code($this->id_auditoria_code);
                }

                $this->_id= $this->id_auditoria;
                $this->_id_code= $this->id_auditoria_code;

                $this->setting('MODIFICADO', true, true);
            }
        }

        if ((!empty($this->id_auditoria)  && ($this->action == 'add' || $this->action == 'update')) && empty($this->error)) {
            $this->id_auditoria_ref= $this->id_auditoria;
            $this->id_auditoria_ref_code= $this->id_auditoria_code;

            $this->fix_periodic_events();
        }

        if (($this->action == 'edit' || $this->action == 'list') && !empty($this->id)) {
            $this->obj->SetIdAuditoria($this->id);
            $this->error= $this->obj->Set();
        }

        if ($this->action == 'delete' && is_null($this->error)) {
            $tipo= $this->obj->GetTipo();
            $origen= $this->obj->GetOrigen();
            $observacion= "{$Ttipo_nota_array[(int)$tipo]} {$Ttipo_nota_origen_array[(int)$origen]}";
            $observacion.= "<br />Inicio: ". $this->obj->GetFechaInicioPlan(). " Fin:". $this->obj->GetFechaFinPlan();

            $result= $this->obj->delete("{$this->year}-01-01", $this->id, $this->id_code, true, true);

            if ($result) {
                $this->obj_code->SetObservacion($observacion);
                $this->obj_code->reg_delete('tauditorias', 'id_code', $this->id_code);
            }
        }

        $error= $this->error;

        $_url_page= "../php/auditoria.interface.php?id=$this->id&menu=$this->menu&signal=$this->signal&action=$this->action";
        $url_page= "&exect=$this->action&id_proceso=$this->id_proceso&year=$this->year&month=$this->month&day=$this->day";
        $url_page.= "&id_tipo_auditoria=$this->id_tipo_auditoria&init_row_temporary=$this->init_row_temporary";
        $url_page= "&tipo_plan=$this->tipo_plan";

        add_page($_url_page.$url_page,$this->action,'i');

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($error)) {
                 if ($this->menu == 'auditoria' || $this->menu =='tablero') {
                    unset($_SESSION['obj']);
                    $_SESSION['obj']= serialize($this->obj);

                    if (($this->action == 'add' || $this->action == 'update') || $this->action == 'delete') {
                        if ($this->signal)
                ?>
                            self.location.href='<?php next_page(null, true);?>';
              <?php
                    }

                    if ($this->action == 'edit' || $this->action == 'list') {
                        if ($this->action == 'edit')
                            $this->action= 'update';
              ?>
                        self.location='../form/fauditoria.php?action=<?= $this->action?>&signal=<?= "$this->signal#$this->id"; ?>';
              <?php
                    }
                 }  else {
                    $obj_tmp= unserialize($_SESSION['obj']);
                    $obj_tmp->action= $this->obj->action;
                    $obj_tmp->error= $this->obj->error;
                    $obj_tmp->signal= $this->obj->signal;

                    $_SESSION['obj']= serialize($obj_tmp);
            ?>
                    CloseWindow('div-ajax-panel');
                    self.location.reload();
            <?php
                }
            } else {
                $this->obj->error= $error;
                $this->obj->signal= $this->signal;
                unset($_SESSION['obj']);
                $_SESSION['obj']= serialize($this->obj);
                
                if (!$error_duplicate) {    
                    ?>
                    self.location.href='<?php prev_page($error);?>';
                    <?php
                } else {
                    ?>
                       var url= '../form/fauditoria.php?action=update&id=<?= $this->id?>';
                       url+= '<?= '&id_proceso='.$this->id_proceso.'&id_proceso_code='.$this->id_proceso_code ?>';
                       self.location= url;
                    <?php   
                }
            }
    }   }
}
?>

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
        $interface= new Tinterface($clink);
        $interface->apply();
        ?>

        <?php if (!$ajax_win) { ?>
        });
        <?php } ?>
    </script>