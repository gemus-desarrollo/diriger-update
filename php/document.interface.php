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
require_once "class/proceso.class.php";
require_once "class/document.class.php";
require_once "class/mail.class.php";

require_once "class/code.class.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !empty($_GET['ajax_win']) ? $_GET['ajax_win'] : false;
if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class Tinterface extends TplanningInterface {
    private $to_print;
    public $filename;
    public $url;

    public function __construct($clink= null) {
        $this->clink= $clink;
        TplanningInterface::__construct($clink);

        $this->id_evento= !empty($_POST['id_evento']) ? $_POST['id_evento'] : $_GET['id_evento'];
        $this->id_tarea= !empty($_POST['id_tarea']) ? $_POST['id_tarea'] : $_GET['id_tarea'];
        $this->id_auditoria= !empty($_POST['id_auditoria']) ? $_POST['id_auditoria'] : $_GET['id_auditoria'];
        $this->id_proyecto= !empty($_POST['id_proyecto']) ? $_POST['id_proyecto'] : $_GET['id_proyecto'];
        $this->id_nota= !empty($_POST['id_nota']) ? $_POST['id_nota'] : $_GET['id_nota'];
        $this->id_riesgo= !empty($_POST['id_riesgo']) ? $_POST['id_riesgo'] : $_GET['id_riesgo'];
        $this->id_politica= !empty($_POST['id_politica']) ? $_POST['id_politica'] : $_GET['id_politica'];
        $this->id_requisito= !empty($_POST['id_requisito']) ? $_POST['id_requisito'] : $_GET['id_requisito'];
        $this->id_indicador= !empty($_POST['id_indicador']) ? $_POST['id_indicador'] : $_GET['id_indicador'];

        $this->id_proceso_code= !empty($this->id_proceso) ? get_code_from_table('tprocesos', $this->id_proceso) : null;
        $this->id_evento_code= !empty($this->id_evento) ? get_code_from_table('teventos', $this->id_evento) : null;
        $this->id_tarea_code= !empty($this->id_tarea) ? get_code_from_table('ttareas', $this->id_tarea) : null;
        $this->id_auditoria_code= !empty($this->id_auditoria) ? get_code_from_table('tauditorias', $this->id_auditoria) : null;
        $this->id_proyecto_code= !empty($this->id_proyecto) ? get_code_from_table('tproyectos', $this->id_proyecto) : null;
        $this->id_nota_code= !empty($this->id_nota) ? get_code_from_table("tnotas", $this->id_nota, $clink) : null;
        $this->id_riesgo_code= !empty($this->id_riesgo) ? get_code_from_table("triesgos", $this->id_riesgo, $clink) : null;
        $this->id_politica_code= !empty($this->id_politica) ? get_code_from_table("tpoliticas", $this->id_politica, $clink) : null;
        $this->id_requisito_code= !empty($this->id_requisito) ? get_code_from_table("tlista_requisitos", $this->id_requisito, $clink) : null;
        $this->id_indicador_code= !empty($this->id_indicador) ? get_code_from_table("tindicadores", $this->id_indicador, $clink) : null;

        $this->signal= !empty($_GET['signal']) ? $_GET['signal'] : null;
    }

    private function set_usuarios() {
        reset($this->accept_user_list);
        reset($this->denied_user_list);

        foreach ($this->accept_user_list as $array) {
            $this->obj->SetIdUsuario($array['id']);
            $error= $this->obj->setUsuario('add');
            if (!empty($error))
                break;
        }

        $this->error= $error;
        if (!empty($error))
            return;

        foreach ($this->denied_user_list as $array) {
            $this->obj->setIdUsuario($array['id']);
            $error= $this->obj->setUsuario('delete');
            if (!empty($error))
                break;
        }

        $this->error= $error;
    }

    private function set_grupos() {
        reset($this->accept_group_list);
        reset($this->denied_group_list);

        foreach ($this->accept_group_list as $array) {
            $this->obj->SetIdGrupo($array['id']);
            $error= $this->obj->setGrupo('add');
            if (!empty($error))
                break;
        }

        $this->error= $error;
        if (!empty($error))
            return;

        foreach ($this->denied_group_list as $array) {
            $this->obj->SetIdGrupo($array['id']);
            $error= $this->obj->setGrupo('delete');
            if (!empty($error))
                break;
        }

        $this->error= $error;
    }

    private function sendmail() {
        $target= null;
        $obj_mail= new Tmail();
        $obj_mail->From= $_SESSION['email_app'];
        $obj_mail->FromName= "Sistema de Tableros de Control Diriger";

        if (is_null($_SESSION['email']))
            $obj_mail->AddReplyTo($_SESSION['email_app'], "Sistema de Tableros de Control Diriger");
        elseif (if_address_email($_SESSION['email']))
            $obj_mail->AddReplyTo($_SESSION['email'], $_SESSION['nombre'].' ('.$_SESSION['cargo'].')');

        reset($this->accept_mail_user_list);

        $obj_mail->ContentType= 'multipart/mixed';
        $obj_mail->Subject= "Documento enviado a través del Sistema Informático Diriger ".date('Y-m-d H:i:s');

        $obj_mail->Body= utf8_encode("Enviado a través del Sistema Informático Diriger ".date('Y-m-d H:i:s')."\n\n");
        $obj_mail->Body.= utf8_encode($this->descripcion);
        $obj_mail->MsgHTML(nl2br($obj_mail->Body));

        if ($_FILES['file_doc-upload']["size"] > 0)
            $obj_mail->AddAttachment(_UPLOAD_DIRIGER_DIR.$this->url, $this->filename);

        foreach ($this->accept_mail_user_list as $user) {
            if ($user['id'] == $_SESSION['id_usuario'])
                continue;
            $target= $user['email'];
            if (is_null($target))
                continue;

            $name= $user['nombre'].', '.$user['cargo'];
            if (!if_address_email($target))
                continue;

            $obj_mail->ClearAddresses();
            $obj_mail->AddAddress($target, $name);
            $result= $obj_mail->Send();
            if (!$result)
                break;
        }

        return !$result ? "No ha salido el correo electronico {$name} correo:{$target}" : null;
    }

    public function apply() {
        $this->obj= new Tdocumento($this->clink);

        if (!empty($this->id) && ($this->action != 'edit' && $this->action != 'list')) {
            $this->obj->Set($this->id);
            $this->id_code= $this->obj->get_id_code();

            $this->id_documento= $this->id;
            $this->id_documento_code= $this->id_code;
        }

        if ($this->action != 'delete' && $this->action != 'edit') {
            $this->obj->SetYear($this->year);
            $this->obj->SetMonth(!empty($this->month) && (int)$this->month > 0 ? $this->month : null);

            $this->obj->SetIdProceso($this->id_proceso);
            $this->obj->set_id_proceso_code($this->id_proceso_code);

            $this->obj->SetIdEvento($this->id_evento);
            $this->obj->set_id_evento_code($this->id_evento_code);

            $this->obj->SetIdTarea($this->id_tarea);
            $this->obj->set_id_tarea_code($this->id_tarea_code);

            $this->obj->SetIdAuditoria($this->id_auditoria);
            $this->obj->set_id_auditoria_code($this->id_auditoria_code);

            $this->obj->SetIdProyecto($this->id_proyecto);
            $this->obj->set_id_proyecto_code($this->id_proyecto_code);

            $this->obj->SetIdNota($this->id_nota);
            $this->obj->set_id_nota_code($this->id_nota_code);

            $this->obj->SetIdRiesgo($this->id_riesgo);
            $this->obj->set_id_riesgo_code($this->id_riesgo_code);

            $this->obj->SetIdRequisito($this->id_requisito);
            $this->obj->set_id_requisito_code($this->id_requisito_code);

            $this->obj->SetIdIndicador($this->id_indicador);
            $this->obj->set_id_indicador_code($this->id_indicador_code);

            $this->obj->SetIdUsuario($_SESSION['id_usuario']);
            $this->id_responsable= $_POST['usuario_doc'];
            $this->obj->SetIdResponsable($_POST['usuario_doc']);
            
            $this->descripcion= trim($_POST['descripcion_doc']);
            $this->obj->SetDescripcion($this->descripcion);
            $this->obj->SetKeywords(trim($_POST['keywords_doc']));

            $this->obj->set_cronos($this->cronos);
        }

        if ($this->action == 'add') {
            $this->error= $this->obj->upload();

            if (is_null($this->error)) {
                $this->error= $this->obj->add();

                if (is_null($this->error)) {
                    $this->id_documento= $this->obj->GetIdDocumento();
                    $this->id_documento_code= $this->obj->get_id_documento_code();
        }   }   }

        if ($this->action == 'update') {
            $this->error = $this->obj->update();
        }

        if (is_null($this->error) && ($this->action == 'add' || $this->action == 'update')) {
            $this->filename= $this->obj->filename;
            $this->url= $this->obj->url;

            $this->error = $this->obj->add_ref();
        }

        if (is_null($this->error) && ($this->menu == 'document' && ($this->action == 'add' || $this->action == 'update'))) {
            $this->set_usuarios_array();
            $this->set_grupos_array();

            $this->set_usuarios();
            $this->set_grupos();
        }

        if (is_null($this->error) && ($this->menu == 'document' && ($_POST['sendmail'] && ($this->action == 'add' || $this->action == 'update')))) {
            $this->sendmail();
        }

        if ($this->action == 'delete') {
            $this->error= $this->obj->eliminar();
        }

        $keywords= $_GET['keywords'];

        if ($this->menu == 'document') {
            $url= "?action=add&id_evento=$this->id_evento&id_proceso=$this->id_proceso&id_auditoria=$this->id_auditoria";
            $url.= "&id_proyecto=$this->id_proyecto&id_politica=$this->id_politica&signal=$this->signal&keywords=$keywords";
            $url.= "&id_indicador=$this->id_indicador&id_nota=$this->id_nota&id_riesgo=$this->id_riesgo";
            ?>
                self.location.href= '../form/fdocument.php<?=$url?>&error=<?=urlencode($this->error)?>';
            <?php
        }

        if ($this->menu == 'lista') {
           ?>
                list_doc();
                close_doc();
           <?php
        }
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
            $interface= new Tinterface($clink);
            $interface->apply();
            ?>

        <?php if (!$ajax_win) { ?>
        });
        <?php } ?>
    </script>
<?php } ?>