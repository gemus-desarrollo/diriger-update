<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
include_once "../../../setup.ini.php";
include_once "../../../class/config.class.php";

include_once "base.interface.php";
?>

<?php include "../../../php/_header.interface.inc"; ?>

<?php
class Tinterface extends Tbase {
    public $menu;
    private $obj;
    
    protected $codigo;
    protected $id_organismo;
    protected $id_organismo_code;

    public function Tinterface($clink) {
        $this->clink= $clink;

        $this->id= !empty($_GET['id']) ? $_GET['id'] : $_POST['id'];
        $this->action= !empty($_GET['action']) ? $_GET['action'] : $_POST['exect'];
        $this->menu= !empty($_GET['menu']) ? $_GET['menu'] : $_POST['menu'];

        $this->obj_code= new Tcode($this->clink);
    }

    public function apply() {
        $this->obj= new Torganismo($this->clink);

        if (!empty($this->id)) {
            $this->obj->SetIdOrganismo($this->id);
            $error= $this->obj->Set();
            $this->id_code= $this->obj->get_id_code();
        }

        if ($this->action == 'add' || $this->action == 'update') {
            $this->obj->SetNombre(trim($_POST['nombre']), false);
            $this->obj->SetCodigo(trim($_POST['codigo']));
            $this->obj->SetDescripcion(trim($_POST['descripcion']));
            $this->obj->SetUseAnualPlan($_POST['use_anual_plan']);
        }

        if ($this->action == 'add') {
            $error= $this->obj->add();

            if (is_null($error)) {
                $this->id= $this->obj->GetIdOrganismo();
                $this->id_organismo= $this->id;
                $this->id_code= $this->obj->get_id_code();
                $this->id_organismo_code= $this->id_code;
            }
        }

        if ($this->action == 'update') {
            $error= $this->obj->update();
        }    
        if ($this->action == 'delete')	{
            $error= $this->obj->eliminar();  
        }
        if ($this->action == 'edit' || $this->action == 'list') {
            $error= $this->obj->Set();
        }

        $url_page= "../php/organismo.interface.php";
        $url_page.= "?id=$this->id&signal=$this->signal&action=$this->action&nombre=$this->nombre&codigo=$this->codigo";
        $url_page.= "&descripcion=$this->descripcion&exect=$this->action&menu=$this->menu"; 
        
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
                if ($this->action == 'edit') $this->action= 'update';
            ?>
                self.location='../form/forganismo.php?action=<?= $this->action?>#<?= $this->id; ?>';

            <?php } } else {		 
                $this->obj->error= $error;
                $_SESSION['obj']= serialize($this->obj);
            ?>
                self.location.href='<?php prev_page($error);?>';

            <?php	 
            }	
    }  }	
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
        $interface = new Tinterface($clink);
        $interface->apply();
        ?>
    });    
</script>       