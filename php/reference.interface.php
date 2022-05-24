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
require_once "interface.class.php";
require_once "class/reference.class.php";
require_once "class/peso.class.php";

require_once "class/code.class.php";

class Tinterface extends TplanningInterface {
    private $_item, $_item_sup;
    protected $id, $id_sup;

    public function __construct($clink= null) {
        $this->clink= $clink;
        TplanningInterface::__construct($this->clink);

        $this->_item= !empty($_POST['_item']) ? $_POST['_item'] : $_GET['_item'];
        $this->_item_sup= !empty($_POST['_item_sup']) ? $_POST['_item_sup'] : $_GET['_item_sup'];
        $this->id= !empty($_POST['id']) ? $_POST['id'] : $_GET['id'];
        $this->id_sup= !empty($_POST['id_sup']) ? $_POST['id_sup'] : $_GET['id_sup'];

        $radio_date= !is_null($_POST['_radio_date']) ? $_POST['_radio_date'] : $_GET['_radio_date'];
        if ($radio_date == 2) $this->year= null;
    }


    public function apply() {
        $this->obj= new Tpeso($this->clink);
        $this->set_reg_table('tusuario_tableros');

        if ($this->_item == 'indi') {
            if ($this->_item_sup == 'ind') $this->obj->empty_tref_indicadores($this->id_sup, $this->id, $this->year);
            if ($this->_item_sup == 'prog') $this->obj->empty_tref_programas($this->id_sup, $this->id, $this->year);
            if ($this->_item_sup == 'per') $this->obj->empty_set_null_perspectiva($this->id_sup, $this->id, $this->year);
        }

        if ($this->_item == 'ind') {
            if ($this->_item == 'obj') $this->obj->empty_tobjetivo_inductores($this->id_sup, $this->id, $this->year);
        }

        if ($this->_item == 'obj') {
            if ($this->_item_sup == 'obj_sup' || $this->_item_sup == 'obj') $this->obj->SetIfObjetivoSup();
            if ($this->_item_sup == 'pol') $this->obj->SetIfObjetivoSup(false);
            $this->obj->empty_tpolitica_objetivos($this->id_sup, $this->id, $this->year);
        }

        $url_page= "../php/reference.interface.php";
        $url_page.= "?id=$this->id&signal=$this->signal&action=$this->action&year=$this->year&month=$this->month&day=$this->day";
        $url_page.= "&id_proceso=$this->id_proceso&exect=$this->action&menu=$this->menu";

        add_page($url_page, $this->action, 'i');

		if (is_null($this->obj->error)) {
        ?>
                <script language='javascript' type="text/javascript" charset="utf-8">self.location.href='<?php next_page();?>'</script>
        <?php
        }  else {
             $this->obj->redirect= 'fail';
          ?>
                 <script language='javascript' type="text/javascript" charset="utf-8">self.location='../form/ftablero.php?action=<?php echo $this->obj->action.'&month='.$this->month.'&year='.$this->year.'&day='.$this->day.'&id='.$this->id.'&id_proceso='.$this->id_proceso; ?>'</script>
          <?php
		}

	}
}


$interface= new Tinterface($clink);
$interface->apply();
?>
