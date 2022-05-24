<?php

/* 
 * Copyright 2017 
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */


session_start();
include_once "../../../php/setup.ini.php";
include_once "../../../php/class/config.class.php";

$_SESSION['debug']= 'no';

include_once "base.interface.php";
?>

<?php include "../../../php/_header.interface.inc"; ?>

<?php
class Tinterface extends Tbase_interface {

    public function __construct($clink) {
        Tbase_interface::__construct($clink); 

        $this->clink= $clink;
    }
 
    public function apply() {
        $this->obj= new Tpersona($this->clink);
      
        if (!empty($this->id)) {
            $this->obj->Set($this->id);
            
            $this->id_code = $this->obj->get_id_code();
            $this->id_persona = $this->id;
            $this->id_persona_code = $this->id_code;
        }

        $this->obj->set_cronos($this->cronos);
        $this->obj->action = $this->action;
        $this->obj->SetIdUsuario($_SESSION['id_usuario']);
        $this->obj->SetYear($this->year);
       
        if ($this->action == 'add' || $this->action == 'update') {
            $this->obj->SetNoIdentidad(trim($_POST['noIdentidad']));

            $nombre= trim($_POST['nombre']);
            $this->obj->SetNombre($nombre, false);

            $cargo= trim($_POST['cargo']);
            $this->obj->SetCargo($cargo);
            $id_organismo= trim($_POST['id_organismo']);
            $this->obj->SetIdOrganismo($id_organismo);
            
            $id_prov= $_POST['provincia'];
            $this->obj->SetProvincia($id_prov);
            $id_mcpo= $_POST['municipio'];
            $this->obj->SetMunicipio($id_mcpo);
            $this->obj->SetDireccion(trim($_POST['direccion']));
            $lugar= trim($_POST['lugar']);
            $this->obj->SetLugar($lugar);

            $this->obj->SetTelefono(trim($_POST['telefono']));
            $this->obj->SetMovil(trim($_POST['movil'])); 
            $email= trim($_POST['email']);
            $this->obj->SetMail_address($email); 
        }
  
        if ($this->action == 'add') {
            $this->error= $this->obj->add();
            
            if (is_null($this->error)) {
                $this->id_persona= $this->obj->GetIdPersona();
                $this->id_persona_code= $this->obj->get_id_persona_code();
            }
        }
        
        if ($this->action == 'update') {
            $this->error= $this->obj->update();
        }        

        if ($this->action == 'edit') {
            $this->obj->Set();
        }
        
        if ($this->action == 'delete') {
            $this->error= $this->obj->eliminar();
        }
        
        $url_page = "../php/persona.interface.php";
        $url_page .= "?id=$this->id&signal=$this->signal&action=$this->action&id_mcpo=$id_mcpo&id_prov=$id_prov";
        $url_page .= "&id_person=$this->id_person&id_proceso=$this->id_proceso";
        $url_page .= "&exect=$this->action&menu=$this->menu";

        add_page($url_page, $this->action, 'i');
        
        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            $url= "&id=$this->id&signal=$this->signal&id_mcpo=$id_mcpo&id_prov=$id_prov";
            $url.= "&id_organismo=$id_organismo&lugar=". urlencode($lugar);
            
            if (is_null($this->error)) {
                $_SESSION['obj'] = serialize($this->obj);

                if (($this->action == 'add' || $this->action == 'update') || $this->action == 'delete') {
                    $action= $this->action == 'list' ? 'list' : 'edit';
            ?>  
                    self.location.href = '../form/lperson.php?action=<?=$action?><?=$url?>';
                <?php     
                }

                if ($this->action == 'edit' || $this->action == 'list') {
                    if ($this->action == 'edit') $this->action = 'update';
                    ?>
                        self.location.href = '../form/fperson.php?action=<?=$this->action?>&id=<?=$this->id?>';
                    <?php
                }
            } else {
                $this->obj->error = $error;
                $_SESSION['obj'] = serialize($this->obj);
                ?>
                    self.location.href = '<?php prev_page($this->error); ?>';
                <?php
        }   }
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
        $interface = new Tinterface($clink);
        $interface->apply();
        ?>
    });    
</script>        