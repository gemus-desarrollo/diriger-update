<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "config.inc.php";
require_once _PHP_DIRIGER_DIR."config.ini";

require_once "interface.class.php";
require_once "class/base.class.php";
require_once "class/connect.class.php";

require_once "class/grupo.class.php";
require_once "class/usuario.class.php";
require_once "class/mail.class.php";


class Tinterface extends TplanningInterface {
    public function __construct($clink= null) {
        $this->clink= $clink;
        TplanningInterface::__construct($this->clink);
    }

    private function read_grupos() {
        $error= NULL;
        $obj_grp= new Tgrupo($this->clink);
        $result= $obj_grp->listar();	

        while ($row= $this->clink->fetch_array($result)) {
            $id= $_POST['multiselect-users_grp'.$row['id']];
            if (empty($id)) 
                continue;

            $obj_grp->cleanListaUser();
            $obj_grp->SetIdGrupo($row['_id']);

            $obj_grp->push2ListaUserGroup($row['_id'], true);
            $user_array= $obj_grp->get_list_user();

            foreach ($user_array as $array) {
                if (!array_key_exists($array['id'], $this->accept_mail_user_list)) {
                    $this->accept_mail_user_list[]= $array;
                }
            }
        }

        $this->accept_mail_user_list= array_unique((array)$this->accept_mail_user_list, SORT_REGULAR);
        unset($obj_grp);
    }

    private function read_usuarios() {
        $error = NULL;
        $user_ref_date = !is_null($this->user_date_ref) ? $this->user_date_ref : $this->fecha_fin;

        $obj_user = new Tusuario($this->clink);
        $obj_user->set_user_date_ref($user_ref_date);
        $result = $obj_user->listar();

        while ($row = $this->clink->fetch_array($result)) {
            $id = $_POST['multiselect-users_user' . $row['_id']];
            if (empty($id)) 
                continue;

            $array= array('id'=>$row['_id'], 'nombre'=>$row['nombre'], 'email'=>$row['email'],'cargo'=>$row['cargo'],
                'usuario'=>$row['usuario'], 'eliminado'=>$row['eliminado'], 'id_proceso'=>$row['id_proceso'],
                'id_proceso_code'=>$row['id_proceso_code']);

            if (!array_key_exists($row['_id'], (array)$this->accept_mail_user_list)) {
                $this->accept_mail_user_list[$row['_id']]= $array;
            }
        }

        unset($obj_user);
    }

    public function apply() { 
        $this->read_usuarios();
        $this->read_grupos();

        $obj_mail= new Tmail();

        $obj_mail->From= $_SESSION['email_app'];
        $obj_mail->FromName= "Sistema de Tableros de Control Diriger";

        if (is_null($_SESSION['email'])) 
            $obj_mail->AddReplyTo($_SESSION['email_app'], "Sistema de Tableros de Control Diriger");
        elseif (if_address_email($_SESSION['email'])) 
            $obj_mail->AddReplyTo($_SESSION['email'], $_SESSION['nombre'].' ('.$_SESSION['cargo'].')');

        reset($this->accept_mail_user_list);

        $obj_mail->ContentType= 'multipart/mixed';
        $obj_mail->Subject= $_POST['subject'];
        $obj_mail->Body= utf8_encode("Enviado a través del Sistema Informático Diriger ".date('Y-m-d H:i:s')."\n\n". $_POST['msg']."\n\n");
        $obj_mail->MsgHTML(nl2br($obj_mail->Body));

        if ($_FILES['attachment']["size"] > 0) 
            $obj_mail->AddAttachment($_FILES['attachment']['tmp_name'], $_FILES['attachment']['name']);

        foreach ($this->accept_mail_user_list as $user) {
            if ($user['id'] == $_SESSION['id_usuario']) 
                continue;
            if (is_null($user['email'])) 
                continue;

            $name= $user['nombre'].' ('.$user['cargo'].')';
            if (!if_address_email($user['email'])) 
                continue;

            $obj_mail->AddAddress($user['email'], $name);
            $error= $obj_mail->Send();
            $obj_mail->ClearAddresses();
        }

        if (!is_null($_POST['tomail'])) {
            $tomail= $_POST['tomail'];
            $array_mail= preg_split('/[;,]/', $tomail);

            foreach ($array_mail as $mail) {
                if (strchr($mail, '@') == false) 
                    continue;
                if (!if_address_email($mail)) 
                    continue;

                $obj_mail->AddAddress($mail, $mail);
                $error= $obj_mail->Send();
                $obj_mail->ClearAddresses();
            }
        }

        if ($error) {
        ?>
                <script language='javascript' type="text/javascript" charset="utf-8">window.close()</script>
        <?php
        } else {
            die("se ha producido un error");
        }
    }	
}


$interface= new Tinterface($clink);
$interface->apply();
?>
