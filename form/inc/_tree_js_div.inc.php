
<?php
function _refresh_obj_ul($k_obj) {
    ?>
    if (tag.search('obj_ul_<?=$k_obj?>') == 0) {
        if ($('#obj_ul_<?=$k_obj?>').css('display') == 'none') {
            $('#obj_ul_<?=$k_obj?>').show();
            $('#img_obj_li_<?=$k_obj?>').removeClass('fa-search-plus');
            $('#img_obj_li_<?=$k_obj?>').addClass('fa-search-minus');
            $('#img_obj_li_<?=$k_obj?>').prop('title', 'contraer');
        }
        else {
            $('#obj_ul_<?=$k_obj?>').hide();
            $('#img_obj_li_<?=$k_obj?>').removeClass('fa-search-minus');
            $('#img_obj_li_<?=$k_obj?>').addClass('fa-search-plus');
            $('#img_obj_li_<?=$k_obj?>').prop('title', 'expandir');
        }
    }
<?php
}


function _refresh_obj($result, &$k_obj, $print_obj= true, &$k_ind) {
    global $clink;
    global $year;
    global $array_procesos_entity;
    
    $obj_peso= new Tpeso($clink);
    $obj_peso->SetYear($year);

    while ($row= $clink->fetch_array($result)) {
        $prs= $array_procesos_entity[$row['id_proceso']];
        if (!empty($prs['id_entity']) && $prs['id_entity'] != $_SESSION['id_entity'])
            continue;
        if (empty($prs['id_entity']) && (!$row['if_send_down'] && $prs['tipo'] < $_SESSION['entity_tipo']))
            continue;
        if (empty($prs['id_entity']) && (!$row['if_send_up'] && $prs['tipo'] > $_SESSION['entity_tipo']))
            continue;        
        
        ++$k_obj;
        $id_objetivo= $row['_id'];

        $i_result= $obj_peso->listar_inductores_ref_objetivo($id_objetivo, true);
        $i_cant= $obj_peso->GetCantidad();

        $k_result= $obj_peso->listar_objetivos_ref_objetivo_sup($id_objetivo, true);
        $k_cant= $obj_peso->GetCantidad();

        if ($print_obj) 
            _refresh_obj_ul($k_obj);
      
        if ($k_cant > 0) 
            _refresh_obj($k_result, $k_obj, $print_obj, $k_ind);
        
        if (!$print_obj && $i_cant > 0) {
            while ($irow= $clink->fetch_array($i_result)) {
                ++$k_ind;
                _refresh_ind($k_ind);
            }
        }
    }
}
?>


<?php function _refresh_ind(&$k_ind) { ?>
    if (tag.search('ind_ul_<?= $k_ind?>') == 0) {
        if ($('#ind_ul_<?= $k_ind?>').css('display') == 'none') {
            $('#ind_ul_<?= $k_ind?>').show();
            $('#img_ind_li_<?= $k_ind?>').removeClass('fa-search-plus');
            $('#img_ind_li_<?= $k_ind?>').addClass('fa-search-minus');
            $('#img_ind_li_<?= $k_ind?>').prop('title', 'contraer');
        }	
        else {
            $('#ind_ul_<?= $k_ind?>').hide();
            $('#img_ind_li_<?= $k_ind?>').removeClass('fa-search-minus');
            $('#img_ind_li_<?= $k_ind?>').addClass('fa-search-plus');
            $('#img_ind_li_<?= $k_ind?>').prop('title', 'expandir');
        }
    }
<?php } ?>


<script language="javascript">

    <?php
    function _refresh_pol_exec($result_pol, &$k_pol) {
        global $clink;
        global $array_politicas;
        global $chk_inner;

        while ($row_pol= $clink->fetch_array($result_pol)) {
            if (empty($array_politicas[$row_pol['_id']]))
                continue;            
            if ($chk_inner && empty($row_pol['if_inner'])) 
                continue;

            ++$k_pol;
            ?>

            if (tag.search('pol_ul_<?= $k_pol?>') == 0) {
                if ($('#pol_ul_<?= $k_pol?>').css('display') == 'none') {
                    $('#pol_ul_<?= $k_pol?>').show();
                    $('#img_pol_li_<?= $k_pol?>').removeClass('fa-search-plus');
                    $('#img_pol_li_<?= $k_pol?>').addClass('fa-search-minus');
                    $('#img_pol_li_<?= $k_pol?>').prop('title', 'contraer');
                }
                else {
                    $('#pol_ul_<?= $k_pol?>').hide();
                    $('#img_pol_li_<?= $k_pol?>').removeClass('fa-search-minus');
                    $('#img_pol_li_<?= $k_pol?>').addClass('fa-search-plus');
                    $('#img_pol_li_<?= $k_pol?>').prop('title', 'expandir');
                }
            }
        <?php    
        }    
    }

    function _refresh_pol(&$k_pol, $capitulo, $grupo= null) {
        global $signal;
        global $clink;
        global $chk_sys, $chk_inner;
        global $id_proceso;
        global $year;

        if ($signal != 'politica') 
            return;

        $obj= new Tpolitica($clink);
        $obj->SetIdProceso($id_proceso);
        $obj->SetYear($year);
        $obj->SetIfTitulo(false);

        if ($grupo === false) 
            $obj->SetIfGrupo(false);

        $obj->SetCapitulo($capitulo);

        if (!empty($grupo)) 
            $obj->SetGrupo($grupo);

        $chk_sys= empty($chk_sys) ? false : true;
        $result_pol= $obj->listar($chk_sys);

        _refresh_pol_exec($result_pol, $k_pol); 
    }
    ?>

    function refresh_pol(tag) {
        if ($('#_cant_'+tag).value == 0) {
            alert("Esta política o lineamiento no tiene asociado objetivos estratégicos en el año seleccionado.");
            return;
        }
	
        <?php
        global $clink;
        global $chk_inner;
        global $capitulo;
        global $grupo;
        global $signal;
        global $year;

        $k_indi= null;

        if ($signal == 'politica') {
            unset($obj);
            $obj= new Tpolitica($clink);
            $obj->SetIfTitulo(false);
            $obj->SetIfGrupo(false);
            $obj->SetGrupo(0);
            $obj->SetCapitulo(0);
            $obj->SetYear($year);
            $i= 0;

            _refresh_pol($i, 0, 0);
            
            $obj= new Tpolitica($clink);
            $obj->SetIfTitulo(true);
            $obj->SetIfCapitulo(true);
            $obj->SetYear($year);
            $result_pol_cap= $obj->listar(false);

            while ($row_pol_cap= $clink->fetch_array($result_pol_cap)) {
                if ($chk_inner && empty($row_pol_cap['if_inner'])) 
                    continue;
                if (!empty($capitulo) && $capitulo != $row_pol_cap['id']) 
                    continue;

                ++$i;

                unset($obj);
                $obj= new Tpolitica($clink);
                $obj->SetIfTitulo(true);
                $obj->SetIfGrupo(true);
                $obj->SetYear($year);
                $obj->SetCapitulo($row_pol_cap['id']);

                $result_pol_grupo= $obj->listar(false);

                _refresh_pol($i, $row_pol_cap['id'], false);

                while ($row_pol_grupo= $clink->fetch_array($result_pol_grupo)) {
                    if ($chk_inner && empty($row_pol_grupo['if_inner'])) 
                        continue;
                    if (!empty($grupo) && $grupo != $row_pol_grupo['id']) 
                        continue;

                    ++$i;

                    _refresh_pol($i, $row_pol_cap['id'], $row_pol_grupo['id']);
                }
        }   }
    ?>
    }


    function refresh_obj_sup(tag) {
        if ($('#_cant_'+tag).value == 0) {
            alert("Este Objetivos Estratégicos del Órgano Superior de Dirección no tiene Objetivos Estratégicos asociados en el año seleccionado.");
            return;
        }

	    <?php
	    global $clink;
        global $array_procesos_entity;
        
	    if ($signal == 'objetivo_sup') {
            $clink->data_seek($result_obj_sup);

            $i= 0;
            while ($row_obj_sup= $clink->fetch_array($result_obj_sup)) {
                $prs= $array_procesos_entity[$row_obj_sup['id_proceso']];
                if (!empty($prs['id_entity']) && $prs['id_entity'] != $_SESSION['id_entity'])
                    continue;
                if (empty($prs['id_entity']) && (!$row_obj_sup['if_send_down'] && $prs['tipo'] < $_SESSION['entity_tipo']))
                    continue;
                if (empty($prs['id_entity']) && (!$row_obj_sup['if_send_up'] && $prs['tipo'] > $_SESSION['entity_tipo']))
                    continue;            

                ++$i;
        ?>
                if (tag.search('obj_sup_ul_<?= $i?>') == 0) {
                    if ($('#obj_sup_ul_<?= $i?>').css('display') == 'none') {
                        $('#obj_sup_ul_<?= $i?>').show();
                        $('#img_obj_sup_li_<?= $i?>').removeClass('fa-search-plus');
                        $('#img_obj_sup_li_<?= $i?>').addClass('fa-search-minus');
                        $('#img_obj_sup_li_<?= $i?>').prop('title', 'contraer');
                    }
                    else {
                        $('#obj_sup_ul_<?= $i?>').hide();
                        $('#img_obj_sup_li_<?= $i?>').removeClass('fa-search-minus');
                        $('#img_obj_sup_li_<?= $i?>').addClass('fa-search-plus');
                        $('#img_obj_sup_li_<?= $i?>').prop('title', 'expandir');
                    }
                }
	<?php } } ?>
    }

<?php
    function _obj_refresh_ind($result_obj, &$k_ind) {
        global $obj_peso;
        global $clink;
        global $array_procesos_entity;
        
        while ($row_obj= $clink->fetch_array($result_obj)) {
            $prs= $array_procesos_entity[$row_obj['id_proceso']];
            if (!empty($prs['id_entity']) && $prs['id_entity'] != $_SESSION['id_entity'])
                continue;
            if (empty($prs['id_entity']) && (!$row_obj['if_send_down'] && $prs['tipo'] < $_SESSION['entity_tipo']))
                continue;
            if (empty($prs['id_entity']) && (!$row_obj['if_send_up'] && $prs['tipo'] > $_SESSION['entity_tipo']))
                continue;               
            
            $result= $obj_peso->listar_inductores_ref_objetivo($row_obj['_id'], true);

            while ($row= $clink->fetch_array($result)) {
                ++$k_ind;
                _refresh_ind($k_ind);
        }   }
    }


    function _pol_refresh_obj(&$k_obj, $capitulo, $grupo= null, $print_obj, &$k_ind= null) {
        global $clink;
        global $chk_sys, $chk_inner;
        global $obj_peso;
        global $id_proceso;
        global $year;

        $obj= new Tpolitica($clink);
        $obj->SetYear($year);
        $obj->SetIdProceso($id_proceso);

        $obj->SetIfTitulo(false);
        if ($grupo === false) 
            $obj->SetIfGrupo(false);
        $obj->SetIfCapitulo(false);
        $obj->SetCapitulo($capitulo);
        if (!empty($grupo)) 
            $obj->SetGrupo($grupo);

        $chk_sys= empty($chk_sys) ? false : true;
        $result_pol= $obj->listar($chk_sys);

        while ($row_pol= $clink->fetch_array($result_pol)) {
            if ($chk_inner && empty($row_pol['if_inner'])) 
                continue;

            $result_obj= $obj_peso->listar_objetivos_ref_politica($row_pol['_id'], true);

            _refresh_obj($result_obj, $k_obj, $print_obj, $k_ind);

            if (!is_null($k_ind)) {
                _obj_refresh_ind($result_obj, $k_ind);
            }
        }
    }
    ?>

    <?php
    function _refresh_pol_obj_exec(&$k_pol, &$k_obj, $print_obj, &$k_ind= null) {
        global $clink;
        global $chk_inner;
        global $year;
        global $obj_peso;
        global $array_politicas;

        $obj= new Tpolitica($clink);
        $obj->SetIfTitulo(false);
        $obj->SetIfCapitulo(0);
        $obj->SetCapitulo(0);
        $obj->SetGrupo(0);
        $obj->SetYear($year);

        $result_pol= $obj->listar(false);

        while ($row_pol= $clink->fetch_array($result_pol)) {
            if (empty($array_politicas[$row_pol['_id']]))
                continue;             
            if ($chk_inner && empty($row_pol['if_inner'])) 
                continue;

            ++$k_pol;

            $result_obj= $obj_peso->listar_objetivos_ref_politica($row_pol['_id'], true);

            _refresh_obj($result_obj, $k_obj, $print_obj, $k_ind);

            if (!is_null($k_ind)) {
                _obj_refresh_ind($result_obj, $k_ind);
            }
        } 
    }
    ?>

    function refresh_obj(tag) {  
        var text;
        
        if ($('#_cant_'+tag).value == 0) {
            text= "Este Objetivo Estratégico no tiene asociado Objetivos de Trabajo en el año seleccionado, ni tiene dependencia con otros ";
            text+= "Objetivos Estratégicos pertenecientes a niveles de dirección o procesos subordinados.";
            alert(text);
            return;
        }

        <?php
        global $clink;
        global $signal;
        global $chk_inner;
        global $capitulo;
        global $grupo;

        global $obj_peso;
        global $year;

        $k_pol= 0; 
        $k_obj= 0; 
        $k_ind= null;

        if ($signal == 'politica') {
            _refresh_pol_obj_exec($k_pol, $k_obj, true, $k_ind);
       
            $obj= new Tpolitica($clink);
            $obj->SetIfTitulo(true);
            $obj->SetIfCapitulo(true);

            $result_pol_cap= $obj->listar(false);

            while ($row_pol_cap= $clink->fetch_array($result_pol_cap)) {
                if ($chk_inner && empty($row_pol_cap['if_inner'])) 
                    continue;
                if (!empty($capitulo) && $capitulo != $row_pol_cap['id']) 
                    continue;

                ++$k_pol;

                unset($obj);
                $obj= new Tpolitica($clink);

                $obj->SetIfTitulo(true);
                $obj->SetIfGrupo(true);
                $obj->SetCapitulo($row_pol_cap['id']);

                $result_pol_grupo= $obj->listar(false);

                _pol_refresh_obj($k_obj, $row_pol_cap['id'], null, true, $k_ind);

                while ($row_pol_grupo= $clink->fetch_array($result_pol_grupo)) {
                    if ($chk_inner && empty($row_pol_grupo['if_inner'])) 
                        continue;
                    if (!empty($grupo) && $grupo != $row_pol_grupo['id']) 
                        continue;

                    ++$k_pol;

                    _pol_refresh_obj($k_obj, $row_pol_cap['id'], $row_pol_grupo['id'], true, $k_ind);
            }   }   
        }

        elseif ($signal == 'objetivo_sup' && isset($result_obj_sup)) {
            $clink->data_seek($result_obj_sup);

            while ($row_obj_sup= $clink->fetch_array($result_obj_sup)) {
                ++$k_obj;
                _refresh_obj_ul($k_pol);

               $result_obj= $obj_peso->listar_objetivos_ref_objetivo_sup($row_obj_sup['_id'], true);
               $cant= $obj_peso->GetCantidad();
               if ($cant > 0) 
                   _refresh_obj($result_obj, $k_obj, true, $k_ind);
        } }

        else {
            $clink->data_seek($result_obj_sup);

            _refresh_obj($result_obj_sup, $k_obj, true, $k_ind);
        }
        ?>
    }


    function refresh_obj_ci(tag) {  
        if ($('#_cant_'+tag).value == 0) {
                alert("Este objetivo de Control Interno no tiene tareas asociados en el año seleccionado.");
                return;
        }	
        
        <?php
        global $signal;
        global $clink;
            global $year;
            global $id_proceso;

        if ($signal == 'objetivo_ci') {
            $obj= new Tobjetivo_ci($clink);
            $k_task= 0;
            $k_obj= 0;

            $clink->data_seek($result_obj);

            while ($row_obj= $clink->fetch_array($result_obj)) {
        ?>
                if (tag.search('obj_ci_ul_<?= ++$k_obj?>') == 0) {
                    if ($('#obj_ci_ul_<?=$k_obj?>').css('display') == 'none') {
                        $('#obj_ci_ul_<?=$k_obj?>').show();
                        $('#img_obj_ci_li_<?=$k_obj?>').removeClass('fa-search-plus');
                        $('#img_obj_ci_li_<?=$k_obj?>').addClass('fa-search-minus');
                        $('#img_obj_ci_li_<?=$k_obj?>').prop('title', 'contraer');
                    }
                    else {
                        $('#obj_ci_ul_<?=$k_obj?>').hide();
                        $('#img_obj_ci_li_<?=$k_obj?>').removeClass('fa-search-minus');
                        $('#img_obj_ci_li_<?=$k_obj?>').addClass('fa-search-plus');
                        $('#img_obj_ci_li_<?=$k_obj?>').prop('title', 'expandir');
                    }
                }

        <?php } } ?>
    }

    <?php
    function _refresh_pol_obj_ind_exec(&$k_pol, &$k_obj, &$k_ind= null) {
        global $clink;
        global $chk_inner;
        global $year;
        global $obj_peso;
        global $array_politicas;

        $obj= new Tpolitica($clink);
        $obj->SetIfTitulo(false);
        $obj->SetIfCapitulo(0);
        $obj->SetCapitulo(0);
        $obj->SetGrupo(0);
        $obj->SetYear($year);

        $result_pol= $obj->listar(false);

        while ($row_pol= $clink->fetch_array($result_pol)) {
            if (empty($array_politicas[$row_pol['_id']]))
                continue;             
            if ($chk_inner && empty($row_pol['if_inner'])) 
                continue;

            $result_obj= $obj_peso->listar_objetivos_ref_politica($row_pol['_id'], true);

            _refresh_obj($result_obj, $k_obj, false, $k_ind);

            if (!is_null($k_ind)) {
                _obj_refresh_ind($result_obj, $k_ind);
            }               
        }        
    }
    ?>


    function refresh_ind(tag) { 	
        if ($('#_cant_'+tag).value == 0) {
                alert("Este Objetivo de Trabajo no tiene asociados indicadores en el año seleccionado.");
                return;
        }		
	
        <?php
        global $clink;
        global $signal;
        global $chk_inner;
        global $capitulo;
        global $grupo;

        global $id_proceso_ref;
        global $obj_peso;
        global $year;

        $k_pol= 0; 
        $k_obj= 0; 
        $k_ind= 0;

        if ($signal == 'politica') {
            _refresh_pol_obj_ind_exec($k_pol, $k_obj, $k_ind);

            $obj= new Tpolitica($clink);
            $obj->SetIfTitulo(true);
            $obj->SetIfCapitulo(true);

            $result_pol_cap= $obj->listar(false);

            while ($row_pol_cap= $clink->fetch_array($result_pol_cap)) {
                if ($chk_inner && empty($row_pol_cap['if_inner'])) 
                    continue;
                if (!empty($capitulo) && $capitulo != $row_pol_cap['id']) 
                    continue;

                ++$k_pol;

                unset($obj);
                $obj= new Tpolitica($clink);

                $obj->SetIfTitulo(true);
                $obj->SetIfGrupo(true);
                $obj->SetCapitulo($row_pol_cap['id']);

                $result_pol_grupo= $obj->listar(false);

                _pol_refresh_obj($k_obj, $row_pol_cap['id'], false, false, $k_ind);

                while ($row_pol_grupo= $clink->fetch_array($result_pol_grupo)) {
                    if ($chk_inner && empty($row_pol_grupo['if_inner'])) 
                        continue;
                    if (!empty($grupo) && $grupo != $row_pol_grupo['id']) 
                        continue;

                    ++$k_pol;

                    _pol_refresh_obj($k_obj, $row_pol_cap['id'], $row_pol_grupo['id'], false, $k_ind);
        }   }   }

        elseif ($signal == 'objetivo_sup' && isset($result_obj_sup)) {
            $clink->data_seek($result_obj_sup);

            while ($row_obj_sup= $clink->fetch_array($result_obj_sup)) {
               $result_obj= $obj_peso->listar_objetivos_ref_objetivo_sup($row_obj_sup['_id'], true);

               _refresh_obj($result_obj, $k_obj, false, $k_ind);

               $result= $obj_peso->listar_inductores_ref_objetivo($row_obj_sup['_id'], true);

               while ($row= $clink->fetch_array($result)) {
                    ++$k_ind;
                    _refresh_ind($k_ind);
               }
        }   }

        elseif ($signal == 'objetivo' && isset($result_obj_sup)) {
            $clink->data_seek($result_obj_sup);

           _refresh_obj($result_obj_sup, $k_obj, false, $k_ind);
         }

        elseif ($signal == 'perspectiva') {
            $obj_prs= new Tproceso($clink);
            $obj_prs->SetTipo($tipo_prs);
            $obj_prs->get_procesos_up_cascade($id_proceso_ref, null, null, true);

            foreach ($obj_prs->array_cascade_up as $prs) {

                $id_proceso= $prs['id'];
                if (isset($obj_persp)) unset($obj_persp);
                $obj_persp= new Tperspectiva($clink);

                $obj_persp->SetYear($year);
                $obj_persp->SetIdProceso($id_proceso);
                $result_persp= $obj_persp->listar();
                $_cant_persp= $obj_persp->GetCantidad();

                if (empty($_cant_persp)) 
                    continue;

                while ($row_persp= $clink->fetch_array($result_persp)) {
                    $result_ind= $obj_peso->listar_inductores_ref_perspectiva($row_persp['_id']);

                    while ($row= $clink->fetch_array($result_ind)) {
                        ++$k_ind;
                        _refresh_ind($k_ind);
                    }
        }   }   }

        else {
            if ($signal != 'objetivo_ci') {
                  $obj= new Tinductor($clink);
                $obj->SetYear($year);
                $obj->SetIdProceso($id_proceso_ref);
                $result_ind= $obj->listar(_PERSPECTIVA_NULL);
                $cant= $obj->GetCantidad();

                 if ($cant > 0) {
                    while ($row= $clink->fetch_array($result_ind)) {
                        ++$k_ind;
                        _refresh_ind($k_ind);
                 }  }

                $obj_persp= new Tperspectiva($clink);
                $obj_persp->SetYear($year);
                $result_persp= $obj_persp->listar();
                $cant_persp= $obj_persp->GetCantidad();

                if ($cant_persp) {
                    while ($row_persp= $clink->fetch_array($result_persp)) {
                        $obj->SetIdPerspectiva($row_persp['_id']);
                         $obj->SetIdProceso($id_proceso_ref);
                         $obj->SetYear($year);

                         $result_ind= $obj->listar(_PERSPECTIVA_NOT_NULL);
                         $cant= $obj->GetCantidad();
                         if (empty($cant)) 
                             continue;

                         while ($row= $clink->fetch_array($result_ind)) {
                            ++$k_ind;
                            _refresh_ind($k_ind);
                         }
            }   }   }   }
        ?>
    }	


    function refresh_per(tag) {
        if ($('#_cant_'+tag).value == 0) {
                alert("Esta perspectiva no tiene asociado indicadores en el año seleccionado.");
                return;
        }
		
        <?php 
        $k_per= 0;

        $obj_prs= new Tproceso($clink);
        $obj_prs->SetTipo($tipo_prs);
        $obj_prs->listar_in_order('eq_asc', false, $tipo_prs, true, 'desc');

        if ($signal == "perspectiva" || $signal == "inductor") {
            foreach ($obj_prs->array_procesos as $prs) {

                $id_proceso= $prs['id'];
                if (isset($obj_persp)) unset($obj_persp);
                $obj_persp= new Tperspectiva($clink);

                $obj_persp->SetYear($year);
                $obj_persp->SetIdProceso($id_proceso);
                $result_persp= $obj_persp->listar();
                $_cant_persp= $obj_persp->GetCantidad();
                if (empty($_cant_persp)) continue;

                while ($row_persp= $clink->fetch_array($result_persp)) {
                    ++$k_per;
                ?>
                    if (tag.search('per_ul_<?= $k_per?>') == 0) {
                        if ($('#per_ul_<?= $k_per?>').css('display') == 'none') {
                            $('#per_ul_<?= $k_per?>').show();
                            $('#img_per_li_<?= $k_per?>').removeClass('fa-search-plus');
                            $('#img_per_li_<?= $k_per?>').addClass('fa-search-minus');
                            $('#img_per_li_<?= $k_per?>').prop('title', 'contraer');
                        } 
                        else {
                            $('#per_ul_<?= $k_per?>').hide();
                            $('#img_per_li_<?= $k_per?>').removeClass('fa-search-minus');
                            $('#img_per_li_<?= $k_per?>').addClass('fa-search-plus');
                            $('#img_per_li_<?= $k_per?>').prop('title', 'expandir');
                        }
                    }

        <?php } } } ?>
    }


    function refresh_prog(tag) {
        if ($('#_cant_'+tag).value == 0) {
            alert("Este Programa no tiene asociado indicadores en el año seleccionado.");
            return;
        }

        <?php 
        $clink->data_seek($result_prog);

        $i= 0;
        while ($row_prog= $clink->fetch_array($result_prog)) {
            ++$i;
        ?>
        if (tag.search('prog_ul_<?= $i?>') == 0) {
            if ($('#prog_ul_<?= $i?>').css('display') == 'none') {
                $('#prog_ul_<?= $i?>').show();
                $('#img_prog_li_<?= $i?>').removeClass('fa-search-plus');
                $('#img_prog_li_<?= $i?>').addClass('fa-search-minus');
                $('#img_prog_li_<?= $i?>').prop('title', 'contraer');
            }
            else {
                $('#prog_ul_<?= $i?>').hide();
                $('#img_prog_li_<?= $i?>').removeClass('fa-search-minus');
                $('#img_prog_li_<?= $i?>').addClass('fa-search-plus');
                $('#img_prog_li_<?= $i?>').prop('title', 'expandir');
            }
        }
        <?php } ?>
    }

</script>

        <!-- win-board-resum -->
        <div id="win-board-resum" class="card card-primary win-board" data-bind="draganddrop">
            <div class="card-header">
                <div class="row">
                    <div id="win-title-item" class="panel-title ajax-title clear win-drag col-11 m-0"></div>
                    <div class="col-1 m-0">
                        <div class="close">
                            <a href= "javascript:CloseWindow('win-board-resum');" title="cerrar ventana">
                                <i class="fa fa-close"></i>
                            </a>                            
                        </div>
                    </div>                
                </div>
            </div>

            <div class="card-body">
                <div id="win-board-resum-icon" class="btn-toolbar">
                    <?php if ($action != 'list') { ?>
                    <a id="img_dox" class="btn btn-app btn-primary btn-sm" onclick="_registrar()" title="registrar situación o cumplimiento">
                            <i class="fa fa-check"></i>Evaluar
                        </a>
                    <?php } ?>

                    <a id="img_dox" class="btn btn-app btn-info btn-sm" title="" onclick="_graficar()">
                        <i class="fa fa-bar-chart"></i>Graficar
                    </a>
                    
                    <?php if ($edit) { ?>
                        <a id="img_edit" class="btn btn-app btn-warning btn-sm" onclick="_editar('<?= $action ?>')" title="">
                            <i class="fa fa-edit"></i>Editar
                        </a>

                        <a id="img_delete" class="btn btn-app btn-danger btn-sm" onclick="_eliminar()" title="">
                            <i class="fa fa-trash"></i>Eliminar
                        </a>
                    <?php } ?>
                </div>

                <div class="win-body">
                    <strong>Descripcion:</strong><br />
                    <div class="win-label" id="descripcion_item"></div>

                    <hr style="margin: 3px 0px 3px 0px;" />
                    <strong>Evaluación:</strong><br />
                    <u>Registro</u>: <div class="win-label" id="registro_item"></div>
                    <!-- <u>Responsable:</u> <div class="win-label" id="responsable_item"></div>-->
                    <div class="win-label" id="observacion_item"></div>             
                </div>
            </div>
        </div> <!-- win-board-resum -->


        <!-- win-board-signal -->
        <div id="win-board-signal" class="card card-danger win-board" data-bind="draganddrop">
            <div class="card-header">
                <div class="row">
                    <div id="win-title" class="panel-title ajax-title win-drag clear col-11 m-0"></div>
                    <div class="col-1 m-0">
                        <div class="close">
                            <a href= "javascript:CloseWindow('win-board-signal');" title="cerrar ventana">
                                <i class="fa fa-close"></i>
                            </a>                            
                        </div>
                    </div>      
                </div>
            </div>

            <div class="card-body">
                <div id="win-board-signal-icon" class="btn-toolbar">

                    <a id="img_dox" class="btn btn-app btn-info btn-sm" onclick="grafico_indicador()" title="ver gráficos">
                        <i class="fa fa-bar-chart"></i>Graficar
                    </a>

                    <a id="img_planning" class="btn btn-app btn-primary btn-sm" onclick="plan_indicador()" title="planificar">
                        <i class="fa fa-th-list"></i>Planificar
                    </a>             

                    <a id="img_register" class="btn btn-app btn-primary btn-sm"  onclick="real_indicador()" title="ingresar datos reales">
                        <i class="fa fa-check"></i>Registrar
                    </a>
                    
                    <?php if ($edit) { ?> 
                        <a id="img_edit_indi" class="btn btn-app btn-warning btn-sm" onclick="edit_indicador('<?=$signal?>','<?=$action?>')" title="editar">
                            <i class="fa fa-edit"></i>Editar
                        </a>
                      
                        <a id="img_delete_indi" class="btn btn-app btn-danger btn-sm" onclick="_eliminar()" title="eliminar indicador y sus datos">
                            <i class="fa fa-trash"></i>Eliminar
                        </a>
                    <?php } ?>
                </div>

                <div class="badge bg-blue" style="font-size: 1.5em; color:white;">REAL</div>
            
                <div class="box-comments">
                    <div class="box-comment">
                        <div class="comment">Registro:</div>
                        <div class="comment-text" id="registro_real"></div>
                    </div>
                    <div class="box-comment">
                        <div class="comment">Valor:</div>
                        <div class="comment-text" id="valor_real"></div>
                    </div>
                    <div class="box-comment">
                        <div class="comment">Observaciones:</div>
                        <div class="comment-text" id="observacion_real"></div>
                    </div>
                    <div class="box-comment">
                        <div class="comment">Responsable:</div>
                        <div class="comment-text" id="responsable_real"></div>
                    </div>
                </div>  
                
                <div class="badge bg-blue"style="font-size: 1.5em; color:white;">PLAN</div>

                <div class="box-comments">    
                    <div class="box-comment">
                        <div class="comment">Registro:</div>
                        <div class="comment-text" id="registro_plan"></div>
                    </div>
                    <div class="box-comment">
                        <div class="comment">Plan:</div>
                        <div class="comment-text" id="valor_plan"></div>
                    </div>
                    <div class="box-comment">
                        <div class="comment">Observaciones:</div>
                        <div class="comment-text" id="observacion_plan"></div>
                    </div>
                    <div class="box-comment">
                        <div class="comment">Responsable:</div>
                        <div class="comment-text" id="responsable_plan"></div>
                    </div>
                </div>
            </div>    
        </div> <!-- win-board-signal -->


        <!-- win-board-task -->
        <div id="win-board-task" class="card card-primary win-board" data-bind="draganddrop">
            <div class="card-header">
                <div class="row">
                    <div id="win-title-task" class="panel-title ajax-title win-drag col-11 m-0"></div>
                    <div class="col-1 m-0">
                        <div class="close">
                            <a href= "javascript:CloseWindow('win-board-task');" title="cerrar ventana">
                                <i class="fa fa-close"></i>
                            </a>                            
                        </div>
                    </div>      
                </div>
            </div>

            <div class="card-body">
                <div class="box-comments">
                    <div class="box-comment">
                        <div class="comment">Titulo:</div>
                        <div class="comment-text" id="titulo_task"></div>
                    </div>

                    <div class="box-comment">
                        <div class="comment">Descipcion:</div>
                        <div class="comment-text" id="descripcion_task"></div>
                    </div>

                    <div class="box-comment">
                        <div class="comment">Responsable:</div>
                        <div class="comment-text" id="responsable_task"></div>
                    </div>

                    <div class="badge bg-blue"style="font-size: 1.5em; color:white;">Cumplimiento</div>

                    <div class="box-comment">
                        <div class="comment">Estado:</div>
                        <div class="comment-text" id="_valor_task"></div>
                    </div>

                    <div class="box-comment">
                        <div class="comment">Observaciones:</div>
                        <div class="comment-text" id="observacion_task"></div>
                    </div>

                    <div class="box-comment">
                        <div class="comment">Responsable:</div>
                        <div class="comment-text" id="registro_task"></div>
                    </div>
                </div>
            </div>
        </div>   <!-- win-board-task -->  

        <div id="div-ajax-panel" class="ajax-panel" data-bind="draganddrop">

        </div>


        <div id="bit" class="loggedout-follow-normal" style="width: 100%;">
            <a class="bsub" href="javascript:void(0)"><span id="bsub-text">Leyenda</span></a>

            <div id="bitsubscribe">
                <div class="row">
                    <div class="col-md-3">
                        <ul class="list-group-item item">
                            <li class="list-group-item item">
                               <img class="img-rounded" src="../img/alarm-dark.ico">
                                Sobrecumplido al 110% o m&aacute;s
                            </li>
                            <li class="list-group-item item">
                               <img class="img-rounded" src="../img/alarm-blue.ico">
                                Sobre cumplido al 105% o m&aacute;s
                            </li>
                            <li class="list-group-item item">
                               <img class="img-rounded" src="../img/alarm-green.ico">
                                Cumplido al 95% o más y menos del 105% de sobrecumplimiento
                            </li>
                            <li class="list-group-item item">
                               <img class="img-rounded" src="../img/alarm-yellow.ico">
                                Al 90% del éxito
                            </li>
                            <li class="list-group-item item">
                               <img class="img-rounded" src="../img/alarm-red.ico">
                                Fracaso
                            </li>
                            <li class="list-group-item item">
                               <img class="img-rounded" src="../img/alarm-null.ico">
                                No hay criterio de comparaci&oacute;n
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <ul class="list-group-item item">
                            <li class="list-group-item item">
                               <img class="img-rounded" src="../img/arrow-green.ico" />
                                Mejora referido al periodo anterior
                            </li>
                            <li class="list-group-item item">
                               <img class="img-rounded" src="../img/arrow-yellow.ico" />
                                Sin Cambios referido al periodo anterior
                            </li>
                            <li class="list-group-item item">
                               <img class="img-rounded" src="../img/arrow-red.ico" />
                                Empeora referido al periodo anterior
                            </li>
                            <li class="list-group-item item">
                               <img class="img-rounded" src="../img/arrow-blank.ico" />
                                No hay datos en perido anterior
                            </li>
                            <li class="list-group-item item">
                               <img class="img-rounded" src="../img/alarm-blank.ico" />
                                No hay datos
                            </li>
                            <li class="list-group-item item">
                               <img class="img-rounded" src="../img/transmit.ico" width="16" height="16" alt="sincronizacion" />
                                Origen de datos remoto
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-2">
                        <ul class="list-group-item item">
                            <li class="list-group-item item">
                               <img class="img-rounded" src="../img/00cecm.ico" alt="CECM" />
                                CECM
                            </li>
                            <li class="list-group-item item">
                               <img class="img-rounded" src="../img/01oac.ico" alt="OACE" />
                                OACE
                            </li>
                            <li class="list-group-item item">
                               <img class="img-rounded" src="../img/02osde.ico" alt="OSDE" />
                                OSDE
                            </li>
                            <li class="list-group-item item">
                               <img class="img-rounded" src="../img/03gae.ico" alt="GAE" />
                                GAE
                            </li>
                            <li class="list-group-item item">
                               <img class="img-rounded" src="../img/04firm.ico" alt="Empresa" />
                                Empresa
                            </li>
                             <li class="list-group-item item">
                               <img class="img-rounded" src="../img/05ueb.ico" alt="UEB" />
                                UEB
                            </li>                           
                            <li class="list-group-item item">
                                <img class="img-rounded" src="../img/06arc.ico" alt="ARC" />
                                Area de Regulación y Control
                            </li>                   
                            <li class="list-group-item item">
                                <img class="img-rounded" src="../img/07team.ico" alt="Grupo / Brigada" />
                                Grupo o Brigada
                            </li>  
                        </ul>
                    </div>
                     <div class="col-md-2">
                        <ul class="list-group-item item">
                            <li class="list-group-item item">
                                <img class="img-rounded" src="../img/08office.ico" alt="departamento" />
                                Departamento
                            </li>                   
                            <li class="list-group-item item">
                                <img class="img-rounded" src="../img/09process.ico" alt="Proceso Interno" />
                                Proceso Interno
                            </li>                   
                            <li class="list-group-item item">
                                <img class="img-rounded" src="../img/10arc.ico" alt="Area de Resutados de Resultados" />
                                Area de Resultados Claves
                            </li>                   
                            <li class="list-group-item item">
                                
                                
                            </li>                   
                        </ul>
                     </div>  
                     <div class="col-md-2">
                        <ul class="list-group-item item">
                            <li class="list-group-item item">
                                <img class="img-rounded" src="../img/0pol.ico" alt="Lineamiento" />
                                Política o Lineamiento del Organismo
                            </li> 
                            <li class="list-group-item item">
                                <img class="img-rounded" src="../img/0obj.ico" alt="Objetivo Estrategico" />
                                Objetivo Estratégico
                            </li> 
                            <li class="list-group-item item">
                                <img class="img-rounded" src="../img/0ind.ico" alt="Objetivo de Trabajo" />
                                Objetivo de Trabajo
                            </li> 
                            <li class="list-group-item item">
                                <img class="img-rounded" src="../img/0indi.ico" alt="Indicador" />
                                Indicador
                            </li> 
                            <li class="list-group-item item">
                                <img class="img-rounded" src="../img/calculator-add.ico" alt="calculado por el sistema" />
                                Valor calculado por el sistema
                            </li> 
                            <li class="list-group-item item">
                                <img class="img-rounded" src="../img/calculator-self.ico" alt="calculado por el sistema" />
                                Acumulativo en el año
                            </li> 
                        </ul>
                     </div>     
                </div>
            </div><!-- bitsubscribe -->
        </div> <!--bit-->    



    <div id='div-ajax-graph-select-panel' class="card card-primary ajax-panel div-ajax-graph-select-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div class="panel-title ajax-title col-11 m-0 win-drag"></div>
                <div class="col-1 m-0">
                    <div class='close'>
                        <a href="#" title="cerrar ventana" onclick="CloseWindow('div-ajax-graph-select-panel');">
                            <i class="fa fa-close"></i>
                        </a>    
                   </div>                      
                </div>
            </div>
        </div>
        <div id='div-ajax-graph-select' class='card-body'>
        </div>
    </div> 
        
        