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
require_once "class/unidad.class.php";
require_once "class/usuario.class.php";
require_once "class/grupo.class.php";
require_once "class/indicador.class.php";

require_once "class/escenario.class.php";
require_once "class/proceso.class.php";
require_once "class/proceso_item.class.php";

require_once "class/tipo_evento.class.php";
require_once "class/tipo_auditoria.class.php";
require_once "class/tipo_reunion.class.php";
require_once "class/tipo_lista.class.php";
require_once "class/tablero.class.php";

require_once "class/entity.class.php";
require_once "class/code.class.php";
require_once "class/badger.class.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !empty($_GET['ajax_win']) ? $_GET['ajax_win'] : false;
if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class Tinterface extends TplanningInterface {
    protected $tipo;
    protected $conectado;
    protected $if_entity;
    protected $id_entity,
            $id_entity_code;

    public function __construct($clink= null) {
        $this->clink= $clink;
        TplanningInterface::__construct($clink);
    }

    protected function setIndicadores() {
        $error= null;

        $use_undefined= $_POST['multiselect-inds_use_undefined'];
        $obj= new Tindicador($this->clink);
        $result= $obj->listar();

        while ($row= $this->clink->fetch_array($result)) {
            $value = !$use_undefined ? $_POST['multiselect-inds_ind' . $row['_id']] : setNULL_undefined($_POST['multiselect-inds_ind' . $row['_id']]);
            $_value = !$use_undefined ? $_POST['multiselect-inds_init_ind' . $row['_id']] : setNULL_undefined($_POST['multiselect-inds_init_ind' . $row['_id']]);
            $peso = $_POST['multiselect-inds-select_' . $row['_id']];

            if ((!$use_undefined && !empty($value)) || ($use_undefined && !is_null($value))) {
                $critico= $_POST['id_ind_'.$row['_id']];
                $critico= empty($critico) ? 0 : 1;

                if ($this->action == 'add')
                    $critico= null;

                $this->obj->SetIdIndicador($row['_id']);
                $this->obj->set_id_indicador_code($row['_id_code']);

                $error= $this->obj->setIndicador('add',$peso, $critico);
                $cant= $this->obj->GetCantidad();

                if ($cant == 0)
                    $error= $this->obj->setIndicador('update',$peso, $critico);

                $this->obj->expand_peso_indicadores(null, null, $peso, $critico);

            } else {
                if ((!$use_undefined && (!empty($_value) && empty($value))) || ($use_undefined && (!is_null($_value) && is_null($value)))) {
                    $this->obj->SetIdIndicador($row['_id']);
                    $this->obj->setIndicador('delete');

                    $this->obj_code->reg_delete('tproceso_indicadores', 'id_proceso_code', $this->id_code, 'id_indicador_code', $row['id_code']);
                }
            }

            if (!is_null($error))
                break;
        }

        unset($obj);
        return $error;
    }

    private function fix_proceso() {
        $obj_prs= new Tproceso($this->clink);
        $obj_prs->SetYear($this->year);
        $obj_prs->SetIdEntity($_SESSION['id_entity']);
        $obj_prs->SetIdProceso($this->id_proceso);

        if ($this->tipo < $_SESSION['entity_tipo'])
            return;

        $id_proceso_jefe= $this->id_proceso;
        $id_proceso_jefe_code= $this->id_proceso_code;
        if ($this->tipo > _TIPO_GRUPO && $_SESSION['entity_tipo'] < _TIPO_GRUPO) {
            $id_proceso_jefe= $obj_prs->get_proceso_top($this->id_proceso, $corte= _TIPO_GRUPO, true, true);
            $id_proceso_jefe_code= get_code_from_table("tprocesos", $id_proceso_jefe);
        }
        
        $obj_user= new Tusuario($this->clink);

        reset($this->accept_mail_user_list);
        foreach ($this->accept_mail_user_list as $user) {
            if ($this->id_proceso == $user['id_proceso'])
                continue;
            $obj_user->SetIdUsuario($user['id']);
            $obj_user->update_proceso($id_proceso_jefe, $id_proceso_jefe_code);
        }

        reset($this->denied_mail_user_list);
        foreach ($this->denied_mail_user_list as $user) {
            if ($id_proceso_jefe != $user['id_proceso'])
                continue;
            $obj_user->SetIdUsuario($user['id']);
            $obj_user->update_proceso($_SESSION['id_entity'], $_SESSION['id_entity_code']);
        }
    }

    private function set_responsable_entity() {
        $obj_user= new Tusuario($this->clink);
        $obj_user->SetIdUsuario($this->id_responsable);
        $obj_user->Set();

        if ($obj_user->GetRole() < _SUPERUSUARIO) {
            $nivel= $this->id == $_SESSION['id_entity'] ? _SUPERUSUARIO : _PLANIFICADOR;
            $obj_user->SetRole($nivel);
            $obj_user->SetClave(null);
            $obj_user->update(false);
        }
    }

    private function fix_entity() {
        $obj= new Ttipo_evento($this->clink);
        $obj->copy($_SESSION['local_proceso_id'], $this->id_proceso, $this->id_proceso_code);
        unset($obj);

        $obj= new Ttipo_auditoria($this->clink);
        $obj->copy($_SESSION['local_proceso_id'], $this->id_proceso, $this->id_proceso_code);
        unset($obj);

        $obj= new Ttipo_reunion($this->clink);
        $obj->copy($_SESSION['local_proceso_id'], $this->id_proceso, $this->id_proceso_code);
        unset($obj);

        $obj= new Ttipo_lista($this->clink);
        $obj->copy($_SESSION['local_proceso_id'], $this->id_proceso, $this->id_proceso_code);
        unset($obj);

        $obj= new Tproceso($this->clink);
        $obj->set_entity($_SESSION['id_entity'], $this->id_proceso);

        $obj= new Ttablero($this->clink);
        $obj->set_entity($_SESSION['local_proceso_id'], $this->id_proceso, $this->id_proceso_code); 
    }

    private function get_data_proceso($id_proceso) {
        $obj_prs= new Tproceso($this->clink);
        $obj_prs->SetIdProceso($id_proceso);
        $obj_prs->Set();
        $array= array('id_entity'=>$obj_prs->GetIdEntity(), 'id_entity_code'=>$obj_prs->get_id_entity_code());
        return $array;
    }

    public function apply() {
        global $array_procesos_entity;

        $this->obj = new Tproceso_item($this->clink);
        $this->obj->action = $this->action;
        if (!empty($this->id))
            $this->obj->SetIdProceso($this->id);

        $codigo = null;
        $error= null;
        if ($this->action == 'add') {
            $obj_entity= new Tentity($this->clink);
            if ($obj_entity->block_entity()) {
                $error= $obj_entity->error;
        }   }
        
        if (!empty($this->id)) {
            $this->id_proceso= $this->id;
            $this->obj->Set();

            $this->_id_responsable= $this->obj->GetIdResponsable();
            $this->id_code= $this->obj->get_id_code();
            $this->id_proceso_code= $this->obj->get_id_proceso_code();

            $codigo= $this->obj->GetCodigo();
            $conectado= $this->obj->GetConectado();
            $this->tipo= $this->obj->GetTipo();
        }

        if ($this->action == 'add' || $this->action == 'update') {
            $this->obj->SetNombre(trim($_POST['nombre']));
            $this->obj->SetEntrada(trim($_POST['entrada']));
            $this->obj->SetSalida(trim($_POST['salida']));
            $this->obj->SetRecursos(trim($_POST['recursos']));
            $this->obj->SetLugar($_POST['lugar']);

            $this->id_responsable = $_POST['responsable'];
            $this->obj->SetIdResponsable($this->id_responsable);

            $this->obj->SetDescripcion(trim($_POST['descripcion']));

            $id_proceso = $_POST['proceso'];
            $id_proceso_code = $_POST['proceso_code_' . $id_proceso];
            $this->obj->SetIdProceso_sup($id_proceso);
            $this->obj->set_id_proceso_sup_code($id_proceso_code);

            $this->tipo = $_POST['tipo'];
            $this->obj->SetTipo($this->tipo);

            $this->if_entity= $_POST['if_entity'];
            $this->obj->SetIfEntity($this->if_entity);

            $this->id_entity= null;
            $this->id_entity_code= null;
            if (!$this->if_entity && $this->id != $_SESSION['id_entity']) {
                $array=!empty($id_proceso) ? $this->get_data_proceso($id_proceso) : null;
                $this->id_entity= !empty($array['id_entity']) ? $array['id_entity'] : $_SESSION['id_entity'];
                $this->id_entity_code= !empty($array['id_entity']) ? $array['id_entity_code'] : $_SESSION['id_entity_code'];
            }
            if ($this->if_entity) {
                $this->id_entity= null;
                $this->id_entity_code= null;
                $this->obj->SetNull_cronos_syn();
            }

            $this->obj->SetIdEntity($this->id_entity);
            $this->obj->set_id_entity_code($this->id_entity_code);

            $this->obj->SetLocalArchive($_POST['local_archive']);
            if (!empty($_POST['codigo_archive']))
                $this->obj->SetCodigoArchive(strtoupper($_POST['codigo_archive']));

            $this->inicio= !empty($_POST['inicio']) ? $_POST['inicio'] : $_POST['init_inicio'];
            $this->obj->SetInicio($this->inicio);
            $this->fin= !empty($_POST['fin']) ? $_POST['fin'] : $_POST['init_fin'];
            $this->obj->SetFin($this->fin);

            $this->conectado = $_POST['conectado'];
            $this->obj->SetConectado($this->conectado);
            $this->obj->SetMail_address(trim($_POST['email']));
            $this->obj->SetCodigo($_POST['codigo']);
            $this->obj->SetProtocolo($_POST['protocolo']);
            $this->obj->SetURL($_POST['url']);
            $this->obj->SetPuerto($_POST['puerto']);

            $this->obj->SetYear($this->year);
        }

        if ($this->action == 'update') {
            $this->obj->set_orange($_POST['_c1']);
            $this->obj->set_yellow($_POST['_c2']);
            $this->obj->set_green($_POST['_c3']);
            $this->obj->set_aqua($_POST['_c4']);
            $this->obj->set_blue($_POST['_c5']);
        }

        $this->fecha_fin= $_POST['fecha_origen'];

        if ($this->action == 'update' || $this->action == 'add') {
            $nombre_entity= $array_procesos_entity[$this->id_proceso]['nombre'];

            if ($array_procesos_entity[$this->id_proceso]['id_entity'] != $_SESSION['id_entity'] 
                && $array_procesos_entity[$this->id_proceso]['id_entity'] != $this->id_proceso) {
                $error= "No es posible asignarle unidades suboordinadas a $nombre_entity ";
                $error.= "desde esta entidad. Deberá entrar a la entidad $nombre_entity para crear la estructura.";
            }
            if (!empty($this->id) && $this->id == $_SESSION['id_entity']) {
                $error= null;
            }
        }

        if ($this->action == 'add' && is_null($error)) {
            $error = $this->obj->add();

            if (is_null($error)) {
                $this->id = $this->obj->GetIdProceso();
                $this->id_proceso = $this->id;

                $this->obj_code->SetId($this->id);

                if (($_POST['conectado'] != _NO_LOCAL && !empty($_POST['codigo'])) && $_POST['codigo'] != $_SESSION['location']) {
                    $this->id_code = $this->obj_code->getCode(1, $_POST['codigo']);
                    $this->obj_code->set_id_code($this->id_code);
                    $this->obj_code->update_code("tprocesos", "id", "id_code", $this->id, $this->id_code);
                } else {
                    $this->obj_code->set_code('tprocesos', 'id', 'id_code');
                }

                $this->id_code = $this->obj_code->get_id_code();
                $this->id_proceso_code = $this->id_code;
                $this->obj->set_id_code($this->id_code);
                $this->obj->set_id_proceso_code($this->id_code);
            }
        }

        if ($this->action == 'update' && is_null($error)) {
            $error = $this->obj->update();

            if ((($_POST['conectado'] != _NO_LOCAL && $codigo != $_POST['codigo']) && $_POST['codigo'] != $_SESSION['location'])
                || $this->conectado != $conectado) {
                $this->id_code = $this->obj_code->getCode(1, $_POST['codigo']);
                $this->obj_code->update_code("tprocesos", "id", "id_code", $this->id, $this->id_code);

                $this->id_proceso_code = $this->id_code;
                $this->obj->SetIdProceso($this->id);
                $this->obj->set_id_proceso_code($this->id_code);
                $this->obj->update_code();
            }
        }

        if (($this->action == 'add' || $this->action == 'update') && is_null($error)) {
            $this->set_responsable_entity();
            $this->set_reg_table('tusuario_procesos');
            $this->setUsuarios();
            $this->setGrupos();
            $this->fix_proceso();

            $this->obj->SetYear($this->year);

            $error = $this->setIndicadores();

            // el tipo debe de coincidir con el corte que se utiliza en usuario_tabs.inc.php
            if ($this->tipo <= _TIPO_GRUPO || $this->conectado != _NO_LOCAL) {
                $obj_user = new Tusuario($this->clink);
                $obj_user->SetIdUsuario($this->id_responsable);
                $obj_user->Set();

                $obj_user->SetIdProceso($this->id);
                $obj_user->set_id_proceso_code($this->id_code);
                $obj_user->SetClave(null);
                $obj_user->SetFirma(null, null);
                $obj_user->update(false);
            }
        }

        if (($this->action == 'add' || $this->action == 'update') && is_null($error)) {
            if ($this->if_entity && !$_POST['_if_entity']) {
                $this->fix_entity();
            }
        }
        
        if (($this->action == 'add' || $this->action == 'update') && is_null($error)) {
            if (isset($obj_user)) 
                unset($obj_user);
            $obj_user = new Tusuario($this->clink);
            
            $obj_user->SetIdUsuario($this->id_responsable);
            $obj_user->SetIdProceso_jefe($this->id);
            $obj_user->set_id_proceso_jefe_code($this->id_code);

            if ($this->action == 'add')
                $obj_user->update_id_proceso_jefe('add', $this->tipo);
            if ($this->action == 'update' && $this->_id_responsable != $this->id_responsable)
                $obj_user->update_id_proceso_jefe('update', $this->tipo);
        }

        if (($this->tipo == _TIPO_PROCESO_INTERNO  && $this->action == 'update') && is_null($error)) {
            $error= $this->obj->set_criterio_eval();
        }

        if ($this->action == 'delete') {
            global $Ttipo_proceso_array;
            $observacion= $Ttipo_proceso_array[$this->tipo]. ", ".$this->obj->GetNombre(). " ".$this->obj->GetInicio(). " - ". $this->obj->GetFin();

            $error = $this->obj->eliminar();
            if (is_null($error)) {
                $this->obj_code->SetObservacion($observacion);
                $this->obj_code->reg_delete('tprocesos', 'id_code', $this->id_code);
            }
        }

        if ($this->action == 'edit' || $this->action == 'list') {
            $error = $this->obj->Set();
        }

        $url_page= "../php/proceso.interface.php";
        $url_page.= "?id=$this->id&signal=$this->signal&action=$this->action&year=$this->year&month=$this->month";
        $url_page.= "&day=$this->day&id_proceso=$this->id_proceso&exect=$this->action&menu=$this->menu&error=".urlencode($error);

        add_page($url_page, $this->action, 'i');

        unset($_SESSION['obj']);

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($error)) {
                if ($this->action == 'update' && $this->id == $_SESSION['local_proceso_id']) {
                ?>
                    parent.location.href='<?= _SERVER_DIRIGER . 'index.php' ?>';
                <?php
                }

                $_SESSION['obj'] = serialize($this->obj);

                if (($this->action == 'add' && $this->tipo != _TIPO_PROCESO_INTERNO) || ($this->action == 'update' || $this->action == 'delete')) {
                ?>
                     self.location.href='<?php next_page(); ?>';
                <?php
                }

                if ($this->action == 'add' && $this->tipo == _TIPO_PROCESO_INTERNO)
                    $this->action= 'edit';
                if ($this->action == 'edit' || $this->action == 'list') {
                    if ($this->action == 'edit')
                        $this->action= 'update';
                ?>
                        self.location='../form/fproceso.php?action=<?=$this->action?>&signal=<?=$this->signal?>';

            <?php } } else {
                $this->obj->error= $error;
                $_SESSION['obj']= serialize($this->obj);

                ?>
                self.location.href='<?php prev_page($error);?>';
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
            $interface= new Tinterface($clink);
            $interface->apply();
            ?>

        <?php if (!$ajax_win) { ?>
        });
        <?php } ?>
    </script>
<?php } ?>