<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
$csfr_token_parent= "456def";

$_SESSION['_ctime']= 0;
$_SESSION['id_usuario']= -1;

require_once "../inc.php";
require_once "setup.ini.php";

require_once "config.inc.php";
require_once "class/base.class.php";
require_once "class/ldap.class.php";
require_once "class/radius.class.php";

require_once "../tools/alert/php/base_alert.class.php";
require_once "../tools/alert/php/alert.class.php";

require_once "class/usuario.class.php";
require_once "class/time.class.php";
require_once "class/proceso.class.php";
require_once "class/escenario.class.php";

require_once "../tools/dbtools/update.class.php";
require_once "../tools/dbtools/base_clean.class.php";
require_once "../tools/dbtools/clean.class.php";
require_once "../tools/lote/php/baseLote.class.php";

require_once "class/entity.class.php";
require_once "class/traza.class.php";

require_once "class/pop3/pop3.class.php";

$signal= !is_null($_GET['signal']) ? $_GET['signal'] : 'index';
$index_page= ($signal == 'index' || $signal == 'login') ? 'index.php' : 'tools/lote/index.php';
$index_page= _SERVER_DIRIGER.$index_page;

$id_proceso= !empty($_POST['proceso']) ? $_POST['proceso'] : null;

global $clink;

if (isset($_SESSION['_ctime']))
    unset($_SESSION['_ctime']);
if (isset($_SESSION['mac']))
    unset($_SESSION['mac']);
if (isset($_SESSION['ip_app']))
    unset($_SESSION['ip_app']);

$_SESSION['_ctime']= 0;
$_SESSION['id_usuario']= -1;

require_once "setup.ini.php";
require_once _PHP_DIRIGER_DIR."config.ini";
$_SESSION['_DB_SYSTEM']= _DB_SYSTEM;

require_once "class/connect.class.php";

require_once "class/init.class.php";
require_once "class/config.class.php";
require_once "class/config_ldap.class.php";
require_once "class/config_mail.class.php";

$_SESSION['_DB_SYSTEM']= !is_null($_SESSION['_DB_SYSTEM']) ? $_SESSION['_DB_SYSTEM'] : "mysql";
$_SESSION['id_usuario']= null;
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

        <?php require '../form/inc/_page_init.inc.php'; ?>

        <script type="text/javascript" src="../js/string.js"></script>

        <script type="text/javascript">
            var error= null;
            var expire_days= false;
            var update_sys_upgrade= false;
            var update_sys_update= false;
            var export_lote= false;
            var backup_sys= false;
            var purge_sys= false;
        </script>
    </head>

    <body class="form">
        <!--
        <img src="../img/premiun.jpg" width="100%" height="100%" border="none" />
        -->
        <?php
        if (!$clink) {
            $_SESSION['id_usuario']= null;
        ?>
            <script type="text/javascript" charset="utf-8">
                error= "Falló el intento de conexión al sistema. Por favor, consulte a su administrador de red.";
                self.location.href= '<?=$index_page?>?error='+encodeURIComponent(error);
            </script>
        <?php } ?>

        <?php
        $obj= null;
        $cant= null;

        $usuario= clean_string(trim($_POST['usuario']));
        $clave= clean_string(trim($_POST['clave']));
        $id_entity= $_POST['entity'];

        $obj= new Tusuario($clink);
        $obj->create_views();

        $obj->SetUsuario($usuario);
        $obj->SetClave($clave);
        $obj->SetIdProceso($id_proceso);
        $obj->SetIdEntity($id_entity);

        if ($signal != 'login' && empty($id_proceso)) {
            $found_user= $obj->if_unique_username();
            if (empty($found_user))
                $result= false;
            if ($found_user == 1) {
                $id_proceso= $obj->array_procesos[0]['id'];
                $obj->SetIdProceso($id_proceso);
            }
            if ($found_user > 1) {
               $_SESSION['obj']= serialize($obj);
        ?>
            <script type="text/javascript" charset="utf-8">
                error= "En el sistema existe más de un usuario con el mismo nombre de usuario. En este caso deberá especificar ";
                error+= "la unidad organizativa a la que usted pertenece.";
                self.location.href= '<?=_SERVER_DIRIGER ?>form/flogin.php?signal=<?=$signal?>&error='+encodeURIComponent(error);
            </script>
        <?php
            }
        }

        $result= null;
        $error= null;
        $obj_ldap= null;
        $cldap= null;
        $login_db= false;
        $found_user= false;
        $obj_entity= new Tentity($clink);
        $config= new _Tconfig_ldap($clink);

        if ($config->ldap_login)
            $obj_ldap = new Tldap();

        if ($signal != 'login' || ($signal == 'login' && !empty($id_proceso))) {
            if ($config->ldap_login && strtolower($usuario) != "administrador") {
                $cldap= $obj_ldap->login($usuario, $clave, $id_entity);
                $error= !is_null($cldap) ? $cldap[1] : null;
                $uid= is_null($error) ? $obj_ldap->get_uid() : null;

                if (is_null($error) ||  (!is_null($error) && $cldap[0] == _LDAP_SERVER_NO_USER)) {
                    $obj->SetUsuario($usuario);
                    $found_user= $obj->if_unique_username();
                }
            }

            if (!$config->block_no_ldap_login && (!$config->ldap_login || strtolower($usuario) == "administrador")) {
                $result= $obj->login();
                $login_db= true;

            } else {
                if ($config->ldap_login && strtolower($usuario) != "administrador" && (is_null($error)
                                            || (!is_null($error) && $cldap[0] == _LDAP_SERVER_NO_USER))) {
                    if (is_null($cldap)) {
                        $obj_prs= new Tproceso($clink);
                        $affected_user= 0;

                        if ($found_user) {
                            $array_NO_LOCALs= $obj_prs->listar_NO_LOCALs();
                            $array= array();
                            foreach ($array_NO_LOCALs as $id)
                                $array[]= $id['id'];
                            $affected_user= $obj->update_clave($array);
                        }

                        if ($uid) {
                            if (empty($affected_user) || $affected_user == -1) {
                                $_id_proceso= $obj_ldap->GetIdProceso();

                                $obj_prs->Set($_id_proceso);
                                $_id_proceso_code= $obj_prs->get_id_code();

                                $obj_user= new Tusuario($clink);

                                $obj_user->SetIdProceso($_id_proceso);
                                $obj_user->set_id_proceso_code($_id_proceso_code);
                                if ($config->mail_use_ldap)
                                    $obj_user->SetMail_address("{$usuario}@{$config->domain}");
                                $obj_user->SetUsuario($usuario);
                                if (!$config->block_no_ldap_login)
                                    $obj_user->SetClave($clave);
                                $obj_user->SetNoIdentidad(null);
                                $obj_user->SetCargo(null);
                                $obj_user->SetNombre($obj_ldap->GetNombre(), false);
                                $obj_user->set_user_ldap($obj_ldap->get_uid());
                                $obj_user->SetRole(_REGISTRO);

                                if (!$obj_entity->block_users()) 
                                    $error= $obj_user->add();
                                else 
                                    $error= "Se ha alcanzado el límite de usuarios permitidos en la licencia. Consulte al proveedor del sistema. ";
                                
                                if (is_null($error)) {
                                    $id_usuario= $obj_user->GetIdUsuario();
                                    $obj_prs->SetIdProceso($_id_proceso);
                                    $obj_prs->set_id_proceso_code($_id_proceso_code);
                                    $obj_prs->SetIdUsuario($id_usuario);
                                    $obj_prs->setUsuario();
                                } 
                            } else {
                                $uid= $obj->get_user_ldap(true);
                                !empty($uid) ? $obj->set_user_ldap($uid) : $obj->set_user_ldap($obj_ldap->get_uid(), true);
                }   }   }   }

                $result= null;
                if (strtolower($usuario) != "administrador" && (is_null($error) || (!is_null($error) && $cldap[0]))) {
                    if ((!is_null($cldap) && $cldap[0]) && !$config->block_no_ldap_login) {
                        $obj->SetIdProceso($id_proceso);
                        $result= $obj->login(true);
                    } else {
                        if (is_null($error)) {
                            $obj->set_user_ldap($obj_ldap->get_uid());
                            $obj->SetIdProceso(null);
                            $result= $obj->login(false);
                }   }   }

                if ($result && strtolower($usuario) != "administrador")
                    $error= null;
                if (is_null($result) && strtolower($usuario) == "administrador")
                    $error.= "El usuario administrador es un usuario local. Solo se pueden autenticar usuarios del Dominio.";
            }
        }

        $conectado= false;
        $bloqueado= false;
        $eliminado= false;
        $acc_sys= null;

        if ($result) {
            // temporal hasta que no se resuelva el onclose en mozilla
            // $conectado= $obj->GetConectado();
            $conectado= 0;
            $acc_sys= $obj->get_acc_sys();
            $eliminado= $obj->GetEliminado();
        }

        if (!$result && $login_db)
            $error.= "Nombre de usuario o clave incorrectos. ";
        if (!empty($conectado) && $obj->GetIdUsuario() != 1)
            $error.= "Usted ya está conectado al sistema desde otra estación. Desconectese o consulte a un ADMINISTRADOR o SUPERUSUARIO. ";
        if (is_null($error) && $acc_sys == 1)
            $error.= "Usted tiene bloqueado el acceso al sistema. Consulte a un ADMINISTRADOR o SUPERUSUARIO del sistema. ";
        if (is_null($error) && $acc_sys > 1) {
            $error.= "Su acceso ha sido bloqueado temporalmente. El Sistema se está actualizando en estos momentos. ";
            $error.= "Esta operación puede demorar varios minutos. Por favor, intente más tarde.";
        }
        if (is_null($error) && $eliminado)
            $error= "Usted ha sido eliminado del Sistema. Por favor, consulte a un ADMINISTRADOR o SUPERUSUARIO del sistema.";

        $obj->Set();
        $_SESSION['id_usuario']= $obj->GetIdUsuario();
        $_SESSION['usuario_proceso_id']= $obj->GetIdProceso();
        $_SESSION['nivel']= $obj->GetRole();
        $obj_prs= new Tproceso($clink);
        $obj_prs->Set($_SESSION['usuario_proceso_id']);

        if (($_SESSION['id_usuario'] != _USER_SYSTEM && $_SESSION['nivel'] != _GLOBALUSUARIO) 
                && ($obj_prs->GetIdEntity() != $_POST['entity'] && $_SESSION['usuario_proceso_id'] != $_POST['entity'])) {
            $error.= "Autenticación fallida. Por favor, revise su usuario, contraseña y unidad a la que intenta acceder. ";
        }
        
        $_SESSION['csfr_token']= null;
        if (!is_null($error)) {
        ?>
            <script type="text/javascript" charset="utf-8">
                self.location.href= '<?=$index_page?>?error=<?= urlencode($error) ?>';
            </script>
        <?php
        }
        $_SESSION['csfr_token']= '123abc';
        
        $obj->SetConectado();

        $obj_prs= new Tproceso($clink);
        $obj_prs->Set($id_entity);

        $obj_esc= new Tescenario($clink);

        $obj_init= new Tinit($clink);
        $obj_init->SetLocalProceso();
        $obj_init->SetCurrentProceso($id_entity);
        
        $obj_init->SetUsuario($obj, $obj_prs);
        $obj_init->SetEscenario($obj_esc);
        $obj_init->SetEntity($id_entity);

        $obj_traza= new Ttraza($clink);
        $obj_traza->SetYear(date('Y'));
        $obj_traza->SetIdProceso($id_entity);
        $obj_traza->add("ENTRADA");


        /* ------------------------------------------------------------------------------------------------------------------- */
        if ($_SESSION['id_usuario'] != _USER_SYSTEM) {
            $obj_alert= new Tbase_alert($clink);
            $obj_alert->SetIdUsuario($_SESSION['id_usuario']);
            $obj_alert->select_events();
        }

        /* ----------------------------------------------------------------------------------------------------------------*/
        $obj_sys= new Tupdate_sys($clink);
        $fecha_expire= $obj_sys->get_days_expire();
        $days= (int)s_datediff('d', date_create(date('Y-m-d H:i:s')), date_create($fecha_expire));

        if ($days <= 27) {
         ?>
            <script type="text/javascript">
                expire_days= <?=$days?>;
            </script>
        <?php }

        /* ------------------------------------------------------------------------------------------------------------------- */
        global $config;
        $config= new _Tconfig_mail($clink);

        $obj_sys= new Tclean($clink);
        $occupied= $obj_sys->if_occuped_system();

        /*--------------------------------------------------------------------------------------------------------------------*/
        if ($_SESSION['nivel'] >= _SUPERUSUARIO && is_null($occupied)) {
            $obj_sys= new Tupdate_sys($clink);
            if (empty($config->off_mail_server))
                $update= $obj_sys->test_updatemail_exists(false);
        ?>

            <script type="text/javascript">
                <?php if ($update == true && is_null($obj_sys->error)) { ?>
                    update_sys_upgrade= true;
                <?php } ?>
                <?php  if (!is_null($obj_sys->error)) { ?>
                    error= "<?=addslashes($obj_sys->error)?>";
                <?php } ?>
            </script>
        <?php
        }

        /*--------------------------------------------------------------------------------------------------------------------*/
        $obj_sys= new Tupdate_sys($clink);
        $fecha_script= $obj_sys->get_system('update');

        if ((is_null($fecha_script) || (int)strtotime(_UPDATE_DATE_DIRIGER) > (int)strtotime($fecha_script)) && (int)$_SESSION['nivel'] >= _SUPERUSUARIO) {
        ?>
            <script type="text/javascript">
                update_sys_update= true;
            </script>
        <?php }

        /*--------------------------------------------------------------------------------------------------------------------*/
        if (!empty($config->type_synchro)) {
            $obj_sys= new TbaseLote($clink);
            $fecha_synchro= $obj_sys->get_last_date_synchronization();

            $fecha_synchro= !is_null($fecha_synchro) ? $fecha_synchro : $_SESSION['current_year'].'-01-01';
            $now= date('Y-m-d H:i:s');
            $seconds= !empty($fecha_synchro) ? (int)s_datediff('s', date_create($fecha_synchro), date_create($now)) : 0;
            $array= split_time_seconds($seconds);

            $day_synchro= $array['d'];
            $hour_synchro= $array['h'];
            $min_synchro= $array['i'];

            if ((is_null($fecha_synchro) || ($seconds - (int)$config->time_synchro)/3600 > 24) && $_SESSION['nivel'] >= _SUPERUSUARIO) {
            ?>
                <script type="text/javascript">
                    export_lote= true;
                </script>
        <?php } }

        /*--------------------------------------------------------------------------------------------------------------------*/
        $obj_sys= new Tclean($clink);
        $fecha_backup= $obj_sys->get_system('backupbd');

        $fecha_backup= !is_null($fecha_backup) ? $fecha_backup : $_SESSION['current_year'].'-01-01';
        $now= date('Y-m-d');
        $result= date_diff(date_create($fecha_backup), date_create($now));
        $days= (int)$result->format('%a');

        if ((!empty($config->daysbackup) && (is_null($fecha_backup) || ($days - $config->daysbackup) > 1)) && $_SESSION['nivel'] >= _SUPERUSUARIO) {
         ?>
            <script type="text/javascript">
                backup_sys= true;
            </script>
        <?php }

        /*--------------------------------------------------------------------------------------------------------------------*/
        $obj_sys= new Tclean($clink);
        $obj_sys->get_system('purge');
        $fecha_clean= $obj_sys->GetFecha();
        $fecha_clean= !is_null($fecha_clean) ? $fecha_clean : '2013-01-01';
        $now= date('Y-m-d');
        $result= date_diff(date_create($fecha_clean), date_create($now));
        $months= (int)$result->format('%m');

        $fecha_clean= !empty($config->monthpurge) ? add_date($fecha_clean, 0, $config->monthpurge) : $fecha_clean;
        $result= date_diff(date_create($fecha_clean), date_create($now));
        $days_clean= (int)$result->format('%R%a');

        if ((!empty($config->monthpurge) &&  (is_null($fecha_clean) || $days_clean > 1) && $_SESSION['nivel'] >= _SUPERUSUARIO)) {
         ?>
            <script type="text/javascript">
                purge_sys= true;
            </script>
        <?php }
        /*--------------------------------------------------------------------------------------------------------------------*/
?>

    </body>

    <script type="text/javascript">
        var occupied= false;
        var occupied_text= false;

        <?php if (!empty($occupied)) { ?>
            occupied= <?= $occupied[0] ? 'true' :  'false' ?>;
            occupied_text= <?=$occupied[1] ? "'{$occupied[1]}'" : 'undefined'?>;
        <?php } ?>

        function _update_sys_update() {
            if (!update_sys_update) {
                _export_lote();
                return;
            }

            text= "Puede que sea necesario que se requiera la actualización de la estructura de la Base de Datos. ";
            text+= "De ser necesario el sistema lo hará automáticamente. Por favor, sea paciente y este atento a cualquier mensaje de error.";
            alert(text, function(ok) {
                if (ok)
                    self.location.href= '../html/home.php?action=update';
            });
        }

        function _export_lote() {
            if (!export_lote) {
                _backup_sys();
                return;
            }

            text= "Se requiere ejecutar la sincronización del sistema. ";
            <?php if (!is_null($fecha_synchro)) { ?>
                text+= "La última sincronización se realizó hace <?= $day_synchro ?> días, <?= $hour_synchro ?> horas, <?= $min_synchro ?> minutos.";
            <?php } ?>
            text+= "¿Desea ejecutar la sincronización ahora?";
            confirm(text, function(ok) {
                if (ok)
                    self.location.href= '../html/home.php?signal=login&action=export';
                else
                    _backup_sys();
            });
        }

        function _backup_sys() {
            if (!backup_sys) {
                _purge_sys();
                return;
            }

            text= "Es conveniente que realice una salva periódica de la Base de Datos para la protección de su Sistema. ";
            text+= "La última salva automática se hizo hace más de <?= $days ?> días. ¿Desea que el sistema lo haga ahora?";
            confirm(text, function(ok) {
                if (ok)
                    self.location.href= '../html/home.php?action=backup';
                else
                    _purge_sys();
            });
        }

        function _purge_sys() {
            if (!purge_sys) {
                self.location.href= '../html/home.php?error='+encodeURI(error);
                return;
            }

            text= "Es conveniente que realice el mantenimiento para mejorar el rendimiento de su Sistema. ";
            text+= "El último mantenimiento se hizo hace más de <?= $months ?> meses. ¿Desea que el sistema lo haga ahora?";
            confirm(text, function(ok) {
                if (ok)
                    self.location.href= '../html/home.php?action=purge';
                else
                    parent.location.href= '../html/home.php?error='+encodeURI(error);
            });
        }

        function _update_sys_upgrade() {
            if (update_sys_upgrade) {
                text= "En el buzón del sistema existen mejoras disponibles. ";
                text+= "Las actualizaciones corrigen errores y mejoran las funcionalidades ¿Desea que Diriger se actualice ahora?";
                confirm(text, function(ok) {
                    if (ok)
                        self.location.href= '../html/home.php?action=upgrade';
                    else
                        _update_sys_update();
                });
            } else {
                _update_sys_update();
            }
        }

        function _actions() {
            if (update_sys_update) {
                _update_sys_update();
                return;
            }

            if (!occupied && occupied_text) {
                alert(occupied_text, function(ok) {
                    _update_sys_upgrade();
                });
            } else {
                _this();
            }

            function _this() {
                if (occupied && (update_sys_upgrade || purge_sys || backup_sys || export_lote)) {
                    occupied_text+= " Desea realizar las operaciones de actualización, salva de base de datos o sincronizacion requeridas ?";
                    confirm(occupied_text, function(ok) {
                        if (ok)
                            _update_sys_upgrade();
                        else
                            self.location.href= '../html/home.php';
                    });
                } else
                    _update_sys_upgrade();
            }
        }
    </script>


    <script type="text/javascript">
        $(document).ready(function () {

            if (expire_days) {
                if (parseInt(expire_days) > 0) {
                    text= "La Licencia de Uso Anual del sistema vence dentro de "+expire_days+" días. Consulte a su proveedor antes de que se ";
                    text+= "le bloquee el acceso al sistema.";
                    alert(text, function(ok) {
                        if (ok)
                            _actions();
                    });
                } else {
                    text= "Ha expirado la Licencia Anual del sistema. Contacte a su proveedor";
                    self.location.href= '<?=$index_page?>?error='+encodeURIComponent(text);
                }
            } else
                _actions();
        });
    </script>
</html>
