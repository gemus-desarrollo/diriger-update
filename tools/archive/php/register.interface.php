<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


session_start();
include_once "../../../php/setup.ini.php";
include_once "../../../php/class/config.class.php";

$_SESSION['debug']= 'no';

include_once "base.interface.php";
?>

<?php 
global $using_remote_functions;
$ajax_win= !empty($_GET['ajax_win']) ? true : false;
if (is_null($using_remote_functions) && !$ajax_win) 
    include "../../../php/_header.interface.inc"; 
?>

<?php
class Tinterface extends Tbase_interface {
    private $id_ref;
    private $id_ejecutante;
    
    public function __construct($clink) {
        Tbase_interface::__construct($clink); 
        $this->clink= $clink;
        
        $this->id_ref= !empty($_GET['id_ref']) ? $_GET['id_ref'] : null;
        $this->id_ejecutante= !empty($_GET['id_ejecutante']) ? $_GET['id_ejecutante'] : null;
        $this->numero= !empty($_GET['numero']) ? $_GET['numero'] : null;
        $this->numero_keywords= !empty($_GET['numero_keywords']) ? $_GET['numero_keywords'] : null;
        $this->keywords= !empty($_GET['keywords']) ? urldecode($_GET['keywords']) : null;
    }
    
    private function eliminar() {
        $this->obj_ref->SetIdArchivo($this->id);
        $this->obj_ref->id_ref_archivo= $this->id_ref;
        $this->obj_ref->eliminar();
        
        $this->obj_ref->id_ref_archivo= null;
        $this->obj_ref->SetIdUsuario(null);
        $this->obj_ref->SetIdGrupo(null);
        $this->obj_ref->SetIdPersona(null);
        
        $this->obj_ref->if_sender= $this->if_output ? false : true;
        
        $this->error= $this->obj->eliminar();

        if (is_null($this->error)) {
            $this->obj_code->reg_delete('tarchivos','id_code',$this->id_archivo_code);

            if (!empty($this->id_evento))
                $this->obj_code->reg_delete('teventos','id_code', $this->id_evento_code);
        }  
    }
    
    protected function setReg_archivo($i) {
        $id_archivo= $_POST['id_'.$i];
        $id_archivo_code= $_POST['id_code_'.$i];
        $id_usuario= $_POST['id_usuario_'.$i];

        if (isset($obj)) unset($obj);
        $obj_archivo= new Tarchivo($this->clink);
        $obj_archivo->SetYear($this->year);
        
        $obj_archivo->SetIdArchivo($id_archivo);
        $obj_archivo->set_id_archivo_code($id_archivo_code);              

        $obj_archivo->SetIdUsuario($id_usuario);
        $row= $obj_archivo->getReg();

        if (strtotime($row['cronos']) != strtotime(time2odbc($_POST['time_'.$i]))) {
            $obj_archivo->SetCumplimiento($_POST['cumplimiento_'.$i]);
            $obj_archivo->SetObservacion($_POST['observacion_'.$i]);
            $obj_archivo->SetFecha(time2odbc($_POST['time_'.$i]));
            $obj_archivo->addReg();
        }   
    }        
     
    protected function setReg_evento() {
        $cant= $_POST['cant_'];
        
        for ($i= 1; $i <= $cant; ++$i) {
            if ($_POST['tab_'.$i] == 1) 
                continue;
            
            $id_archivo= $_POST['id_'.$i];
            $id_archivo_code= $_POST['id_code_'.$i];            
            $id_evento= $_POST['id_evento_'.$i];
            $id_evento_code= $_POST['id_evento_code_'.$i];
            $id_usuario= $_POST['id_usuario_'.$i];
            
            if (empty($id_evento)) {
                $this->setReg_archivo($i);
                continue;
            }
            if (isset($obj_register)) unset($obj_register);
            $obj_register= new Tregister_planning($this->clink);
            
            $obj_register->SetYear($this->year);
            $obj_register->SetIdEvento($id_evento);
            $obj_register->set_id_evento_code($id_evento_code);
            $obj_register->SetIdArchivo($id_archivo);
            $obj_register->set_id_archivo_code($id_archivo_code);
            
            $obj_register->SetIdUsuario($id_usuario);
            $row= $obj_register->get_last_reg();

            if (strtotime($row['cronos']) != strtotime(time2odbc($_POST['time_'.$i]))) {
                $obj_register->SetCumplimiento($_POST['cumplimiento_'.$i]);
                $obj_register->SetObservacion($_POST['observacion_'.$i]);
                $obj_register->SetFecha(time2odbc($_POST['time_'.$i]));
                $obj_register->add_cump();
            } 
        }       
    }
    
    public function apply() {
        if (!empty($this->id)) {
            $this->obj->Set($this->id);
            
            $this->id_code = $this->obj->get_id_code();
            $this->id_archivo = $this->id;
            $this->id_archivo_code = $this->id_code;
            $this->id_evento= $this->obj->GetIdEvento();
            $this->id_evento_code= $this->obj->get_id_evento_code();   
            
            $this->if_output= $this->obj->GetIfOutput();
        }

        $this->obj->set_cronos($this->cronos);
        $this->obj->action = $this->action;
        $this->obj->SetIdUsuario($_SESSION['id_usuario']);        
 
        if ($this->action == 'delete') {
            $this->eliminar();
        }
        
        if ($this->action == 'add' || $this->action == 'update') {
            $this->setReg_evento();
        }
        
        $url_page = "../php/register.interface.php";
        $url_page .= "?action=$this->action&exect=$this->action&year=$this->year&if_output=$this->if_output";
        $url_page .= "&id_organismo=$this->id_organismo&id_proceso=$this->id_proceso&keywords=". urlencode($this->keywords);
        $url_page .= "&menu=$this->menu&date_init=".urlencode($this->date_init)."&date_end=".urlencode($this->date_end);
        $url_page .= "&numero_keywords=$this->numero_keywords&id_ejecutante=$this->id_ejecutante";
        
        add_page($url_page, $this->action, 'i');
        
        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            $url= "&if_output=$this->if_output&id_organismo=$this->id_organismo&keywords=".urlencode($this->keywords);
            $url.= "&date_init=".urlencode($this->date_init)."&date_end=".urlencode($this->date_end)."&id_responsable=";
            $url.= "$this->id_responsable&cumplimiento=$this->cumplimiento&numero=$this->numero&id_ejecutante=$this->id_ejecutante";          
            
            if (is_null($error)) {
                $_SESSION['obj'] = serialize($this->obj);

                if (!$this->to_print) {
                    if ($this->action != "delete") {
            ?>        
                        self.location.href = '../form/faccords.php?action=add<?=$url?>';
                    <?php } else { ?>
                        self.location.href = '../form/lrecord.php?action=add<?=$url?>';
                    
                <?php } } else { ?>                       
                    var url= "../print/lconduce.php?<?=$url?>";
                    show_imprimir(url,"ESTADO DE CUMPLIMIENTO DE LAS INDICACIONES","width=800,height=600,toolbar=no,location=no,scrollbars=yes"); 
                    self.location.href = '<?php prev_page(); ?>';
                <?php   
                }
            } else {
                $obj->error = $error;
                ?>
                    self.location.href = '<?php prev_page($error); ?>';
                <?php
        }   }        
    }
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
            $interface = new Tinterface($clink);
            $interface->apply();
            ?>

        <?php if (!$ajax_win) { ?>            
        });
        <?php } ?>
    </script> 
<?php } ?>      