<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once _PHP_DIRIGER_DIR."config.ini";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/grupo.class.php";
require_once "../php/class/proceso.class.php";
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>CORREO ELECTRÓNICO</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <link rel="stylesheet" href="../libs/multiselect/multiselect.css?version=" />
    <script type="text/javascript" charset="utf-8"
        src="../libs/multiselect/multiselect.js?version="></script>

    <link rel="stylesheet" type="text/css" href="../libs/tinyeditor/tinyeditor.css?version=" />
    <script type="text/javascript" src="../libs/tinyeditor/tiny.editor.packed.js?version=">
    </script>

    <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

    <link rel="stylesheet" type="text/css" href="../libs/tinyeditor/tinyeditor.css?version=" />
    <script type="text/javascript" src="../libs/tinyeditor/tiny.editor.packed.js?version=">
    </script>

    <script type="text/javascript" src="../js/ajax_core.js?version="></script>

    <script type="text/javascript" src="../js/form.js?version="></script>

    <script language="javascript" type="text/javascript">
    function validar() {
        var form = document.forms[0];

        if (!Entrada(form.subject.value)) {
            alert('Introduzca el asunto');
            return;
        }

        editor.post();
        if (!Entrada(form.msg.value)) {
            alert('Introduzca el cuerpo del correo');
            return;
        }

        if (form.cant_tab_user.value == 0 && !Entrada(form.tomail.value)) {
            alert('Debe de especificar al menos un destinatario del correo');
            return;
        }

        form.action = '../php/mail.interface.php';
        form.submit();
    }
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        $(".tabLink").each(function() {
            $(this).click(function() {
                tabeId = $(this).attr('id');
                $(".tabLink").removeClass("activeLink");
                $(this).addClass("activeLink");
                $(".tabcontent").addClass("hide");
                $("#" + tabeId + "-1").removeClass("hide")
                return false;
            });
        });
    });
    </script>

    <style type="text/css">
    body {
        background: none;
    }

    .email {
        background-color: transparent !important;
        border: none
    }
    </style>
</head>

<body class="form">
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">CORREO ELECTRÓNICO</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Nuevo Correo</a></li>
                        <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Contactos</a></li>
                    </ul>

                    <form class="form-horizontal" name="fevento" id="fevento" action="javascript:validar()"
                        method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="id" value="<?=$_SESSION['id_usuario'] ?>" />
                        <input type="hidden" name="menu" id="menu" value="email" />

                        <?php
                        $action = 'no-send';

                        if ($ok) {
                            $subject = NULL;
                            $body = NULL;
                        }
                        ?>

                        <!-- generales -->
                        <div class="tabcontent" id="tab1">
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Destinatario:
                                </label>
                                <div class="col-md-10 ">
                                    <input type='text' name='tomail' id="tomail" value='' class="form-control">
                                    (Puede utilizar la lista de contacto o usuarios de Diriger)
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Asunto:
                                </label>
                                <div class="col-md-10">
                                    <input type='text' name='subject' id="subject" value='' class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Adjunto:
                                </label>
                                <div class="col-md-10">
                                    <input type="file" name="attachment" id="attachment" class="form-control" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-0 col-md-12 col-lg-12">
                                    <textarea id="msg" name='msg' class="form-control"></textarea>

                                    <script language="javascript" type="text/javascript">
                                    var editor = new TINY.editor.edit('editor', {
                                        id: 'msg',
                                        width: '100%',
                                        height: 230,
                                        cssclass: 'tinyeditor',
                                        controlclass: 'tinyeditor-control',
                                        rowclass: 'tinyeditor-header',
                                        dividerclass: 'tinyeditor-divider',

                                        controls: ['bold', 'italic', 'underline', 'strikethrough', '|',
                                            'subscript', 'superscript', '|',
                                            'orderedlist', 'unorderedlist', '|', 'outdent', 'indent', '|',
                                            'leftalign',
                                            'centeralign', 'rightalign', 'blockjustify', '|', 'unformat',
                                            '|', 'undo', 'redo', 'n',
                                            'font', 'size', 'style', '|', 'image', 'hr', 'link', 'unlink',
                                            '|', 'print'
                                        ],

                                        //footer: true,
                                        footer: false,
                                        fonts: ['Arial', 'ArialNarrow', 'Courier', 'CourierNew', 'Geneva',
                                            'Georgia', 'Segoe UI', 'Tahoma', 'Terminal', 'TimesNewRoman',
                                            'Trebuchet MS', 'Verdana'
                                        ],
                                        xhtml: true,
                                        //	cssfile: 'custom.css',
                                        bodyid: 'editor',
                                        //	footerclass: 'tinyeditor-footer',
                                        //	toggle: {text: 'source', activetext: 'wysiwyg', cssclass: 'toggle'},
                                        resize: {
                                            cssclass: 'resize'
                                        }
                                    });
                                    </script>

                                </div>
                            </div>
                        </div><!-- generales -->


                        <!-- Participantes -->
                        <div class="tabcontent" id="tab2">
                            <?php
                            $id= $id_usuario;
                            $id_user_restrict= $id_usuario;
                            $user_ref_date= $year.'-'.$month.'-'.$day;
                            $restrict_prs= array(_TIPO_PROCESO_INTERNO);
                            $config->freeassign= true;

                            require "inc/usuario_tabs.inc.php";
                            ?>
                        </div><!-- Participantes -->

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'no-send') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset" onclick="self.close()">Cerrar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/02_usuarios.htm#02_4.3')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                        </div>

                    </form>

                </div> <!-- panel-body -->
            </div> <!-- panel -->
        </div> <!-- container -->

    </div>


</body>

</html>