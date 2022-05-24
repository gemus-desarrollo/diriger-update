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
require_once "class/grupo.class.php";
require_once "class/peso.class.php";

require_once "class/tmp_tables_planning.class.php";
require_once "class/register_planning.class.php";
require_once "class/evento.class.php";
require_once "class/asistencia.class.php";

require_once "class/user_user.class.php";

require_once "class/badger.class.php";
require_once "class/code.class.php";
?>

<?php
class Tinterface extends Tuser_user {
    private $cant_event;
    private $cant_audit;
    private $cant_task;
    private $cant_matter;

    public function __construct($clink= null) {
        $this->clink= $clink;
        Tuser_user::__construct($clink);
    }

    private function find_auditoria($id) {
        reset($this->array_eventos);
        foreach ($this->array_eventos as $row) {
            if ($row['id_auditoria'] == $id)
                return true;
        }
        return false;
    }

    private function find_tarea($id) {
        reset($this->array_eventos);
        foreach ($this->array_eventos as $row) {
            if ($row['id_tarea'] == $id)
                return true;
        }
        return false;
    }

    private function find_tematica($id) {
        reset($this->array_eventos);
        foreach ($this->array_eventos as $row) {
            if ($row['id_tematica'] == $id)
                return true;
        }
        return false;
    }

    private function fix_target_to_eventos() {
        $i= 0;
        foreach ($this->array_eventos as $id => $row) {
            if ($this->copy_user == 1) {
                $this->set_responsable('teventos', $id);
                $this->set_responsable("tproceso_eventos_{$row['year_init']}", $id, "id_evento");
            }
            $user_in= $this->insert_treg_evento($id, $row['year_init']);
            if (!$user_in)
                $this->insert_tusuario_eventos($row, $row['year_init']);
            if ($row['id_tipo_reunion'])
                $this->insert_tasistencias($row);

            $r= (float)(++$i) / $this->cant_event;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(0, "Copiando actividades ... $_r%", $r);
        }
    }

    private function fix_target_to_auditorias() {
        $i= 0;
        foreach ($this->array_auditorias as $id => $row) {
            if ($this->copy_user == 1) {
                $this->set_responsable('tauditorias', $id);
                $this->set_responsable("tproceso_eventos_{$row['year_init']}", $id, "id_auditoria");
            }
            $user_in= $this->find_auditoria($id);
            if (!$user_in) {
                $row['id_auditoria']= $row['id'];
                $row['id_auditoria_code']= $row['id_code'];

                $row['id']= null;
                $row['id_code']= null;

                $this->insert_tusuario_eventos($row, $row['year_init']);
            }

            $r= (float)(++$i) / $this->cant_audit;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(0, "Copiando auditorias ... $_r%", $r);
        }
    }

    private function fix_target_to_tareas() {
        $i= 0;
        foreach ($this->array_tareas as $id => $row) {
            if ($this->copy_user == 1) {
                $this->set_responsable('ttareas', $id);
                for ($year= $row['year_init']; $year <= $row['year_end']; $year++) {
                    $this->set_responsable("tproceso_eventos_$year", $id, "id_tarea");
                }
            }

            $user_in= $this->find_tarea($id);
            if (!$user_in) {
                $row['id_tarea']= $row['id'];
                $row['id_tarea_code']= $row['id_code'];

                $row['id']= null;
                $row['id_code']= null;

                $this->insert_tusuario_eventos($row, $row['year_init'], $row['year_end']);
            }

            $r= (float)(++$i) / $this->cant_task;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(0, "Copiando tareas ... $_r%", $r);
        }
    }

    private function fix_target_to_tematicas() {
        $i= 0;
        foreach ($this->array_tematicas as $id => $row) {
            if ($this->copy_user == 1)
                $this->set_responsable('ttematicas', $id);

            $this->insert_tasistencias($row);

            $r= (float)(++$i) / $this->cant_matter;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(0, "Copiando tematicas ... $_r%", $r);
        }
    }

    private function fix_tematicas() {
        /* tematica */
        $i= 0;
        $obj= new Ttematica($this->clink);
        $obj_assist= new Tasistencia($this->clink);

        $obj->SetIdUsuario($this->id_user_source);
        $obj_assist->SetIdUsuario($this->id_user_source);

        reset($this->array_tematicas);
        foreach ($this->array_tematicas as $row) {
            $obj->SetYear($row['year_init']);
            $obj->SetIdTematica($row['id']);
            $obj->set_id_tematica_code($row['id_code']);
            $obj->setUsuario('delete');

            $obj_assist->SetIdEvento($row['id_evento']);
            $obj_assist->set_id_evento_code($row['id_evento_code']);
            $obj_assist->eliminar();

            $r= (float)(++$i) / $this->cant_matter;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(0, "Quitando participación en tematicas ... $_r%", $r);
        }
    }

    private function fix_eventos() {
        /* evento */
        $obj= new Tevento($this->clink);
        $obj->SetIdUsuario($this->id_user_source);
        $obj->set_go_delete(_DELETE_PHY);

        $i= 0;
        reset($this->array_eventos);
        foreach ($this->array_eventos as $row) {
            $obj->SetYear($row['year_init']);
            $obj->SetIdEvento($row['id']);
            $obj->set_id_evento_code($row['id_code']);
            $obj->setUsuario('delete');
            $obj->update_reg('delete', _DELETE_PHY, $this->id_user_source);
        }
    }

    private function fix_auditotias() {
        /* auditoria */
        $i= 0;
        $obj= new Tauditoria($this->clink);
        $obj->SetIdUsuario($this->id_user_source);
        $obj->set_go_delete(_DELETE_PHY);

        reset($this->array_auditorias);
        foreach ($this->array_auditorias as $row) {
            $obj->SetYear($row['year_init']);
            $obj->SetIdAuditoria($row['id']);
            $obj->set_id_auditoria_code($row['id_code']);
            $obj->setUsuario('delete');
            $obj->update_reg('delete', _DELETE_PHY, $this->id_user_source);

            $r= (float)(++$i) / $this->cant_audit;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(0, "Quitando participación en actividades ... $_r%", $r);
        }
    }

    private function fix_tareas() {
        /* tarea */
        $i= 0;
        $obj= new Ttarea($this->clink);
        $obj->SetIdUsuario($this->id_user_source);
        $obj->set_go_delete(_DELETE_PHY);

        reset($this->array_tareas);
        foreach ($this->array_tareas as $row) {
            for ($year= $row['year_init']; $year <= $row['year_end']; $year++) {
                $obj->SetYear($year);
                $obj->SetIdTarea($row['id']);
                $obj->set_id_tarea_code($row['id_code']);
                $obj->setUsuario('delete');
                $obj->update_reg('delete', _DELETE_PHY, $this->id_user_source);
            }
            $r= (float)(++$i) / $this->cant_task;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(0, "Quitando participación en tareas ... $_r%", $r);
        }
    }

    public function apply() {
        global $id_user_source;
        global $id_user_target;
        global $fecha;
        global $copy_user;

        $this->id_user_source= $id_user_source;
        $this->id_user_target= $id_user_target;
        $this->fecha= date2odbc($fecha);
        $this->copy_user= $copy_user;

        $this->cant_event= $this->get_teventos();
        $this->cant_audit= $this->get_tauditorias();
        $this->cant_task= $this->get_ttareas();
        $this->cant_matter= $this->get_ttematicas();

        $this->fix_target_to_eventos();
        $this->fix_target_to_auditorias();
        $this->fix_target_to_tematicas();
        $this->fix_target_to_tareas();

        if ($this->copy_user == 1) {
            $this->fix_eventos();
            $this->fix_auditotias();
            $this->fix_tareas();
            $this->fix_tematicas();

            $this->set_indicadores();
        }
    }
}
?>

<?php
$_SESSION['in_javascript_block']= false;

$interface= new Tinterface($clink);
$interface->apply();

$_SESSION['in_javascript_block']= null;
?>

