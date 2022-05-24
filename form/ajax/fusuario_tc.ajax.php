<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/class/connect.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/tablero.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : NULL;

$obj= new Ttablero($clink); 
$result= $obj->listar();
$cant= $obj->GetCantidad();

if (!empty($id_usuario)) {
	$obj_user= new Tusuario($clink);
	$obj_user->SetIdUsuario($id_usuario);
	$obj_user->set();
	 
	$obj->SetIdUsuario($id_usuario); 
	$array_tableros= $obj->get_procesos_by_user();
}
?>

<link rel="stylesheet" type="text/css" href="../css/ajax.css?version=">

<script type="text/javascript" src="../../js/string.js?version="></script>
<script type="text/javascript" src="../../js/general.js?version="></script>

<script language='javascript' type="text/javascript" charset="utf-8">
var trId_tc = null;
var chk_id_tc = 0;


function rowSelect_tc(obj, id) {
    if (trId_tc != null)
        document.getElementById(trId_tc).className = 'row';

    trId_tc = obj.getAttribute('id');
    obj.className = 'row rselect';
    chk_id_tc = id;
}


function rowOver_tc(obj) {
    if (obj.getAttribute('id') == trId_tc)
        return;
    else
        obj.className = 'row rover';
}

function rowOut_tc(obj) {
    if (obj.getAttribute('id') == trId_tc)
        return;
    else
        obj.className = 'row';
}

function insertRow_tc(xtable) {
    if (trId_tc == null) {
        alert('No hay tablero selecionado.');
        return;
    }

    var objRow = document.getElementById('_' + trId_tc);
    var table = objRow.parentNode;

    var strHTML = objRow.cells[0].innerHTML;
    table.removeChild(objRow);

    // agregar a la tabla 2	
    var objTr = document.createElement("tr");
    objTr.id = '_' + trId_tc;

    var objTd = document.createElement("td");
    objTd.innerHTML = strHTML;

    objTr.appendChild(objTd);

    TABLE = document.getElementById(xtable);
    TABLE.appendChild(objTr);

    document.getElementById(trId_tc).className = 'row';

    if (xtable == 'table2')
        document.getElementById('tab_tc' + chk_id_tc).value = 1;
    if (xtable == 'table1')
        document.getElementById('tab_tc' + chk_id_tc).value = 0;
}
</script>


<form id="frm_tc" name="frm_tc" class="form-in-win" action="javascript:ejecutar('tc')" method="post"
    style="border:none">
    <input type=hidden name=exect value=<?=$action?> />
    <input type=hidden name=id value=<?=$id_usuario?> />
    <input type=hidden name=menu value=user_tablero />

    <table border="0" cellspacing="0" cellpadding="0">

        <?php if ($cant == 0) { ?> <tr>
            <td width="100%" id="msg">
                <div id="div-msg" class='box-alarm'></div>
            </td>
        </tr>
        <script language="javascript">
        div_alarm(
            "Aún no hay Tableros de Control de Indicadores creados en el sistema para el escenario en el que está trabajando. "+ 
            "Deberá crear los Tableros y posteriormente podrá utilizar esta funcionalidad."
            );
        </script>
        <?php } else { ?>

        <tr>
            <td colspan="3" valign="middle"><label for="clave1"
                    style="padding-top:2px;">Usuario:</label>&nbsp;<strong><?php echo $obj_user->GetNombre().' ('.$obj_user->GetCargo().')'; ?></strong>
            </td>
        </tr>

        <tr height="30px">
            <td valign="middle"><label for="clave1">No tiene acceso a:</label></td>
            <td>&nbsp;</td>
            <td valign="middle"><label for="clave1">Tiene acceso a:</label></td>
        </tr>

        <tr>
            <td>
                <div id="div1" class="xpanel">
                    <table id="table1" class="ptable" border="0" cellpadding="0" cellspacing="0">

				<?php 
				$k= 0;
				
				$clink->data_seek($result);
				
				while ($row= $clink->fetch_array($result)) {
					if (!empty($id_usuario)) {
						$value= $array_tableros[$row['id']];
						if (isset($value)) 
                            continue;
					}
					else {
						$value= false;
					}
					
					if ($value == false) {
						++$k;
				?>
                        <input type="hidden" id="tab_tc<?=$k?>" name="tab_tc<?=$row['id']?>" value="0" />
                        <tr id="_tr1-<?=$k?>">
                            <td>
                                <div class="row" id="tr1-<?=$k?>" onClick="rowSelect_tc(this,<?=$k?>)"
                                    onMouseOver="rowOver_tc(this)" onMouseOut="rowOut_tc(this)">
                                    <?php echo $row['nombre']?></div>
                            </td>
                        </tr>

                        <?php } } ?>
                    </table>
                </div>

            </td>

            <td width="60px" align="center" valign="middle" style="padding:5px;">
                <br /><br /><br />
                <?php if ($action != 'list') { ?>
                <span class="submit" align="center">
                    <input type="button" name="button2" id="button2" value="Agregar >>" onClick="insertRow_tc('table2')"
                        title="Agregar" style="width:80px;">
                    <input type="button" name="button3" id="button3" value="<< Quitar" onClick="insertRow_tc('table1')"
                        title="Eliminar" style="width:80px; margin-top:10px;"></span>
                <?php } ?>

            </td>
            <td>

                <div id="div2" class="xpanel">
                    <table id="table2" class="ptable" border="0" cellpadding="0" cellspacing="0">
                        <?php 				
				$clink->data_seek($result);
				
				while ($row= $clink->fetch_array($result)) {
					if (!empty($id_usuario)) {
						$value= $array_tableros[$row['id']];
						if (!isset($value)) 
                            continue;
					}
					else {
						$value= false;
					}
					
					if ($value == true) {
						++$k;
				?>
                        <input type="hidden" id="init_tab_tc<?=$k?>" name="init_tab_tc<?=$row['id']?>" value="1" />
                        <input type="hidden" id="tab_tc<?=$k?>" name="tab_tc<?=$row['id']?>" value="1" />
                        <tr id="_tr2-<?=$k?>">
                            <td>
                                <div class="row" id="tr2-<?=$k?>" onClick="rowSelect_tc(this,<?=$k?>)"
                                    onMouseOver="rowOver_tc(this)" onMouseOut="rowOut_tc(this)">
                                    <?php echo $row['nombre']?></div>
                            </td>
                        </tr>

                        <?php } } ?>
                    </table>
                </div>
            </td>
        </tr>

        <tr>
            <?php } ?>
    </table>
    <br />

    <div id="_submit" class="submit" align="center" style="width:100%; text-align:center; display:block">
        <?php if ($action == 'edit' && $cant > 0) { ?> <input value="Aceptar" type="submit">&nbsp; <?php } ?>
        <input value="Cancelar" type="reset" onclick="closeFloatingDiv('div-ajax-panel')">
    </div>

    <div id="_submited" class="submited" align="center" style="display:none">
        <img src="../img/loading.gif" alt="cargando" /> Por favor espere, la operación puede tardar unos minutos
        ........
    </div>

    <input type="hidden" id="cant_tab" name="cant_tab" value="<?=$k?>" />

</form>

<?php if (!is_null($error)) { ?>
<script language='javascript' type="text/javascript" charset="utf-8">
alert("<?php echo str_replace("\n"," ", addslashes($error)) ?>")
</script>
<?php } ?>