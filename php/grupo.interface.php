<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "config.inc.php";

require_once "class/grupo.class.php";
require_once "class/usuario.class.php";
require_once "interface.class.php";

require_once "class/connect.class.php";
require_once "class/register_planning.class.php";
require_once "class/grupo_extend.class.php";
require_once "class/badger.class.php";

require_once "class/code.class.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !is_null($_GET['ajax_win']) ? $_GET['ajax_win'] : true;
if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class Tinterface extends TbaseInterface {
    private $obj_extend;
    private $obser_add, $obser_del;

    public function __construct($clink= null) {
        $this->clink= $clink;
        TbaseInterface::__construct($this->clink);

        $this->obj= new Tgrupo($this->clink);
        $this->obj_extend= null;
        $this->obj->set_cronos($this->cronos);

        $cronos= odbc2date($this->cronos);
        $this->obser_add= "Asignado al agregar el usuario al grupo de trabajo en fecha $cronos";
        $this->obser_del= "Eliminada al eliminar al usuario del grupo de trabajo en fecha $cronos";
    }

    private function get_array_eventos($id_grupo) {
        if (isset($this->obj_extend)) {
            unset($this->obj_extend);
            $this->obj_extend= null;
        }

        $this->obj_extend= new Tgrupo_extend($this->clink);
        $this->obj_extend->SetYear($this->year);
        $this->obj_extend->SetIdGrupo($id_grupo);
        $this->obj_extend->set_cronos($this->cronos);
        $this->obj_extend->SetIdUsuario($this->id);
        $this->obj_extend->SetIdResponsable($_SESSION['id_usuario']);

        return $this->obj_extend->get_array_eventos();
    }

    protected function setUsuarios() {
        $error = null;

        $obj = new Tusuario($this->clink);
        $obj->set_use_copy_tusuarios(false);
        $obj->set_user_date_ref($this->fecha_fin);
        $result = $obj->listar();

        while ($row = $this->clink->fetch_array($result)) {
            $value = $_POST['multiselect-users_user' . $row['_id']];
            $_value= $_POST['multiselect-users_init_user' . $row['_id']];

            $this->obj->SetIdUsuario($row['_id']);

            if (!empty($value) && empty($_value)) {
                $error = $this->obj->setUsuario('add');

                if (is_null($error) && !is_null($this->obj_extend)) {
                    $this->obj_extend->SetIdUsuario($row['_id']);
                    $this->obj_extend->SetIdResponsable($_SESSION['id_usuario']);
                    $this->obj_extend->_observacion = $this->obser_add;

                    $this->obj_extend->update_usuario_eventos('add');
                    $this->obj_extend->update_usuario_procesos('add');
                }
            } else {
                if (!empty($_value) && empty($value)) {
                    $error = $this->obj->setUsuario('delete');

                    if (is_null($error) && !is_null($this->obj_extend)) {
                        $this->obj_extend->SetIdUsuario($row['_id']);
                        $this->obj_extend->SetIdResponsable($_SESSION['id_usuario']);
                        $this->obj_extend->_observacion = $this->obser_del;

                        $this->obj_extend->update_usuario_eventos('delete');
                        $this->obj_extend->update_usuario_procesos('delete');
                    }

                    $this->obj_code->reg_delete('tusuario_grupos', 'id_grupo', $this->id, 'id_usuario', $row['_id']);
                }
            }

            if (!is_null($error))
                break;
        }

        unset($obj);
        return $error;
    }

    public function apply() {
        $error= null;

        if (!empty($this->id)) {
            $this->id_grupo= $this->id;

            $this->obj->SetId($this->id_grupo);
            $this->obj->Set();
            $this->id_code= $this->obj->get_id_code();
        }

        if ($this->action == 'add' || $this->action == 'update') {
            $this->obj->action = $this->action;
            $this->obj->SetNombre(trim($_POST['nombre']));
            $this->obj->SetDescripcion(trim($_POST['descripcion']));

            $this->obj->SetIdEntity($_SESSION['id_entity']);
            $this->obj->set_id_entity_code($_SESSION['id_entity_code']);
        }

        $this->year= !empty($_POST['user_date_ref']) ? date('Y', strtotime($_POST['user_date_ref'])) : $_SESSION['current_year'];
        $this->year= !empty($this->year) ? $this->year : date('Y');

        $this->obj->set_user_date_ref($_POST['user_date_ref']);
        $this->fecha_fin = $_POST['user_date_ref'];

        if ($this->action == 'add') {
            $error = $this->obj->add();

            if (is_null($error)) 
                $this->id_grupo = $this->obj->GetIdGrupo();
        }

        if ($this->action == 'update') {
            $error = $this->obj->update();

            if (is_null($error))
                $this->get_array_eventos($this->id_grupo);
        }

        if (($this->action == 'add' || $this->action == 'update') && is_null($error)) {
            $error = $this->setUsuarios();
        } 

        if ($this->action == 'delete') {
            $observacion= "No. ".$this->obj->GetNombre();

            $error = $this->obj->eliminar();

            if (is_null($error)) {
                $this->obj_code->SetObservacion($observacion);
                $this->obj_code->reg_delete('tgrupos', 'id', $this->id_grupo);
            }
        }

        if ($this->action == 'edit' || $this->action == 'list') {
            $error = $this->obj->Set();
        }

        $url_page= "../php/grupo.interface.php";
        $url_page.= "?id=$this->id&signal=$this->signal&action=$this->action&year=$this->year&month=$this->month";
        $url_page.= "&day=$this->day&id_proceso=$this->id_proceso&exect=$this->action&menu=$this->menu";

        add_page($url_page, $this->action, 'i');

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
                    if ($this->action == 'edit') 
                        $this->action= 'update';
                ?>
                    self.location='../form/fgrupo.php?action=<?= $this->action?>&signal=<?= $this->signal?>';
                <?php } } else {
                    $this->obj->error= $error;

                    $_SESSION['obj']= serialize($this->obj);
                ?>
                    self.location.href='<?php prev_page($error);?>';
            <?php
            }
        }
    }

    public function apply_for_grupos() {
        $this->year= !empty($_POST['user_date_ref']) ? date('Y', strtotime($_POST['user_date_ref'])) : $_SESSION['current_year'];
        $this->year= !empty($this->year) ? $this->year : date('Y');

        $this->obj->SetYear($this->year);
        $this->obj->set_user_date_ref($_POST['user_date_ref']);
        $this->fecha_fin= $_POST['user_date_ref'];

        $result= $this->obj->listar();

        $this->obj->SetIdUsuario($this->id);
//	$tobj->cleanUsuario();

	while ($row = $this->clink->fetch_array($result)) {
            $value = $_POST['multiselect-grp_'.$row['_id']];
            $this->obj->SetIdGrupo($row['_id']);

            if (!empty($value)) {
                $error = $this->obj->setUsuario('add');

                if (is_null($error)) {
                    $cant = $this->get_array_eventos($row['_id']);

                    if (!empty($cant)) {
                        $this->obj_extend->_observacion = $this->obser_add;
                        $this->obj_extend->update_usuario_eventos('add');
                    }
                }
            } else {
                if (!empty($_POST['multiselect-grp_init_'.$row['_id']])) {
                    $error = $this->obj->setUsuario('delete');

                    if (is_null($error)) {
                        $cant = $this->get_array_eventos($row['_id']);

                        if (!empty($cant)) {
                            $this->obj_extend->_observacion = $this->obser_del;
                            $this->obj_extend->update_usuario_eventos('delete');
                        }
                    }

                    $this->obj_code->reg_delete('tusuario_grupos', 'id_grupo', $row['_id'], 'id_usuario', $this->id);
                }
            }

            if (!is_null($error)) break;
        }

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($error)) {
            ?>
                CloseWindow('div-ajax-panel');
            <?php } else { ?>
                alert("<?= $error ?>");
                CloseWindow('div-ajax-panel');
            <?php
            }
    }   }
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
        $menu= !empty($_POST['menu']) ? $_POST['menu'] : $_GET['menu'];

        $interface= new Tinterface($clink);
        if ($menu == 'grupo') 
            $interface->apply();
        if ($menu == 'user_grupo') 
            $interface->apply_for_grupos();
        ?>

    <?php if (!$ajax_win) { ?>
    });
    <?php } ?>
</script>