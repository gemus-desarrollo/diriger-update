<?php
/**
 * User: Geraudis Mustelier
 * Date: 12/01/2021
 * Time: 13:25
 */

require_once 'repare_ldap.class.php';

class Trepare_ldap_list extends Trepare_ldap {
    public $array_usuarios;

    private $array_list_usuarios;
    private $array_logins;


    
    public function __construct($clink) {
        global $config;
        $this->total_tables= 65;
        Tbase::__construct($clink);

        $this->array_usuarios= array();
        $this->array_logins= array();
        $this->array_entities= array();
    }    
    
    public function report_deleted() {
        global $array_procesos_entity;

        $usuario= $this->array_list_usuarios[$this->id_user_delete]['usuario'];
        $nombre= textparse($this->array_list_usuarios[$this->id_user_delete]['nombre']);
        $entity= $array_procesos_entity[$this->array_list_usuarios[$this->id_user_delete]['id_entity']]['nombre'];

        echo "<div class=\"row col-12 mt-1\">
            <div class=\"col-5 alert alert-danger\">
                <strong>Usuario:</strong> $usuario
                <strong>Nombre:</strong> $nombre<br/>
                <strong>Entidad:</strong> $entity
            </div>";

        $usuario= $this->array_list_usuarios[$this->id_user_fix]['usuario'];
        $nombre=textparse($this->array_list_usuarios[$this->id_user_fix]['nombre']);
        $entity= $array_procesos_entity[$this->array_list_usuarios[$this->id_user_fix]['id_entity']]['nombre'];

        echo "   <div class=\"col-2 align-content-center\">
                =====>>>
            </div>
            <div class=\"col-5 alert alert-success\">
                <strong>Usuario:</strong> $usuario
                <strong>Nombre:</strong> $nombre</br>
                <strong>Entidad:</strong> $entity
            </div>        
        </div>";
    }

    public function init_list() {
        global $array_procesos_entity;

        $sql= "select * from tusuarios";
        $result= $this->clink->query($sql);
        
        $i= 0;
        $j= 0;
        while ($row= $this->clink->fetch_array($result)) {
            if ($row['id'] == _USER_SYSTEM)
                continue;
            if (empty($row['nombre']) || empty($row['usuario']))
                continue;

            $row['id_entity']= $array_procesos_entity[$row['id_proceso']]['id_entity'];
            $this->array_list_usuarios[$row['id']]= $row; 

            $usuario= strtolower($row['usuario']);
            $this->array_usuarios[$usuario][]= $row;
            if (!$this->array_logins[$usuario])
                $this->array_logins[$usuario]= $usuario;
        }   
    }

    public function fix_lista_usuario() {
            foreach ($this->array_logins as $usuario) {
                if (empty($this->array_usuarios[$usuario]))
                    continue;

                if (count($this->array_usuarios[$usuario]) > 1)
                    $this->fix_usuario($this->array_usuarios[$usuario]);
        }
    }

    private function fix_usuario($usuarios) {
        $array_id_usuarios= array();
        $cronos= null;
        $id_user_fixed= null;
        $this->if_secure_mix_cargo= false;

        foreach ($usuarios as $user) {
            $fixed= false;

            if (empty($cronos)) {
                $cronos= $user['cronos'];
                $id_user_fixed= $user['id'];
                $fixed= true;
            }

            if (strtotime($cronos) > strtotime($user['cronos']) && (!empty($user['cargo']))) {
                $user_fixed= $this->array_list_usuarios[$user['id']];
                $user_delete= $this->array_list_usuarios[$id_user_fixed];
                
                if (($user_fixed['id_entity'] == $user_delete['id_entity'])
                    || ($user_fixed['id_entity'] != $user_delete['id_entity'] 
                        && (!empty($user_fixed['email']) && $user_fixed['email'] == $user_delete['email']))) {
                        $array_id_usuarios[]= $id_user_fixed;
                        $this->if_secure_mix_cargo= true;
                }  

                $cronos= $user['cronos'];
                $id_user_fixed= $user['id'];
                $fixed= true;
            }    

            if (!$fixed) {
                $user_fixed= $this->array_list_usuarios[$id_user_fixed];
                $user_delete= $this->array_list_usuarios[$user['id']];

                if (($user_fixed['id_entity'] == $user_delete['id_entity'])
                    || ($user_fixed['id_entity'] != $user_delete['id_entity'] 
                        && (!empty($user_fixed['email']) && $user_fixed['email'] == $user_delete['email']))) {
                    $array_id_usuarios[]= $user['id'];

                    if ((strtotime($cronos) < strtotime($user['cronos'])) && !empty($user['cargo']))
                        $this->if_secure_mix_cargo= true; 
            }   }    
        }

        if (count($array_id_usuarios))
            $this->mix_usuarios($array_id_usuarios, $id_user_fixed);
    } 

    private function mix_usuarios($array_id_usuarios, $id_user_fixed) {
        $this->id_user_fix= $id_user_fixed;

        foreach ($array_id_usuarios as $id_usuario) {
            $this->id_user_delete= $id_usuario;
  
            $this->repare();
            $this->delete_user();
            $this->report_deleted();
        }
    }
}