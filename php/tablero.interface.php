<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";
require_once "config.inc.php";

require_once "class/connect.class.php";
require_once "class/grupo.class.php";
require_once "class/usuario.class.php";
require_once "class/indicador.class.php";
require_once "class/tablero.class.php";
require_once "class/code.class.php";

require_once "interface.class.php";
?>

<?php include "_header.interface.inc"; ?>

<?php
class Tinterface extends TplanningInterface {
    public function __construct($clink= null) {
        $this->clink= $clink;
        TplanningInterface::__construct($this->clink);
    }

    protected function setIndicadores() {
        $error= null;
        $use_undefined= $_POST['multiselect-inds_use_undefined'];

        $obj= new Tindicador($this->clink);
        $result= $obj->listar();

        while ($row= $this->clink->fetch_array($result)) {
            $this->obj->SetIdIndicador($row['_id']);
            $this->obj->set_id_indicador_code($row['_id_code']);

            $value = !$use_undefined ? $_POST['multiselect-inds_ind' . $row['_id']] : setNULL_undefined($_POST['multiselect-inds_ind' . $row['_id']]);
            $_value = !$use_undefined ? $_POST['multiselect-inds_init_ind' . $row['_id']] : setNULL_undefined($_POST['multiselect-inds_init_ind' . $row['_id']]);

            if ((!$use_undefined && !empty($value)) || ($use_undefined && !is_null($value))) {
                $error= $this->obj->setIndicador();
            } else {
                if ((!$use_undefined && (!empty($_value) && empty($value))) || ($use_undefined && (!is_null($_value) && is_null($value)))) {
                    $this->obj->setIndicador('delete');
                    $this->obj_code->reg_delete('tindicador_tableros', 'id_tablero', $this->id, 'id_indicador_code', $row['id_code']);
                }
            }

            if (!is_null($error))
                break;
        }

        unset($obj);
        return $error;
    }

    public function apply() {
        $this->obj= new Ttablero($this->clink);
        $this->set_reg_table('tusuario_tableros');

        if (!empty($this->id)) {
            $this->obj->SetId($this->id);
            $this->obj->SetIdTablero($this->id);
            $this->obj->Set();
            $this->id_code= $this->obj->get_id_code();
            if (empty($this->id_code))
                $this->id_code= $this->id;
        }

        $this->obj->action= $this->action;
        $this->obj->SetNombre(trim($_POST['nombre']));
        $this->obj->SetDescripcion(trim($_POST['descripcion']));
        $this->obj->use_perspectiva= $_POST['use_perspectiva'];

        $this->obj->set_user_date_ref($_POST['user_date_ref']);
        $this->user_date_ref= $_POST['user_date_ref'];

        if ($this->action == 'add') {
            $this->obj->SetIdEntity($_SESSION['id_entity']);
            $this->obj->set_id_entity_code($_SESSION['id_entity_code']);

            $error= $this->obj->add();
            $this->id= $this->obj->GetId();
        }

        if ($this->action != 'add')
            $this->obj->SetIdTablero($this->id);

        if ($this->action == 'update') {
            $error= $this->obj->update();
        }

        if ($this->action == 'update' || $this->action == 'add') {
            if (is_null($error))
                $error= $this->setUsuarios();
            if (is_null($error))
                $error= $this->setGrupos();
            if (is_null($error))
                $error= $this->setIndicadores();
        }

        if ($this->action == 'delete') {
            $error= $this->obj->eliminar();
            if (is_null($error))
                $this->obj_code->reg_delete('ttableros', 'id', $this->id);
        }

        if ($this->action == 'edit' || $this->action == 'list') {
            $error= $this->obj->Set();
        }

        $url_page= "../php/tablero.interface.php";
        $url_page.= "?id=$this->id&signal=$this->signal&action=$this->action&year=$this->year&month=$this->month&day=$this->day";
        $url_page.= "&id_proceso=$this->id_proceso&exect=$this->action&menu=$this->menu";

        add_page($url_page, $this->action, 'i');

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
                        $this->obj->action= 'update';
               ?>
                    self.location='../form/ftablero.php?action=<?= $this->obj->action.'&month='.$this->month.'&year='.$this->year.'&day='.$this->day.'&id='.$this->id.'&id_proceso='.$this->id_proceso; ?>';

                <?php
               }
            }  else {
                $this->obj->redirect= 'fail';
                $this->obj->error= $error;
                $_SESSION['obj']= serialize($this->obj);

                if ($this->action == 'add' || $this->action == 'update') {
              ?>
                    self.location='../form/ftablero.php?action=<?= $this->obj->action.'&month='.$this->month.'&year='.$this->year.'&day='.$this->day.'&id='.$this->id.'&id_proceso='.$this->id_proceso; ?>';
              <?php
                }

                if ($this->action == 'delete') {
              ?>
                    self.location.href='<?php prev_page($error);?>';
              <?php
                }
            }
        }   
    }
}
?>

        </div>
    </body>
</html>

<script type="text/javascript">
    $(document).ready(function() {
        setInterval('setChronometer()',1);

        $('#body-log table').mouseover(function() {
            _moveScroll= false;
        });
        $('#body-log table').mouseout(function() {
            _moveScroll= true;
        });

        <?php
        $interface= new Tinterface($clink);
        $interface->apply();
        ?>
    });
</script>