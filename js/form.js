/* 
 * Copyright 2017 
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */

var focusin;
$(document).ready(function() {
    heightHeader = 0;
    if ($('.app-body').hasClass('onebar'))
        heightHeader += 60;
    if ($('.app-body').hasClass('twobar'))
        heightHeader += 128;
    if ($('.app-body').hasClass('threebar'))
        heightHeader += 150;
    if ($('.app-body').hasClass('fourbar'))
        heightHeader += 130;
    if ($('.app-body').hasClass('fivebar'))
        heightHeader += 120;
    if ($('.app-body').hasClass('sixthbar'))
        heightHeader += 170;
    if ($('.app-body').hasClass('sevenbar'))
        heightHeader += 80;
    if ($('.app-body').hasClass('eightbar'))
        heightHeader += 50;
    if ($('.app-body').hasClass('ninebar'))
        heightHeader += 180;
    if ($('.app-body').hasClass('tenbar'))
        heightHeader += 90;                 
    $('.app-body.container-fluid, .app-body.container').height($(window).height() - heightHeader);

    $(window).on('resize', function() {
        heightHeader = 0;
        if ($('.app-body').hasClass('onebar'))
            heightHeader += 60;
        if ($('.app-body').hasClass('twobar'))
            heightHeader += 128;
        if ($('.app-body').hasClass('threebar'))
            heightHeader += 150;
        if ($('.app-body').hasClass('fourbar'))
            heightHeader += 130;
        if ($('.app-body').hasClass('fivebar'))
            heightHeader += 120;
        if ($('.app-body').hasClass('sixthbar'))
            heightHeader += 170;
        if ($('.app-body').hasClass('sevenbar'))
            heightHeader += 80;
        if ($('.app-body').hasClass('eightbar'))
            heightHeader += 50;
        if ($('.app-body').hasClass('ninebar'))
            heightHeader += 180; 
        if ($('.app-body').hasClass('tenbar'))
            heightHeader += 90;                         
        $('.app-body.container-fluid, .app-body.container').height($(window).height() - heightHeader);
    });

    focusin = function(_this) {
        tabId = $(_this).parents('* .tabcontent');
        $("ul.nav.nav-tabs li").removeClass('active');
        $(".tabcontent").hide();
        $('#nav-' + tabId.prop('id')).addClass('active');
        tabId.show();
        $(_this).focus();
    }

    //When page loads...
    $(".tabcontent").hide(); //Hide all content
    $("ul.nav li:first a").addClass("active").show(); //Activate first tab
    $(".tabcontent:first").show(); //Show first tab content

    //On Click Event
    $("ul.nav li a").click(function() {
        $("ul.nav li a").removeClass("active"); //Remove any "active" class
        $(this).addClass("active"); //Add "active" class to selected tab
        $(".tabcontent").hide(); //Hide all tab content

        var activeTab = $(this).attr("href"); //Find the href attribute value to identify the active tab + content          
        $("#" + activeTab).fadeIn(); //Fade in the active ID content
        // $("#" + activeTab + " .form-control:first").focus();
        return false;
    });
});