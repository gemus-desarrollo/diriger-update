<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (empty($inicio) || empty($fin)) {
    $inicio= (int)date('Y') - 5;
    $fin= (int)date('Y') + 5;
}
if ($signal == 'programa') {
    $inicio-= 10;
    $fin+= 10;
}
?>

    <li class="navd-dropdown date-menu">
        <a class="dropdown-toggle" href="#navbarDate" data-toggle="collapse" aria-expanded="false">
            <i class="fa fa-calendar"></i>
                <?=$use_select_month ? "Año/Mes" : "Año"?>
                <?php if ($use_select_day) echo "/Día"?>
            <span class="caret"></span>
        </a>

        <ul class="navd-dropdown-menu date-menu" id="navbarDate">
           <?php if ($use_select_year) { ?> 
           <li class="navd-dropdown date-menu">
               <input type="hidden" name="year" id="year" value="<?=$year?>" />
               <a class="dropdown-toggle" href="#navbarYear" data-toggle="collapse" aria-expanded="false">
                    <i class="fa fa-calendar"></i>Año
               </a>
               <ul class="navd-dropdown-menu date-menu" id="navbarYear">
                   <?php for ($y= $inicio; $y <= $fin; ++$y) { ?>
                   <li class="nav-item">
                       <a class="<?php if ($y == $year) echo "active"?>" href="#" onclick="_dropdown_year(<?=$y?>)"><?=$y?></a>
                   </li>
                   <?php } ?>
               </ul>
           </li>
           <?php } ?>            
            
           <?php if ($use_select_month) { ?>  
           <li class="navd-dropdown date-menu">
               <input type="hidden" name="month" id="month" value="<?=$month?>" />
               <a class="dropdown-toggle" href="#navbarMonth" data-toggle="collapse" aria-expanded="false">
                   <i class="fa fa-calendar"></i>Mes
               </a>
               <ul class="navd-dropdown-menu date-menu" id="navbarMonth">
                   <?php if ($use_select_month_all) { ?>
                   <li class="nav-item <?php if (empty($month) || $month == -1) echo 'active'?>">
                       <a href="#" onclick="_dropdown_month(-1)">
                           Cualquiera ... 
                       </a>
                   </li>
                    <?php } ?>
                   
                   <?php for ($m= 1; $m <= 12; ++$m) { ?>
                   <li class="nav-item">
                       <a class="<?php if ((int)$m == (int)$month) echo "active"?>" href="#" onclick="_dropdown_month(<?=$m?>)">
                        <?=$meses_array[$m]?>
                    </a>
                   </li>
                   <?php } ?>
               </ul>
           </li>
           <?php } ?>

           <?php if ($use_select_day) { ?>
           <li class="navd-dropdown date-menu">
               <input type="hidden" name="day" id="day" value="<?=$day?>" />
               <a class="dropdown-toggle" href="#navbarDay" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                   <i class="fa fa-calendar"></i>Día
               </a>
               <ul class="dropdown-menu date-menu" id="navbarDay">
                   <li class="nav-item">
                       <div class="daypicker">
                            <?php
                            $d = 1;
                            $time->SetDay($d);
                            $firstday = $time->weekDay();
                            $lastday = $time->longmonth();
                            $mm = str_pad($month, 2, '0', STR_PAD_LEFT);
                            $im = (int) $month;
                            ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th colspan="7"><?=$meses_array[$im]?> / <?=$year?></th>
                                    </tr>
                                    <tr>
                                        <th>Lu</th>
                                        <th>Ma</th>
                                        <th>Mi</th>
                                        <th>Ju</th>
                                        <th>Vi</th>
                                        <th>Sa</th>
                                        <th>Do</th>
                                    </tr>
                                </thead> 
                                <tbody>
                                    <tr>
                                        <?php for ($i= 1; $i < $firstday && $i < 8; ++$i) { ?>
                                           <td class="day new"></td> 
                                        <?php    
                                        } 

                                        for ($i= $firstday; $i < 8; $i++) {
                                            $fecha = $year . '-' . $mm . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                                            $work= !get_work_day($fecha, $weekday) ? "free" : null;
                                            $active= ($d == (int)$day) ? "active" : null;
                                        ?>
                                           <td class="day <?=$work?> <?=$active?>" onclick='_dropdown_day(<?=$d?>)'>
                                               <?=$d++?>
                                           </td>
                                        <?php   
                                        }
                                    ?>
                                    </tr>

                                    <?php
                                    $col= 1;
                                    for ($i= $d; $i <= $lastday; ++$i) {
                                        if ($col == 1) {
                                        ?>
                                        <tr>
                                        <?php 
                                        } 
                                        $fecha = $year . '-' . $mm . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                                        $work = !get_work_day($fecha, $weekday) ? "free" : null;
                                        $active= ($d == (int)$day) ? "active" : null; 
                                        ?>
                                            <td class="day <?=$work?> <?=$active?>" onclick='_dropdown_day(<?=$d?>)'>
                                                <?=$d++?>                                         
                                            </td>
                                        <?php
                                        ++$col;
                                        if ($col > 7) {
                                            $col= 1;
                                        ?>
                                        </tr>
                                    <?php  
                                        } 
                                    }
                                    if ($col < 7) {
                                        for ($i= $col; $i < 8; $i++) {
                                    ?>
                                            <td class="day new"></td>
                                    <?php
                                        }
                                    ?>
                                    </tr>
                                    <?php        
                                    }
                                    ?>
                                </tbody>
                            </table>    
                        </div>
                   </li>

               </ul>               
               
           <?php } ?>
       </ul>
   </li>
