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
require_once "class/connect.class.php";
require_once "class/usuario.class.php";
require_once "class/grupo.class.php";

require_once "class/tablero.class.php";
require_once "class/proyecto.class.php";
require_once "class/orgtarea.class.php";

require_once "class/code.class.php";
require_once "class/proceso.class.php";

require_once "class/entity.class.php";
?>

<?php
$ajax_win= ($_POST['menu'] == 'usuario' || $_GET['menu']) ? false : true;
include "_header.interface.inc";
?>

<?php
class Tuser_interface extends Tbase {
    public $menu;
    private $obj;
    private $global_user;
    private $user_ldap;

    public function Tuser_interface($clink) {
        $this->id = !empty($_GET['id']) ? $_GET['id'] : $_POST['id'];

        $this->action = !empty($_POST['exect']) ? $_POST['exect'] : $_GET['exect'];
        $this->menu = !empty($_POST['menu']) ? $_POST['menu'] : $_GET['menu'];
        $this->error = $_GET['error'];
        $this->clink = $clink;

        $this->user_ldap = !is_null($_POST['user_ldap']) ? $_POST['user_ldap'] : $_GET['user_ldap'];

        $this->obj= new Tusuario($this->clink);
        $this->obj_code= new Tcode($this->clink);
    }

    private function setUsuario($tab) {
        if ($tab == 'tc') {
            $tobj = new Ttablero($this->clink);
            $tab = 'tab_tc';
            $msg = "Los Tableros de Control que hayan sido asignados a Grupos de Usuarios a los que pertenece ";
            $msg.= "el usuario no serán eliminados. Deberá retirar el usuario del (los) Grupo(s) o eliminarle a dicho ";
            $msg.= "Grupo(s) el acceso a cada Tablero en específico.";
        }

        $tobj->SetYear($_SESSION['current_year']);
        $result = $tobj->listar();

        $tobj->SetIdUsuario($this->id);
        //$tobj->cleanUsuario();

        while ($row = $this->clink->fetch_array($result)) {
            $value = $_POST[$tab . $row['_id']];
            $tobj->SetId($row['_id']);

            if (!empty($value)) {
                $error = $tobj->setUsuario();
            } else {
                if (!empty($_POST['init_' . $tab . $row['_id']])) {
                    $error = $tobj->setUsuario('delete');

                    if (is_null($error) && $this->obj->GetIfGlobalUser()) {
                        if ($tab == 'tab_pr')
                            $this->obj_code->reg_delete('tusuario_proyectos', 'id_usuario', $this->id, 'id_proyecto_code', $row['id_code']);
            }   }   }

            if (!is_null($error))
                break;
        }

        if (is_null($error) && !empty($msg))
            echo "<script language='javascript'>alert(\"$msg\")</script>";

        unset($tobj);
        return $error;
    }

    private function setSubordinados() {
        $error = null;

        $use_undefined= (int)$_POST['multiselect-users_use_undefined'];
        $tobj = new Torgtarea($this->clink);
        $tobj->SetIdResponsable($this->id);
        // $tobj->cleanObjeto();

        $obj_user = new Tusuario($this->clink);
        $obj_user->set_user_date_ref($this->user_date_ref);
        $result = $obj_user->listar(null);

        while ($row = $this->clink->fetch_array($result)) {
            $value = !$use_undefined ? $_POST['multiselect-users_user' . $row['_id']] : setNULL_undefined($_POST['multiselect-users_user' . $row['_id']]);
            $_value = !$use_undefined ? $_POST['multiselect-users_init_user' . $row['_id']] : setNULL_undefined($_POST['multiselect-users_init_user' . $row['_id']]);

            if ((!$use_undefined && !empty($value)) || ($use_undefined && !is_null($value))) {
                $tobj->SetIdUsuario($row['id']);
                $error = $tobj->setUsuario();
            } else {
                if ((!$use_undefined && (!empty($_value) && empty($value))) || ($use_undefined && (!is_null($_value) && is_null($value)))) {
                    $tobj->SetIdUsuario($row['id']);
                    $error = $tobj->setUsuario('delete');
                    if (is_null($error))
                        $this->obj_code->reg_delete('tsubordinados', 'id_responsable', $this->id,'id_usuario', $row['id']);
                }
            }

            if (!is_null($error))
                break;
        }

        unset($obj_user);
        if (!is_null($error))
            return $error;

        // Fijar los grupos de trabajo
        $obj_grupo = new Tgrupo($this->clink);
        $result = $obj_grupo->listar();

        while ($row = $this->clink->fetch_array($result)) {
            $value = !$use_undefined ? $_POST['multiselect-users_grp'.$row['_id']] : setNULL_undefined($_POST['multiselect-users_grp'.$row['_id']]);
            $_value = !$use_undefined ? $_POST['multiselect-users_init_grp'.$row['_id']] : setNULL_undefined($_POST['multiselect-users_init_grp'.$row['_id']]);

            if ((!$use_undefined && !empty($value)) || ($use_undefined && !is_null($value))) {
                $tobj->SetIdGrupo($row['_id']);
                $error = $tobj->setGrupo('add');
            } else {
                if ((!$use_undefined && !empty($_value)) || ($use_undefined && is_null($value))) {
                    $tobj->SetIdGrupo($row['_id']);
                    $error = $tobj->setGrupo('delete');

                    if (is_null($error))
                        $this->obj_code->reg_delete('tsubordinados', 'id_responsable', $this->id, 'id_grupo', $row['_id']);
                }
            }

            if (!is_null($error))
                break;
        }

        unset($obj_grupo);
        unset($tobj);
        return $error;
    }

    private function setProcesos() {
        $error = null;

        $use_undefined= (int)$_POST['multiselect-prs_use_undefined'];
        $tobj = new Tproceso($this->clink);
        $result = $tobj->listar();
        $tobj->SetIdUsuario($this->id);
        //       $tobj->cleanObjetoByUser();

        while ($row = $this->clink->fetch_array($result)) {
            $value = !$use_undefined ? $_POST['multiselect-prs_'.$row['_id']] : setNULL_undefined($_POST['multiselect-prs_'.$row['_id']]);
            $_value = !$use_undefined ? $_POST['multiselect-prs_init_'.$row['_id']] : setNULL_undefined($_POST['multiselect-prs_init_'.$row['_id']]);

            $tobj->SetIdProceso($row['_id']);
            $tobj->set_id_code($row['_id_code']);
            $tobj->set_id_proceso_code($row['_id_code']);

            if ((!$use_undefined && !empty($value)) || ($use_undefined && !is_null($value))) {
                $error = $tobj->setUsuario();
            } else {
                if ((!$use_undefined && !empty($_value)) || ($use_undefined && is_null($value))) {
                    $error = $tobj->setUsuario('delete');

                    if (is_null($error))
                        $this->obj_code->reg_delete('tusuario_procesos', 'id_usuario', $this->id, 'id_proceso_code', $row['_id_code']);
                }
            }

            if (!is_null($error))
                break;
        }

        if (is_null($error)) {
            $tobj->SetIdProceso($this->id_proceso);
            $tobj->set_id_proceso_code($this->id_proceso_code);
            $tobj->set_id_code($this->id_proceso_code);

            if (empty($_POST['init_tab_prs' . $this->id_proceso]))
                $tobj->setUsuario();
        }

        unset($tobj);
    }

    public function apply_setting() {
        if ($this->menu == 'user_tablero') {
            $f = "tc";
            $error = $this->setUsuario("tc");
        }
        if ($this->menu == 'user_subordinado') {
            $f = "su";
            $error = $this->setSubordinados();
        }
        if ($this->menu == 'user_proceso') {
            $f = "prs";
            $error = $this->setProcesos();
        }

        if (is_null($error)) {
        ?>
            cerrar();
        <?php }	else { ?>
            $('#div-ajax-panel').load('../form/ajax/fusuario_<?=$f?>.ajax.php?id_usuario=<?=$this->id?>&error=<?=urlencode($error)?>');
        <?php }
    }

    public function apply() {
        $error = null;
        $this->obj->action = $this->action;
        $this->obj->set_cronos();

        $error= null;
        if ($this->action == 'add') {
            $obj_entity= new Tentity($this->clink);
            if ($obj_entity->block_users()) {
                $error= $obj_entity->error;
        }   }        
        
        if ($this->action == 'add' || ($this->action == 'update' && !$this->user_ldap)) {
            $this->obj->SetUsuario(clean_string(trim($_POST['usuario'])));
        }

        if ($this->action == 'add' || $this->action == 'update') {
            $this->obj->SetNombre(trim($_POST['nombre']));
            $this->obj->SetMail_address(trim($_POST['email']));

            $this->obj->SetCargo($_POST['cargo']);
            $this->obj->SetNoIdentidad($_POST['no_identidad']);
            $this->obj->SetRole($_POST['role']);

            $this->id_proceso = $_POST['proceso'];
            $this->id_proceso_code = $_POST['proceso_code_' . $this->id_proceso];
            $this->obj->SetIdProceso($this->id_proceso);
            $this->obj->set_id_proceso_code($this->id_proceso_code);

            $this->global_user = !empty($_POST['global_user']) ? 1 : 0;
            $this->obj->SetIfGlobalUser($this->global_user);

            $this->obj->set_acc_sys($_POST['acc_sys']);
            $this->obj->set_acc_planwork($_POST['acc_planwork']);
            $this->obj->set_acc_planrisk($_POST['acc_planrisk']);
            $this->obj->set_acc_planaudit($_POST['acc_planaudit']);
            $this->obj->set_acc_planheal($_POST['acc_planheal']);
            $this->obj->set_acc_planproject($_POST['acc_planproject']);
            
            $this->obj->set_freeassign($_POST['freeassign']);

            $this->obj->set_acc_archive($_POST['acc_archive']);
            $this->obj->set_nivel_archive2($_POST['nivel_archive2']);
            $this->obj->set_nivel_archive3($_POST['nivel_archive3']);
            $this->obj->set_nivel_archive4($_POST['nivel_archive4']);
        }

        if (($this->action == 'add' || ($this->action == 'update') && $_POST['clave'] != "12345678")) {
            $this->obj->SetClave(clean_string(trim($_POST['clave'])));
        }

        $this->obj->SetFirma(null, null);

        if (($this->action == 'add' || $this->action == 'update') && is_null($error)) {
            if ($_POST['firma-trash'] == 0 && !empty($_FILES['firma-upload']['name'])) {
                $fileName = $_FILES['firma-upload']['name'];
                $tmpName = $_FILES['firma-upload']['tmp_name'];
                $fileSize = $_FILES['firma-upload']['size'];
                $fileType = $_FILES['firma-upload']['type'];

                $tmpImage = _DATA_DIRIGER_DIR . $fileName;
                redim_imagen($tmpName, $tmpImage, _MAX_IMG_SING_WIDTH, _MAX_IMG_SING_HEIGHT, 0);

                $datos = getimagesize($tmpImage);
                $fp = fopen($tmpImage, 'r');
                $image = fread($fp, $fileSize);
                $image = $_SESSION["_DB_SYSTEM"] == "mysql" ? addslashes($image) : bin2hex($image);
                fclose($fp);
                chmod($tmpImage, 0777);
                unlink($tmpImage);

                if (!get_magic_quotes_gpc())
                    $fileName = addslashes($fileName);

                $param['name'] = $fileName;
                $param['size'] = $fileSize;
                $param['type'] = $fileType;
                $param['dim'] = $datos[3];

                $this->obj->SetFirma($image, $param);
            }

            if ($_POST['firma-trash'])
                $this->obj->SetFirma(0, 0);
        }

        $this->obj->set_user_date_ref($_POST['user_date_ref']);
        $this->user_date_ref = $_POST['user_date_ref'];

        if ($this->action == 'add' && is_null($error)) {
            $error = $this->obj->add();
            $this->id = $this->obj->GetIdUsuario();
        }

        if ($this->action != 'add')
            $this->obj->SetIdUsuario($this->id);

        if (is_null($error) && $this->action == 'update') {
            if ($_POST['set_ldap'])
                $this->obj->set_ldap();

            $error = $this->obj->update(true);

            if (!empty($_POST['eliminado']))
                $this->obj->set_eliminado(false);
        }

        if (($this->action == 'add' || $this->action == 'update') && is_null($error)) {
            $this->setSubordinados();
            $this->setProcesos();
        }

        if ($this->action == 'delete' && $this->id != 1) {
            $this->obj->Set($this->id);
            $this->user_ldap= $this->obj->get_user_ldap();

            $error = $this->obj->eliminar($this->user_ldap ? false : true);
        }

        if ($this->action == 'edit' || $this->action == 'list') {
            $error = $this->obj->Set();
        }

        $url_page = "../php/user.interface.php";
        $url_page .= "?id=$this->id&signal=$this->signal&action=$this->action&year=$this->year&month=$this->month";
        $url_page .= "&day=$this->day&id_proceso=$this->id_proceso&exect=$this->action&menu=$this->menu";

        add_page($url_page, $this->action, 'i');

        $error = !is_null($this->error) ? $this->error . " " . $error : $error;
        ?>

        <?php
        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($error)) {
                $_SESSION['obj'] = serialize($this->obj);

                if ((($this->action == 'add' || $this->action == 'update') || $this->action == 'delete') && $this->menu == 'usuario') {
            ?>

                    <?php if ($this->action == 'update') { ?>
                        <?php if ($this->id_usuario == $_SESSION['id_usuario']) { ?>
                            confirm("Para que tomen efecto los cambios el usuario deberá salir del sistema y volver a entrar. ¿Desea salir ahora del sistema?", function(ok) {
                                if (!ok)
                                    self.location.href='<?php next_page();?>';
                                else
                                    parent.location.href= '<?=_SERVER_DIRIGER?>index.php';
                            });
                        <?php } else { ?>
                            alert('Los cambios tomarán efecto la próxima vez que el usuario acceda al sistema.', function(ok) {
                                if (ok)
                                    self.location.href='<?php next_page();?>';
                            });

                    <?php } } ?>
                    <?php if ($this->action == 'add' || $this->action == 'delete') { ?>
                            self.location.href='<?php next_page();?>';
                    <?php } ?>
            <?php
                }

                if ($this->action == 'edit' || $this->action == 'list') {
                    if ($this->action == 'edit')
                        $this->action= 'update';
            ?>
                    self.location.href='../form/fusuario.php?action=<?=$this->action?>&signal=<?="$this->signal#$this->id"; ?>'
            <?php
            } } else {
                     $this->obj->error= $error;

                     $_SESSION['obj']= serialize($this->obj);

                    if ($this->action == 'edit' || $this->action == 'list') {
                        if ($this->action == 'edit')
                            $this->action= 'update';
            ?>
                        self.location.href='../form/fusuario.php?action=<?=$this->action?>&signal=<?="$this->signal#$this->id"; ?>&error=<?=$error?>'
                    <?php } ?>

                self.location.href='<?php prev_page($error);?>';
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
        $interface= new Tuser_interface($clink);
        if ($_POST['menu'] == 'usuario' || $_GET['menu'])
            $interface->apply();
        else
            $interface->apply_setting();
        ?>
    <?php if (!$ajax_win) { ?>   }); <?php } ?>
</script>