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

require_once "class/base.class.php";
require_once "class/connect.class.php";
require_once "class/time.class.php";
require_once "class/usuario.class.php";
require_once "class/proceso_item.class.php";

require_once "class/politica.class.php";
require_once "class/inductor.class.php";
require_once "class/objetivo_ci.class.php";
require_once "class/programa.class.php";
require_once "class/peso_calculo.class.php";

require_once "class/code.class.php";

require_once "register.interface.php";

class Tinterface extends TRegister {

    public function __construct($clink= null) {
        $this->clink = $clink;
        TRegister::__construct($clink);
    }

    public function apply() {
        $obj = new Tpeso($this->clink);
        $obj->set_cronos($this->cronos);

        $obj->SetIdProceso($_POST['id_proceso']);
        $obj->set_id_proceso_code($_POST['id_proceso_code']);
        $obj->SetYear($this->year);
        $obj->SetMonth($this->month);
        $obj->SetDay($this->day);
        $this->id_usuario= $_SESSION['id_usuario'];
        $obj->SetIdUsuario($this->id_usuario);

        $this->observacion= trim($_POST['observacion']);
        $obj->SetObservacion($this->observacion);

        $this->value= $_POST['_value'];
        $obj->SetCumplimiento($this->value);
        $obj->SetValue($this->value);

        $_item = !empty($_POST['_item']) ? $_POST['_item'] : $_GET['_item'];
        $i_global = !empty($_POST['i_global']) ? $_POST['i_global'] : $_GET['i_global'];
        $id_code = null;

        switch ($_item) {
            case 'per': {
                    $item = 'treg_perspectiva';
                    $id_code = get_code_from_table('tperspectivas', $this->id);
                    $obj->flag_field_prs = true;
                    $obj->set_observacion($item, 'id_perspectiva', $this->id, $id_code);
                    break;
                }
            case 'prog': {
                    $item = 'treg_programa';
                    $id_code = get_code_from_table('tprogramas', $this->id);
                    $obj->flag_field_prs = true;
                    $obj->set_observacion($item, 'id_programa', $this->id, $id_code);
                    break;
                }
            case 'pro': {
                    $item = 'treg_proceso';
                    $id_code = get_code_from_table('tprocesos', $this->id);
                    $obj->flag_field_prs = false;
                    $obj->set_observacion($item, 'id_proceso', $this->id, $id_code);
                    break;
                }
            case 'pol': {
                    $item = 'treg_politica';
                    $id_code = get_code_from_table('tpoliticas', $this->id);
                    $obj->flag_field_prs = true;
                    $obj->set_observacion($item, 'id_politica', $this->id, $id_code);
                    break;
                }
            case 'ind': {
                    $item = 'treg_inductor';
                    $id_code = get_code_from_table('tinductores', $this->id);
                    $obj->flag_field_prs = false;
                    $obj->set_observacion($item, 'id_inductor', $this->id, $id_code);
                    break;
                }
            case 'obj': {
                    $item = 'treg_objetivo';
                    $id_code = get_code_from_table('tobjetivos', $this->id);
                    $obj->flag_field_prs = true;
                    $obj->set_observacion($item, 'id_objetivo', $this->id, $id_code);
                    break;
                }
            case 'obj_sup': {
                    $item = 'treg_objetivo';
                    $id_code = get_code_from_table('tobjetivos', $this->id);
                    $obj->flag_field_prs = true;
                    $obj->set_observacion($item, 'id_objetivo', $this->id, $id_code);
                    break;
                }
            case 'obj_ci': {
                    $item = 'treg_objetivo';
                    $id_code = get_code_from_table('tobjetivos', $this->id);
                    $obj->SetIfControlInterno();
                    $obj->flag_field_prs = true;
                    $obj->set_observacion($item, 'id_objetivo', $this->id, $id_code);
                    break;
                }
        }

        if (is_null($this->error)) {
            $this->redirect = 'ok';

            $obj_user= new Tusuario($this->clink);
            $email_user= $obj_user->GetEmail($this->id_usuario);
            $responsable= $email_user['nombre'];
            if (!is_null($email_user['cargo'])) 
                $responsable.= ', '.textparse($email_user['cargo']);
            $responsable.= '  <br /><u>corte:</u>'.odbc2date($array_register['reg_fecha']).'<br /><u>registrado:</u>'.odbc2time_ampm($array_register['cronos']);
            ?>

            <script language='javascript' type="text/javascript" charset="utf-8">
                $('#observacion_<?=$_item?>_<?=$i_global?>').val("<?= $this->observacion?>");
                $('#registro_<?=$_item?>_<?=$i_global?>').val("<?= $responsable?>");

                $('#observacion_item').html("<?= $this->observacion?>");
                $('#responsable_item').html("<?= $responsable?>");

                cerrar();
            </script>

        <?php } else { ?>

            <script language='javascript' type="text/javascript" charset="utf-8">
                alert("<?=$this->error?>");
                cerrar();
            </script>

            <?php
        }
    }

}

$interface = new Tinterface($clink);
$interface->apply();
?>