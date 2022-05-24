<?php

/**
 * Copyright 2019
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */

session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

$_SESSION['debug']= 'no';
$_SESSION['trace_time']= 'no';

require_once "config.inc.php";
require_once "class/connect.class.php";
require_once "class/time.class.php";

require_once "class/proceso.class.php";
require_once "class/evento.class.php";
require_once "class/plantrab.class.php";

require_once "class/evento_numering.class.php";
require_once "class/tipo_evento.class.php";
?>

<?php
$ajax_win= true;
require_once "_header.interface.inc";
?>

<?php
class TEnumInterfase extends Tbase_planning {
    private $obj;
    public $signal;

    public function __construct($clink = null) {
        parent::__construct($clink);

        $this->id_plan= !empty($_GET['id_plan']) ? $_GET['id_plan'] : $_POST['id_plan'];

        $this->signal= !empty($_GET['signal']) ? $_GET['signal' ] : "anual_plan";
        $this->year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
        $this->month= !empty($_GET['month']) ? $_GET['month'] : date('m');

        $this->if_numering= !is_null($_GET['if_numering']) ? $_GET['if_numering'] : _ENUMERACION_MANUAL;
        $this->id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];
        $this->id_proceso_asigna= !empty($_GET['id_proceso_asigna']) ? $_GET['id_proceso_asigna'] : $_SESSION['local_proceso_id'];
    }

    public function apply() {
        $this->obj= new Tevento_numering($this->clink);
        $this->obj->SetIdPlan($this->id_plan);

        $this->obj->SetYear($this->year);
        $this->obj->SetMonth($this->signal == 'anual_plan' ? null : $month);
        $this->obj->SetDay(null);

        $this->obj->signal= $this->signal;
        $this->obj->toshow= $this->signal == 'anual_plan' ? 2 : 1;
        $this->obj->SetIdProceso($this->id_proceso);
        $this->obj->set_id_proceso_asigna($this->id_proceso_asigna);

        $this->obj->SetIfNumering($this->if_numering);
        $this->obj->build_numering();

        if ($this->signal == 'anual_plan' && $this->id_proceso == $_SESSION['local_proceso_id']) {
            $this->obj->SetIfNumering(_ENUMERACION_MANUAL);
            $this->obj->update_if_numering();
        }

        $this->error= $this->obj->error;

        if (is_null($this->error)) {
        ?>
            $('#if_numering').val(<?=_ENUMERACION_MANUAL?>);
            cerrar();

        <?php  } else { ?>
            cerrar("<?= $this->error?>");
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
    $(document).ready(function() {
        <?php
        $interface= new TEnumInterfase($clink);
        $interface->apply();
        ?>
    });
</script>




